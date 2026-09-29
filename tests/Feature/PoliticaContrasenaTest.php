<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use App\Rules\ContrasenaSegura;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Una sola política de contraseñas en todos los caminos donde se fija una:
 * entre 8 y 72 caracteres, con mayúscula, número y carácter especial.
 */
class PoliticaContrasenaTest extends TestCase
{
    use RefreshDatabase;

    private const DEBILES = ['Corta.1', 'sinmayuscula1!', 'SinNumero!!', 'SinSimbolo12'];

    public function test_la_regla_acepta_solo_contrasenas_completas(): void
    {
        foreach (self::DEBILES as $clave) {
            $this->assertTrue(Validator::make(['p' => $clave], ['p' => [new ContrasenaSegura]])->fails(), $clave);
        }
        $this->assertFalse(Validator::make(['p' => 'Buena.Clave1'], ['p' => [new ContrasenaSegura]])->fails());
    }

    public function test_cambiar_la_contrasena_desde_el_perfil_exige_la_politica(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Actual.Clave1')]);

        $this->actingAs($u)->from('/profile')->put(route('password.update'), [
            'current_password' => 'Actual.Clave1', 'password' => 'solo8letras', 'password_confirmation' => 'solo8letras',
        ])->assertSessionHasErrorsIn('updatePassword', 'password');
        $this->assertTrue(Hash::check('Actual.Clave1', $u->fresh()->password));
    }

    public function test_el_cambio_forzoso_de_la_clave_temporal_exige_la_politica(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Temporal.1'), 'password_reset_by_admin' => true]);

        $this->actingAs($u)->post(route('auth.force-password-change.process'), [
            'current_password' => 'Temporal.1', 'password' => 'solo8letras', 'password_confirmation' => 'solo8letras',
        ])->assertSessionHasErrors('password');
        $this->assertTrue((bool) $u->fresh()->password_reset_by_admin);
    }

    public function test_el_administrador_tambien_la_cumple_al_crear_y_al_asignar_clave_temporal(): void
    {
        $admin = User::factory()->create();
        $otro = User::factory()->create();

        $this->actingAs($admin)->postJson(route('users.store'), [
            'name' => 'Nuevo', 'email' => 'nuevo@atlantico.test', 'password' => 'solo8letras', 'password_confirmation' => 'solo8letras',
            'role_id' => Rol::where('nombre', 'Supervisor')->value('id'),
        ])->assertStatus(422)->assertJsonValidationErrors('password');

        $this->actingAs($admin)->postJson(route('users.reset-password', $otro->id), [
            'password' => 'solo8letras', 'password_confirmation' => 'solo8letras',
        ])->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_la_regla_entiende_unicode_y_el_tope_de_bcrypt(): void
    {
        $valida = fn ($clave) => ! Validator::make(['p' => $clave], ['p' => [new ContrasenaSegura]])->fails();

        $this->assertTrue($valida('Ñandú.2026'));     // Ñ es mayúscula
        $this->assertFalse($valida('Pandúcaña2026'));  // ñ/ú son letras, no símbolos (antes pasaba)
        $this->assertTrue($valida('Pandúcaña.2026'));
        $this->assertTrue($valida('A1.'.str_repeat('x', 69)));   // 72 bytes
        $this->assertFalse($valida('A1.'.str_repeat('x', 70)));  // 73 bytes: bcrypt lo truncaría
        $this->assertFalse($valida(['Buena.Clave1']));
    }

    /** Regresión: un arreglo en `password` daba 500 en todos los caminos. */
    public function test_un_arreglo_como_contrasena_es_un_error_de_validacion(): void
    {
        $admin = User::factory()->create();
        $arreglo = ['password' => ['x'], 'password_confirmation' => ['x']];

        $otro = User::factory()->create();
        $temporal = User::factory()->create(['password' => Hash::make('Temporal.1'), 'password_reset_by_admin' => true]);

        $this->actingAs($admin)->postJson(route('users.store'), [...$arreglo, 'name' => 'Nuevo', 'email' => 'n@atlantico.test'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->actingAs($admin)->postJson(route('users.reset-password', $otro->id), $arreglo)
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->actingAs($admin)->putJson(route('password.update'), [...$arreglo, 'current_password' => 'password'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->flushSession(); // otro usuario: otra sesión (la de antes guarda la huella de la clave del admin)
        $this->actingAs($temporal)->postJson(route('auth.force-password-change.process'), [...$arreglo, 'current_password' => 'Temporal.1'])
            ->assertStatus(422)->assertJsonValidationErrors('password');

        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->postJson(route('password.store'), [...$arreglo, 'token' => 't', 'email' => 'n@atlantico.test'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->postJson(route('recovery.reset.process'), [...$arreglo, 'token' => 't'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
    }

    public function test_las_dos_recuperaciones_exigen_la_politica(): void
    {
        // La validación va antes del token: basta con ver el error del campo.
        $this->post(route('password.store'), ['token' => 't', 'email' => 'a@atlantico.test', 'password' => 'solo8letras', 'password_confirmation' => 'solo8letras'])
            ->assertSessionHasErrors('password');
        $this->post(route('recovery.reset.process'), ['token' => 't', 'password' => 'solo8letras', 'password_confirmation' => 'solo8letras'])
            ->assertSessionHasErrors('password');
    }

    /** El texto de ayuda de las páginas React es el mismo que el de la regla. */
    public function test_el_texto_de_ayuda_de_react_coincide_con_la_regla(): void
    {
        $ts = file_get_contents(resource_path('js/lib/contrasena.ts'));
        $this->assertStringContainsString("'".ContrasenaSegura::DESCRIPCION."'", $ts);
    }
}
