<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Cotizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Regresión: una consulta JSON en segundo plano que llegaba entre el redirect
 * de guardar y la página siguiente se «comía» el aviso de éxito.
 */
class FlashConsultasJsonTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    public function test_una_consulta_json_intermedia_no_se_come_el_aviso(): void
    {
        $admin = $this->admin();
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload);
        $cot = Cotizacion::sole();
        $inertia = ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];

        $this->actingAs($admin)->withHeaders($inertia)->put(route('cotizaciones.update', $cot), $payload)->assertRedirect();
        $this->flushHeaders();

        // La proyección de insumos (fetch JSON) llega antes que la página.
        $this->actingAs($admin)->postJson(route('cotizaciones.proyeccionInsumos'), ['lineas' => []])->assertOk();

        $this->actingAs($admin)->get(route('cotizaciones.index', ['ver' => $cot->id]))
            ->assertInertia(fn (Assert $p) => $p->where('flash.success', "Cotización #{$cot->id} actualizada."));

        // Y se muestra una sola vez.
        $this->actingAs($admin)->get(route('cotizaciones.index'))
            ->assertInertia(fn (Assert $p) => $p->where('flash.success', null));
    }
}
