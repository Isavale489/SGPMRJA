<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Usuarios: contrato de la fila con tipos.ts, filtros,
 * historial (estado=0) y respuestas de las acciones para Inertia.
 */
class UsuarioPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        Storage::fake('public');
    }

    /** Claves de `export interface UsuarioFila { ... }` en tipos.ts. */
    private function clavesTs(): array
    {
        $ts = file_get_contents(resource_path('js/pages/Usuarios/tipos.ts'));
        preg_match('/export interface UsuarioFila \{(.*?)\n\}/s', $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);

        return $claves[1];
    }

    public function test_cada_fila_coincide_con_su_interfaz_de_typescript(): void
    {
        $admin = $this->admin();
        $claves = $this->clavesTs();
        $this->assertNotEmpty($claves, 'No se pudo leer UsuarioFila de tipos.ts');

        $this->actingAs($admin)->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Usuarios/Index')
                ->has('usuarios.data', 1, fn (Assert $fila) => $fila->hasAll($claves)) // sin ->etc()
                ->where('usuarios.data.0.es_propio', true)
                ->where('usuarios.data.0.avatar', null) // sin foto: iniciales, sin servicios externos
                ->has('roles')
                ->has('urls.checkEmail'));
    }

    public function test_busca_filtra_por_rol_y_el_historial_son_los_inhabilitados(): void
    {
        $admin = $this->admin();
        $supervisor = User::factory()->supervisor()->create(['name' => 'Carmen Silva']);
        User::factory()->supervisor()->inhabilitado()->create(['name' => 'Luis Retirado']);

        $this->actingAs($admin)->get(route('users.index', ['buscar' => 'silv']))
            ->assertInertia(fn (Assert $p) => $p->has('usuarios.data', 1)->where('usuarios.data.0.nombre', 'Carmen Silva'));
        $this->actingAs($admin)->get(route('users.index', ['rol' => $supervisor->role_id]))
            ->assertInertia(fn (Assert $p) => $p->has('usuarios.data', 1)->where('usuarios.data.0.rol', 'Supervisor'));
        $this->actingAs($admin)->get(route('users.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $p) => $p->has('usuarios.data', 1)->where('usuarios.data.0.nombre', 'Luis Retirado')->where('usuarios.data.0.inhabilitado', true));
    }

    public function test_la_fila_marca_recuperacion_bloqueada_y_clave_temporal(): void
    {
        $admin = $this->admin();
        $u = User::factory()->supervisor()->create();
        $u->forceFill(['recovery_failed_attempts' => 10, 'password_reset_by_admin' => true])->save();

        $this->actingAs($admin)->get(route('users.index', ['rol' => $u->role_id]))
            ->assertInertia(fn (Assert $p) => $p->where('usuarios.data.0.recuperacion_bloqueada', true)
                ->where('usuarios.data.0.debe_cambiar_clave', true)
                ->where('usuarios.data.0.es_propio', false));
    }

    public function test_editar_con_foto_desde_inertia_usa_post_con_method_put(): void
    {
        $admin = $this->admin();
        $u = User::factory()->supervisor()->create();

        $this->actingAs($admin)->from(route('users.index'))->withHeaders(['X-Inertia' => 'true'])
            ->post(route('users.update', $u->id), [
                '_method' => 'put', 'name' => 'Nuevo Nombre', 'email' => $u->email, 'role_id' => $u->role_id,
                'avatar' => UploadedFile::fake()->image('foto.png'),
            ])
            ->assertRedirect(route('users.index'))
            ->assertSessionHas('success', 'Usuario actualizado exitosamente.');

        $this->assertSame('Nuevo Nombre', $u->fresh()->name);
        Storage::disk('public')->assertExists($u->fresh()->avatar);
    }

    public function test_las_reglas_llegan_a_inertia_como_avisos(): void
    {
        $admin = $this->admin();
        $inertia = fn () => $this->actingAs($admin)->from(route('users.index'))->withHeaders(['X-Inertia' => 'true']);

        $inertia()->delete(route('users.destroy', $admin->id))
            ->assertSessionHas('error', 'No puedes inhabilitar tu propia cuenta.');
        $inertia()->post(route('users.reset-password', $admin->id), ['password' => 'Temporal-2026', 'password_confirmation' => 'Temporal-2026'])
            ->assertSessionHas('error', 'No puedes resetear tu propia contraseña desde este panel.');
        $inertia()->put(route('users.update', $admin->id), ['name' => $admin->name, 'email' => $admin->email, 'role_id' => Rol::firstOrCreate(['nombre' => 'Supervisor'], ['es_sistema' => true])->id])
            ->assertSessionHasErrors(['role_id' => 'No puedes quitarle el rol al último administrador activo.']);
        $this->assertTrue($admin->fresh()->isAdmin());
        $this->assertSame(1, (int) $admin->fresh()->estado);
    }

    public function test_reset_exige_confirmar_la_clave_temporal(): void
    {
        $u = User::factory()->supervisor()->create();

        $this->actingAs($this->admin())->from(route('users.index'))->withHeaders(['X-Inertia' => 'true'])
            ->post(route('users.reset-password', $u->id), ['password' => 'Temporal-2026', 'password_confirmation' => 'Otra-2026'])
            ->assertSessionHasErrors(['password' => 'Las contraseñas no coinciden.']);
        $this->assertFalse((bool) $u->fresh()->password_reset_by_admin);
    }
}
