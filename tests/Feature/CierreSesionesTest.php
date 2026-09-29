<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Al cambiar la contraseña (recuperación, reset del administrador, perfil), las
 * demás sesiones abiertas del usuario se cierran (AuthenticateSession en el
 * grupo web); la de quien la cambia sigue abierta.
 */
class CierreSesionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_si_la_clave_cambia_por_otro_lado_la_sesion_abierta_se_cierra(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('dashboard'))->assertOk(); // la sesión guarda la huella de la clave

        // Otro equipo (recuperación o reset del admin) cambia la clave.
        $u->forceFill(['password' => Hash::make('Otra.Clave1')])->save();

        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_en_una_consulta_json_responde_401(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('dashboard'))->assertOk();
        $u->forceFill(['password' => Hash::make('Otra.Clave1')])->save();

        $this->getJson(route('notificaciones.sistema'))->assertUnauthorized();
    }

    public function test_quien_cambia_su_propia_clave_sigue_dentro(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('dashboard'))->assertOk();

        $this->from('/profile')->put('/password', [
            'current_password' => 'password', 'password' => 'Nueva.Clave1', 'password_confirmation' => 'Nueva.Clave1',
        ])->assertSessionHasNoErrors();

        $this->get(route('dashboard'))->assertOk();
        $this->assertAuthenticatedAs($u);
    }

    public function test_en_una_visita_inertia_recarga_completa_al_login_incluso_en_un_put(): void
    {
        $u = User::factory()->create();
        $this->actingAs($u)->get(route('dashboard'))->assertOk();
        $u->forceFill(['password' => Hash::make('Otra.Clave1')])->save();

        // Un redirect haría que el navegador repitiera el PUT contra /login (405).
        $this->withHeaders(['X-Inertia' => 'true'])->put(route('pedidos.update', 1), [])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('login'));
        $this->assertGuest();
    }

    public function test_el_cambio_forzoso_deja_dentro_a_quien_lo_hace(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Temporal.1'), 'password_reset_by_admin' => true]);
        $this->actingAs($u)->get(route('auth.force-password-change.show'))->assertOk();

        $this->post(route('auth.force-password-change.process'), [
            'current_password' => 'Temporal.1', 'password' => 'Nueva.Clave1', 'password_confirmation' => 'Nueva.Clave1',
        ])->assertRedirect(route('profile.edit'));

        $this->get(route('profile.edit'))->assertOk();
        $this->assertAuthenticatedAs($u);
    }
}
