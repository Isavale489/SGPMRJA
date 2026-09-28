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
 * 8+ caracteres, con mayúscula, número y carácter especial.
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
}
