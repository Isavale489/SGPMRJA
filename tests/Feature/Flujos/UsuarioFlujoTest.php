<?php

namespace Tests\Feature\Flujos;

use App\Models\PermisoRol;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Usuarios: alta con política de contraseña y avatar, edición, inhabilitar
 * (NUNCA se borran: estado=0), desbloqueo de recuperación y reset de
 * contraseña por el administrador. Escritos ANTES de migrar a Inertia.
 */
class UsuarioFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private const CLAVE = 'Clave-Segura1';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function supervisorRol(): Rol
    {
        return Rol::firstOrCreate(['nombre' => 'Supervisor'], ['es_sistema' => true]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'name' => 'Carmen Silva',
            'email' => 'carmen@atlantico.test',
            'password' => self::CLAVE,
            'password_confirmation' => self::CLAVE,
            'role_id' => $this->supervisorRol()->id,
        ], $extra);
    }

    public function test_alta_con_avatar_nace_activa_y_con_la_clave_cifrada(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('users.store'), $this->payload([
            'avatar' => UploadedFile::fake()->image('carmen.png', 200, 200),
        ])));

        $u = User::where('email', 'carmen@atlantico.test')->sole();
        $this->assertSame(1, (int) $u->estado);
        $this->assertSame('Supervisor', $u->role);
        $this->assertTrue(Hash::check(self::CLAVE, $u->password));
        $this->assertNotNull($u->avatar);
        Storage::disk('public')->assertExists($u->avatar);
    }

    public function test_la_politica_de_contrasena_se_exige(): void
    {
        $admin = $this->admin();

        foreach (['corta1!', 'sinmayuscula1!', 'SinNumero!!', 'SinEspecial12'] as $clave) {
            $this->actingAs($admin)->postJson(route('users.store'), $this->payload(['password' => $clave, 'password_confirmation' => $clave]))
                ->assertStatus(422)->assertJsonValidationErrors('password');
        }
        $this->actingAs($admin)->postJson(route('users.store'), $this->payload(['password_confirmation' => 'Otra-Clave1']))
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->assertSame(0, User::where('email', 'carmen@atlantico.test')->count());
    }

    public function test_un_correo_repetido_se_rechaza(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('users.store'), $this->payload(['email' => $admin->email]))
            ->assertStatus(422)->assertJsonValidationErrors('email');
    }

    public function test_editar_no_toca_la_clave_ni_el_estado_y_reemplaza_el_avatar(): void
    {
        $admin = $this->admin();
        $u = User::factory()->supervisor()->create(['password' => Hash::make(self::CLAVE), 'avatar' => 'avatars/viejo.png']);
        Storage::disk('public')->put('avatars/viejo.png', 'x');

        $this->assertExito($this->actingAs($admin)->put(route('users.update', $u->id), [
            'name' => 'Carmen S.', 'email' => 'carmen.s@atlantico.test', 'role_id' => $u->role_id,
            'avatar' => UploadedFile::fake()->image('nuevo.jpg'),
        ]));

        $u->refresh();
        $this->assertSame('Carmen S.', $u->name);
        $this->assertSame('carmen.s@atlantico.test', $u->email);
        $this->assertTrue(Hash::check(self::CLAVE, $u->password));
        $this->assertSame(1, (int) $u->estado);
        Storage::disk('public')->assertMissing('avatars/viejo.png');
        Storage::disk('public')->assertExists($u->avatar);
    }

    public function test_inhabilitar_no_borra_y_habilitar_devuelve_el_acceso(): void
    {
        $admin = $this->admin();
        $u = User::factory()->supervisor()->create();

        $this->assertExito($this->actingAs($admin)->delete(route('users.destroy', $u->id)));
        $this->assertModelExists($u); // los usuarios nunca se borran
        $this->assertSame(0, (int) $u->fresh()->estado);

        $this->assertExito($this->actingAs($admin)->post(route('users.restore', $u->id)));
        $this->assertSame(1, (int) $u->fresh()->estado);
    }

    /** Rol no administrador que sí puede gestionar usuarios (la regla no depende de quién lo pida). */
    private function gestorDeUsuarios(): User
    {
        $rol = Rol::create(['nombre' => 'Gestor de usuarios', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'users.gestionar']);

        return User::factory()->create(['role_id' => $rol->id]);
    }

    public function test_no_se_inhabilita_la_propia_cuenta_ni_el_ultimo_administrador(): void
    {
        $admin = $this->admin();
        $otroAdmin = $this->admin();

        $this->actingAs($admin)->deleteJson(route('users.destroy', $admin->id))
            ->assertStatus(422)->assertJsonPath('message', 'No puedes inhabilitar tu propia cuenta.');

        // Con dos administradores se puede inhabilitar a uno; al último, no.
        $this->assertExito($this->actingAs($admin)->delete(route('users.destroy', $otroAdmin->id)));
        $this->actingAs($this->gestorDeUsuarios())->deleteJson(route('users.destroy', $admin->id))
            ->assertStatus(422)->assertJsonPath('message', 'No puedes inhabilitar al último administrador activo.');
        $this->assertSame(1, (int) $admin->fresh()->estado);
    }

    public function test_no_se_le_quita_el_rol_al_ultimo_administrador_activo(): void
    {
        // Mismo resguardo que al inhabilitar: el sistema no puede quedar sin Administrador.
        $admin = $this->admin();

        $this->actingAs($admin)->putJson(route('users.update', $admin->id), [
            'name' => $admin->name, 'email' => $admin->email, 'role_id' => $this->supervisorRol()->id,
        ])->assertStatus(422)->assertJsonValidationErrors('role_id');

        $this->assertTrue($admin->fresh()->isAdmin());
    }

    public function test_desbloquear_recuperacion_limpia_intentos_y_bloqueo(): void
    {
        $u = User::factory()->supervisor()->create();
        $u->forceFill(['recovery_failed_attempts' => 7, 'recovery_locked_until' => now()->addMinutes(10)])->save();

        $this->assertExito($this->actingAs($this->admin())->post(route('users.unlock-recovery', $u->id)));

        $u->refresh();
        $this->assertSame(0, (int) $u->recovery_failed_attempts);
        $this->assertNull($u->recovery_locked_until);
    }

    public function test_reset_de_contrasena_por_el_administrador(): void
    {
        $admin = $this->admin();
        $u = User::factory()->supervisor()->create();
        $u->forceFill(['recovery_failed_attempts' => 3])->save();

        $this->assertExito($this->actingAs($admin)->post(route('users.reset-password', $u->id), [
            'password' => 'Temporal-2026', 'password_confirmation' => 'Temporal-2026',
        ]));

        $u->refresh();
        $this->assertTrue(Hash::check('Temporal-2026', $u->password));
        $this->assertTrue((bool) $u->password_reset_by_admin);
        $this->assertTrue((bool) $u->recovery_must_reset_questions);
        $this->assertSame(0, (int) $u->recovery_failed_attempts);

        // La propia contraseña no se resetea desde este panel.
        $this->actingAs($admin)->postJson(route('users.reset-password', $admin->id), ['password' => 'Temporal-2026', 'password_confirmation' => 'Temporal-2026'])
            ->assertStatus(422);
    }

    public function test_sin_permiso_de_gestion_no_se_crea(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->post(route('users.store'), $this->payload());

        $this->assertSame(0, User::where('email', 'carmen@atlantico.test')->count());
    }
}
