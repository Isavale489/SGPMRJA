<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Pantallas de acceso en Inertia (antes Blade con <x-guest-layout>): cada una
 * renderiza su página con las URL que usa, y el login entra con recarga completa.
 */
class AccesoPaginasTest extends TestCase
{
    use RefreshDatabase;

    public function test_cada_pantalla_de_acceso_es_su_pagina_inertia(): void
    {
        $pantallas = [
            'login' => ['Auth/Login', ['urls.login', 'urls.recuperar']],
            'password.request' => ['Auth/OlvideClave', ['urls.enviar', 'urls.volver']],
            'recovery.method' => ['Auth/Recuperacion/Metodo', ['urls.correo', 'urls.preguntas', 'urls.login']],
            'recovery.email.show' => ['Auth/Recuperacion/Correo', ['urls.continuar', 'urls.volver']],
            'recovery.locked' => ['Auth/Recuperacion/Bloqueo', ['tipo', 'urls.login']],
        ];
        foreach ($pantallas as $ruta => [$componente, $props]) {
            $this->get(route($ruta))->assertOk()->assertInertia(function (Assert $p) use ($componente, $props) {
                $p->component($componente);
                foreach ($props as $prop) {
                    $p->has($prop);
                }
                // Sin usuario no se consulta ni se muestra la tasa BCV.
                $p->where('tasaBcv', null)->where('auth.user', null);
            });
        }

        $this->get(route('password.reset', ['token' => 'abc', 'email' => 'ana@atlantico.test']))
            ->assertInertia(fn (Assert $p) => $p->component('Auth/RestablecerClave')->where('token', 'abc')->where('email', 'ana@atlantico.test')->has('urls.guardar'));
    }

    public function test_el_login_desde_inertia_entra_con_recarga_completa_al_destino(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Clave.Segura1')]);

        // Ruta protegida visitada antes: tras entrar vuelve ahí, con recarga completa (puede no ser Inertia).
        $this->get(route('cotizaciones.index'))->assertRedirect(route('login'));
        $this->withHeaders(['X-Inertia' => 'true'])->post(route('login'), ['email' => $u->email, 'password' => 'Clave.Segura1'])
            ->assertStatus(409)
            ->assertHeader('X-Inertia-Location', route('cotizaciones.index'));
        $this->assertAuthenticatedAs($u);
    }

    public function test_el_login_con_datos_malos_devuelve_el_error_a_la_pagina(): void
    {
        $this->from(route('login'))->withHeaders(['X-Inertia' => 'true'])->post(route('login'), ['email' => 'nadie@atlantico.test', 'password' => 'x'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_la_hora_del_bloqueo_va_en_hora_de_venezuela(): void
    {
        $hasta = now('UTC')->setTime(20, 30); // 20:30 UTC = 16:30 en Caracas
        $this->withSession(['lock_type' => 'soft', 'until' => $hasta])->get(route('recovery.locked'))
            ->assertInertia(fn (Assert $p) => $p->component('Auth/Recuperacion/Bloqueo')->where('tipo', 'soft')->where('hasta', '16:30'));

        $this->flushSession();
        $this->withSession(['lock_type' => 'hard', 'until' => $hasta])->get(route('recovery.locked'))
            ->assertInertia(fn (Assert $p) => $p->where('tipo', 'hard')->where('hasta', null));
    }

    public function test_olvide_mi_clave_confirma_el_envio_en_la_pagina(): void
    {
        $u = User::factory()->create();
        Password::shouldReceive('sendResetLink')->once()->andReturn(Password::RESET_LINK_SENT);

        $this->from(route('password.request'))->post(route('password.email'), ['email' => $u->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHas('status');
        $this->get(route('password.request'))->assertInertia(fn (Assert $p) => $p->has('flash.status'));
    }
}
