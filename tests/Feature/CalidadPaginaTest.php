<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\ControlCalidad;
use App\Models\OrdenProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Control de Calidad: tabla agrupada por pedido, cola del
 * pedido por recarga parcial y la inspección enviada como lo hace Inertia.
 */
class CalidadPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Calidad/Index.tsx'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves); // solo el primer nivel
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    /** Orden finalizada de 10 unidades (Ana 6 / Luis 4), lista para inspeccionar. */
    private function ordenFinalizada($admin): OrdenProduccion
    {
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 50]);
        $ana = $this->empleado('Ana Pérez');
        $luis = $this->empleado('Luis Rojas');
        $this->actingAs($admin)->postJson(route('ordenes.store'), [
            'detalle_pedido_id' => $linea->id,
            'empleados' => [['id' => $ana->id, 'cantidad' => 6], ['id' => $luis->id, 'cantidad' => 4]],
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin_estimada' => now()->addWeek()->toDateString(),
            'insumos' => [['id' => $insumo->id, 'cantidad_estimada' => 20]],
        ]);
        $orden = OrdenProduccion::sole();
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 6, 'empleado_id' => $ana->id]);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 4, 'empleado_id' => $luis->id]);

        return $orden->fresh();
    }

    public function test_la_tabla_agrupa_por_pedido_y_la_cola_llega_por_recarga_parcial(): void
    {
        $admin = $this->admin();
        $orden = $this->ordenFinalizada($admin);

        $this->actingAs($admin)->get(route('calidad.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Calidad/Index')
                ->has('registros.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('PedidoEnCola')))
                ->where('registros.data.0.pedido_id', $orden->pedido_id)
                ->where('registros.data.0.ordenes', 1)
                ->where('cola', null));

        $this->actingAs($admin)->get(route('calidad.index', ['pedido' => $orden->pedido_id]))
            ->assertInertia(fn (Assert $p) => $p
                ->has('cola', 1, fn (Assert $o) => $o->hasAll($this->clavesTs('OrdenEnCola')))
                ->where('cola.0.id', $orden->id)
                ->has('cola.0.equipo', 2));
    }

    public function test_filtra_por_estado_de_calidad(): void
    {
        $admin = $this->admin();
        $orden = $this->ordenFinalizada($admin);

        $this->actingAs($admin)->get(route('calidad.index', ['estado' => 'pendiente']))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1));
        $this->actingAs($admin)->get(route('calidad.index', ['estado' => 'reinspeccion']))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 0));
        $this->actingAs($admin)->get(route('calidad.index', ['buscar' => (string) $orden->pedido_id]))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1));
    }

    public function test_un_rechazo_desde_inertia_reparte_entre_el_equipo_y_avisa(): void
    {
        $admin = $this->admin();
        $orden = $this->ordenFinalizada($admin);
        [$ana, $luis] = $orden->empleadosAsignados()->orderBy('empleado.id')->get()->all();

        $this->actingAs($admin)
            ->from(route('calidad.index', ['pedido' => $orden->pedido_id]))
            ->withHeaders($this->inertia())
            ->post(route('calidad.inspeccionar', $orden), [
                'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 7, 'cantidad_rechazada' => 3,
                'resultado' => 'rechazado', 'observaciones' => 'Costura torcida',
                'rechazos' => [['empleado_id' => $ana->id, 'cantidad' => 2], ['empleado_id' => $luis->id, 'cantidad' => 1]],
            ])
            ->assertRedirect(route('calidad.index', ['pedido' => $orden->pedido_id]))
            ->assertSessionHas('success', 'Inspección registrada correctamente.');

        $this->assertSame('rechazado', ControlCalidad::sole()->resultado);
        $this->assertSame('En Proceso', $orden->fresh()->estado);
    }

    public function test_un_error_de_la_regla_llega_a_inertia_como_error_de_validacion(): void
    {
        $admin = $this->admin();
        $orden = $this->ordenFinalizada($admin);

        $this->actingAs($admin)
            ->from(route('calidad.index'))
            ->withHeaders($this->inertia())
            ->post(route('calidad.inspeccionar', $orden), [
                'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 7, 'cantidad_rechazada' => 3,
                'resultado' => 'rechazado', 'observaciones' => 'Costura torcida',
                'rechazos' => [['empleado_id' => $orden->empleado_id, 'cantidad' => 1]], // no suma 3
            ])
            ->assertRedirect(route('calidad.index'))
            ->assertSessionHasErrors('rechazos');

        $this->assertSame(0, ControlCalidad::count());
    }
}
