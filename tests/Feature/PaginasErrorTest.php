<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_la_sesion_vencida_sin_usuario_muestra_la_pagina_419(): void
    {
        // El caso típico: back() llevaría al login y el aviso se perdería.
        Route::middleware('web')->post('/_prueba-419', fn () => abort(419));

        $this->post('/_prueba-419')
            ->assertStatus(419)
            ->assertInertia(fn (Assert $p) => $p->component('Error')->where('status', 419));
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
