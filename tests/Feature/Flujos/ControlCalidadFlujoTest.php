<?php

namespace Tests\Feature\Flujos;

use App\Models\ControlCalidad;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\OrdenProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Flujo Control de Calidad (FEAT-006): solo se inspeccionan órdenes finalizadas;
 * conforme → queda finalizada; rechazo → reproceso (vuelve a En Proceso).
 * Invariante: la inspección NO toca stock.
 */
class ControlCalidadFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    /** Orden de 10 unidades (Ana 6 / Luis 4) ya producida y Finalizada. */
    private function ordenFinalizada($admin): array
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

        $orden->refresh();
        $this->assertSame('Finalizado', $orden->estado);
        $this->assertSame('Completado', $linea->pedido->fresh()->estado);

        return [$orden, $linea, $insumo, $ana, $luis];
    }

    public function test_solo_se_inspeccionan_ordenes_finalizadas(): void
    {
        $admin = $this->admin();
        [$orden] = $this->ordenFinalizada($admin);
        $orden->update(['estado' => 'En Proceso']);

        $this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 10, 'cantidad_rechazada' => 0, 'resultado' => 'aprobado',
        ])->assertStatus(422);

        $this->assertSame(0, ControlCalidad::count());
    }

    public function test_inspeccion_conforme_deja_la_orden_finalizada_y_no_toca_stock(): void
    {
        $admin = $this->admin();
        [$orden, $linea, $insumo] = $this->ordenFinalizada($admin);
        $movimientos = MovimientoInsumo::count();

        $this->assertExito($this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 10, 'cantidad_rechazada' => 0, 'resultado' => 'aprobado',
        ]));

        $this->assertSame('aprobado', ControlCalidad::sole()->resultado);
        $this->assertSame('Finalizado', $orden->fresh()->estado);
        $this->assertSame('Completado', $linea->pedido->fresh()->estado);
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual);
        $this->assertSame($movimientos, MovimientoInsumo::count());
    }

    public function test_rechazo_reabre_la_orden_para_reproceso(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->ordenFinalizada($admin);

        $this->assertExito($this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 7, 'cantidad_rechazada' => 3,
            'resultado' => 'rechazado', 'observaciones' => 'Costura torcida',
            'rechazos' => [['empleado_id' => $ana->id, 'cantidad' => 2], ['empleado_id' => $luis->id, 'cantidad' => 1]],
        ]));

        $orden->refresh();
        $this->assertSame('rechazado', ControlCalidad::sole()->resultado);
        $this->assertSame('En Proceso', $orden->estado);
        $this->assertSame(7, (int) $orden->cantidad_producida);
        $this->assertSame(3, (int) $orden->cantidad_defectuosa);
        $this->assertNull($orden->fecha_fin_real);

        $pivot = $orden->empleadosAsignados()->get()->keyBy('id');
        $this->assertSame(4, (int) $pivot[$ana->id]->pivot->cantidad_producida);
        $this->assertSame(2, (int) $pivot[$ana->id]->pivot->cantidad_defectuosa);
        $this->assertSame(3, (int) $pivot[$luis->id]->pivot->cantidad_producida);
        // Invariante: total de la orden == suma por empleado.
        $this->assertSame((int) $orden->cantidad_producida, $pivot->sum(fn ($e) => (int) $e->pivot->cantidad_producida));
    }

    public function test_rechazo_devuelve_el_pedido_a_procesando(): void
    {
        $admin = $this->admin();
        [$orden, $linea, , $ana, $luis] = $this->ordenFinalizada($admin);

        $this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 7, 'cantidad_rechazada' => 3,
            'resultado' => 'rechazado', 'observaciones' => 'Costura torcida',
            'rechazos' => [['empleado_id' => $ana->id, 'cantidad' => 2], ['empleado_id' => $luis->id, 'cantidad' => 1]],
        ]);

        // Regresión: el pedido quedaba 'Completado' con órdenes en reproceso
        // (docs/conventions/business-flows.md — compuerta de calidad).
        $this->assertSame('Procesando', $linea->pedido->fresh()->estado);
    }

    public function test_el_reproceso_vuelve_a_finalizar_la_orden(): void
    {
        $admin = $this->admin();
        [$orden, $linea, , $ana, $luis] = $this->ordenFinalizada($admin);
        $this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 7, 'cantidad_rechazada' => 3,
            'resultado' => 'rechazado', 'observaciones' => 'Costura torcida',
            'rechazos' => [['empleado_id' => $ana->id, 'cantidad' => 2], ['empleado_id' => $luis->id, 'cantidad' => 1]],
        ]);

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 2, 'empleado_id' => $ana->id]));
        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 1, 'empleado_id' => $luis->id]));

        $this->assertSame('Finalizado', $orden->fresh()->estado);
        $this->assertSame('Completado', $linea->pedido->fresh()->estado);
    }

    public function test_con_equipo_la_atribucion_del_rechazo_debe_cuadrar(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana] = $this->ordenFinalizada($admin);

        $this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 7, 'cantidad_rechazada' => 3,
            'resultado' => 'rechazado', 'observaciones' => 'Costura torcida',
            'rechazos' => [['empleado_id' => $ana->id, 'cantidad' => 1]], // suma 1 ≠ 3
        ])->assertStatus(422)->assertJsonValidationErrors('rechazos');

        $this->assertSame(0, ControlCalidad::count());
        $this->assertSame('Finalizado', $orden->fresh()->estado);
    }

    public function test_las_cantidades_deben_cuadrar(): void
    {
        $admin = $this->admin();
        [$orden] = $this->ordenFinalizada($admin);

        $this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 5, 'cantidad_rechazada' => 0, 'resultado' => 'aprobado',
        ])->assertStatus(422);

        $this->assertSame(0, ControlCalidad::count());
    }

    public function test_sin_permiso_no_se_inspecciona(): void
    {
        [$orden] = $this->ordenFinalizada($this->admin());

        $this->actingAs($this->usuarioSinPermisos())->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 10, 'cantidad_rechazada' => 0, 'resultado' => 'aprobado',
        ])->assertForbidden();

        $this->assertSame(0, ControlCalidad::count());
    }
}
