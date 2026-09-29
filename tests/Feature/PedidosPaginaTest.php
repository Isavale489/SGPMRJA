<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Cotizacion;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de Pedidos: listado con «Ver» por recarga parcial y el
 * asistente (crear desde una cotización / editar pagos y entrega). Las claves de
 * cada prop se comparan con resources/js/pages/Pedidos/tipos.ts.
 */
class PedidosPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz, string $archivo = 'js/pages/Pedidos/tipos.ts'): array
    {
        $ts = file_get_contents(resource_path($archivo));
        preg_match("/export interface {$interfaz}(?: extends (\\w+))? \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[2] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return ! empty($m[1]) ? array_merge($this->clavesTs($m[1], $archivo), $claves[1]) : $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    private function cotizacionAprobada($admin): Cotizacion
    {
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $payload['productos'][0]['precio_unitario'] = 10;
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload);
        $cot = Cotizacion::latest('id')->first();
        $cot->update(['estado' => 'Aprobada']);

        return $cot->fresh();
    }

    private function pedido($admin): Pedido
    {
        $cot = $this->cotizacionAprobada($admin);
        $this->assertExito($this->actingAs($admin)->postJson(route('pedidos.store'), [
            'cotizacion_id' => $cot->id, 'fecha_entrega_estimada' => now()->addDays(20)->toDateString(), 'prioridad' => 'Normal',
            'pagos' => [['metodo' => 'efectivo', 'monto' => 60]],
        ]));

        return Pedido::latest('id')->firstOrFail();
    }

    public function test_el_listado_y_el_ver_cumplen_el_contrato(): void
    {
        $admin = $this->admin();
        $p = $this->pedido($admin);

        $this->actingAs($admin)->get(route('pedidos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $x) => $x->component('Pedidos/Index')
                ->has('registros.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('PedidoFila')))
                ->where('detalle', null));

        $this->actingAs($admin)->get(route('pedidos.index', ['ver' => $p->id]))
            ->assertInertia(fn (Assert $x) => $x
                ->has('detalle', fn (Assert $d) => $d->hasAll($this->clavesTs('PedidoDetalle'))->etc())
                ->has('detalle.grupos.0', fn (Assert $g) => $g->hasAll($this->clavesTs('GrupoCotizacion', 'js/pages/Cotizaciones/tipos.ts')))
                ->has('detalle.pagos.0', fn (Assert $g) => $g->hasAll($this->clavesTs('Pago')))
                ->where('detalle.id', $p->id)
                ->where('detalle.formalizado', true));
    }

    public function test_el_asistente_de_alta_y_edicion_cumple_el_contrato(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);

        $this->actingAs($admin)->get(route('pedidos.create', ['cotizacion' => $cot->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $x) => $x->component('Pedidos/Formulario')
                ->hasAll($this->clavesTs('PaginaFormularioPedido'))
                ->where('pedido', null)
                ->has('cotizaciones', 1, fn (Assert $c) => $c->hasAll($this->clavesTs('CotizacionDisponible')))
                ->has('cotizacion', fn (Assert $c) => $c->hasAll($this->clavesTs('CotizacionElegida')))
                ->where('cotizacionPedida', $cot->id));

        $p = $this->pedido($admin);
        $this->actingAs($admin)->get(route('pedidos.edit', $p))
            ->assertOk()
            ->assertInertia(fn (Assert $x) => $x->component('Pedidos/Formulario')
                ->has('pedido', fn (Assert $d) => $d->hasAll($this->clavesTs('PedidoEditable'))->etc())
                ->where('cotizaciones', [])
                ->where('urls.guardar', route('pedidos.update', $p, absolute: false)));
    }

    public function test_el_enlace_viejo_desde_cotizaciones_lleva_al_asistente(): void
    {
        $this->actingAs($this->admin())->get('/pedidos?convertir=7')->assertRedirect(route('pedidos.create', ['cotizacion' => 7]));
    }

    public function test_guardar_desde_inertia_vuelve_al_listado_con_la_ficha_abierta(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $this->actingAs($admin)->withHeaders($this->inertia())
            ->post(route('pedidos.store'), ['cotizacion_id' => $cot->id, 'fecha_entrega_estimada' => now()->addDays(20)->toDateString(), 'prioridad' => 'Normal', 'pagos' => [['metodo' => 'efectivo', 'monto' => 60]]])
            ->assertRedirect(route('pedidos.index', ['ver' => Pedido::sole()->id]))
            ->assertSessionHas('success');

        // Una regla de negocio (abono) llega como error del formulario, no como 500 ni JSON.
        $otra = $this->cotizacionAprobada($admin);
        $this->actingAs($admin)->withHeaders($this->inertia())
            ->post(route('pedidos.store'), ['cotizacion_id' => $otra->id, 'fecha_entrega_estimada' => now()->addDays(20)->toDateString(), 'prioridad' => 'Normal', 'pagos' => [['metodo' => 'efectivo', 'monto' => 1]]])
            ->assertSessionHasErrors('general');
    }

    public function test_sin_permiso_no_se_entra(): void
    {
        $sin = $this->usuarioSinPermisos();
        $this->actingAs($sin)->get(route('pedidos.index'))->assertForbidden();
        $this->actingAs($sin)->get(route('pedidos.create'))->assertForbidden();
    }
}
