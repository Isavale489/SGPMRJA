<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Errores y cambio forzoso de contraseña en Inertia (antes vistas Blade con el
 * layout del tema, que se eliminó). JSON sigue respondiendo como siempre.
 */
class PaginasErrorTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    public function test_una_ruta_que_no_existe_muestra_la_pagina_de_error(): void
    {
        $this->get('/esta-pagina-no-existe')
            ->assertNotFound()
            ->assertInertia(fn (Assert $p) => $p->component('Error')->where('status', 404));

        // Con sesión, igual.
        $this->actingAs($this->admin())->get('/tampoco-existe')
            ->assertNotFound()
            ->assertInertia(fn (Assert $p) => $p->component('Error')->where('status', 404));
    }

    public function test_sin_permiso_muestra_la_pagina_403_y_json_sigue_igual(): void
    {
        $sin = $this->usuarioSinPermisos();
        $this->actingAs($sin)->get(route('pedidos.index'))
            ->assertForbidden()
            ->assertInertia(fn (Assert $p) => $p->component('Error')->where('status', 403));

        $this->actingAs($sin)->getJson(route('pedidos.index'))->assertForbidden()->assertJsonMissingPath('component');
        $this->getJson('/no-existe-json')->assertNotFound()->assertJsonMissingPath('component');
    }

    public function test_la_sesion_vencida_con_usuario_vuelve_atras_con_aviso(): void
    {
        Route::middleware('web')->post('/_prueba-419', fn () => abort(419));

        // Con usuario: vuelve a donde estaba, con el aviso.
        $this->actingAs($this->admin())->from(route('dashboard'))->withHeaders(['X-Inertia' => 'true'])
            ->post('/_prueba-419')
            ->assertStatus(303)
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_el_login_con_la_sesion_vencida_vuelve_con_token_nuevo_y_aviso(): void
    {
        // El formulario Blade de login abierto mucho rato: back() lo recarga con un token nuevo.
        Route::middleware('web')->post('/_prueba-419', fn () => abort(419));

        $this->from(route('login'))->post('/_prueba-419', ['email' => 'ana@example.com', 'password' => 'secreta', 'respuestas' => [1 => 'Firulais']])
            ->assertStatus(303)
            ->assertRedirect(route('login'))
            ->assertSessionHas('aviso', 'La sesión expiró. Vuelve a intentarlo.')
            ->assertSessionHasInput('email', 'ana@example.com')
            ->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.respuestas');

        // El layout de acceso muestra el aviso.
        $this->withSession(['aviso' => 'La sesión expiró. Vuelve a intentarlo.'])->get(route('login'))
            ->assertOk()->assertSee('La sesión expiró. Vuelve a intentarlo.');
    }

    public function test_las_preguntas_de_recuperacion_con_la_sesion_vencida_llevan_el_aviso_al_formulario_de_correo(): void
    {
        // Con la sesión vencida se pierde el correo en recuperación: showQuestions redirige
        // al formulario de correo y el aviso no debe consumirse en ese paso intermedio.
        Route::middleware('web')->post('/_prueba-419', fn () => abort(419));

        $this->from(route('recovery.questions.show'))->post('/_prueba-419')
            ->assertRedirect(route('recovery.questions.show'));
        $this->get(route('recovery.questions.show'))->assertRedirect(route('recovery.email.show'));
        $this->get(route('recovery.email.show'))->assertOk()->assertSee('La sesión expiró. Vuelve a intentarlo.');
    }

    public function test_si_la_pagina_de_error_falla_queda_la_respuesta_estandar(): void
    {
        // Una prop compartida que revienta (p. ej. BD caída) no deja un 500 en blanco, y queda registrada.
        Exceptions::fake();
        \Inertia\Inertia::share('rota', fn () => throw new \RuntimeException('BD caída'));

        $this->get('/no-existe-tampoco')->assertNotFound()->assertDontSee('data-page', false);
        Exceptions::assertReported(\RuntimeException::class);
    }

    public function test_la_visita_inertia_sin_usuario_con_la_sesion_vencida_muestra_la_pagina_419(): void
    {
        // back() llevaría al login y el aviso se perdería.
        Route::middleware('web')->post('/_prueba-419', fn () => abort(419));

        $this->withHeaders(['X-Inertia' => 'true'])->post('/_prueba-419')
            ->assertStatus(419)
            ->assertJsonPath('component', 'Error')
            ->assertJsonPath('props.status', 419);
    }

    public function test_el_cambio_de_contrasena_temporal_es_una_pagina_inertia(): void
    {
        $u = User::factory()->create(['password' => Hash::make('Temporal.1'), 'password_reset_by_admin' => true]);

        $this->actingAs($u)->get(route('dashboard'))->assertRedirect(route('auth.force-password-change.show'));
        $this->actingAs($u)->get(route('auth.force-password-change.show'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Auth/CambioClaveObligatorio')->has('urls.guardar')->has('aviso'));

        $this->actingAs($u)->post(route('auth.force-password-change.process'), [
            'current_password' => 'Temporal.1', 'password' => 'Nueva.Clave1', 'password_confirmation' => 'Nueva.Clave1',
        ])->assertRedirect(route('profile.edit'));
        $this->assertFalse((bool) $u->fresh()->password_reset_by_admin);

        // Sin la marca, la página ya no aplica.
        $this->actingAs($u->fresh())->get(route('auth.force-password-change.show'))->assertRedirect(route('dashboard'));
    }
}
