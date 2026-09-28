<?php

namespace Tests\Feature\Flujos;

use App\Models\DetallePedido;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\OrdenProduccion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Flujo Órdenes de Producción: crear (descuenta insumos) → avance por empleado
 * → Finalizado, con cancelación/eliminación que reponen stock solo en Pendiente.
 */
class OrdenProduccionFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function payload(DetallePedido $linea, Insumo $insumo, array $empleados, ?int $cantidad = null, float $consumo = 20): array
    {
        return array_filter([
            'detalle_pedido_id' => $linea->id,
            'empleados' => $empleados,
            'cantidad' => $cantidad,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin_estimada' => now()->addWeek()->toDateString(),
            'insumos' => [['id' => $insumo->id, 'cantidad_estimada' => $consumo]],
        ], fn ($v) => $v !== null);
    }

    /** Orden de 10 unidades repartidas 6/4 entre dos empleados. */
    private function crearOrden($admin, ?DetallePedido $linea = null, ?Insumo $insumo = null): array
    {
        $linea ??= $this->pedidoConLinea(10);
        $insumo ??= $this->insumo(['stock_actual' => 50]);
        $ana = $this->empleado('Ana Pérez');
        $luis = $this->empleado('Luis Rojas');

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.store'), $this->payload(
            $linea, $insumo, [['id' => $ana->id, 'cantidad' => 6], ['id' => $luis->id, 'cantidad' => 4]]
        )));

        return [OrdenProduccion::sole(), $linea, $insumo, $ana, $luis];
    }

    public function test_crear_orden_descuenta_insumos_y_reparte_el_equipo(): void
    {
        [$orden, , $insumo, $ana, $luis] = $this->crearOrden($this->admin());

        $this->assertSame('Pendiente', $orden->estado);
        $this->assertSame(10, (int) $orden->cantidad_solicitada);
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual);
        $this->assertSame('Salida', MovimientoInsumo::sole()->tipo_movimiento);
        $reparto = $orden->empleadosAsignados()->get()->pluck('pivot.cantidad', 'id')->map(fn ($c) => (int) $c)->all();
        $this->assertEquals([$ana->id => 6, $luis->id => 4], $reparto);
    }

    public function test_sin_abono_minimo_no_se_produce(): void
    {
        $linea = $this->pedidoConLinea(10, abono: 10); // 10 % < mínimo
        $insumo = $this->insumo(['stock_actual' => 50]);

        $this->actingAs($this->admin())->postJson(route('ordenes.store'), $this->payload(
            $linea, $insumo, [['id' => $this->empleado()->id, 'cantidad' => 10]]
        ))->assertStatus(422);

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    public function test_stock_insuficiente_no_crea_nada(): void
    {
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 5]);

        $this->actingAs($this->admin())->postJson(route('ordenes.store'), $this->payload(
            $linea, $insumo, [['id' => $this->empleado()->id, 'cantidad' => 10]]
        ))->assertStatus(422)->assertJsonStructure(['faltantes']);

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(5, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(0, MovimientoInsumo::count());
    }

    public function test_no_se_asignan_mas_unidades_que_las_de_la_linea(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 100]);
        $emp = $this->empleado();

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.store'),
            $this->payload($linea, $insumo, [['id' => $emp->id, 'cantidad' => 7]], cantidad: 7)));

        $this->actingAs($admin)->postJson(route('ordenes.store'),
            $this->payload($linea, $insumo, [['id' => $emp->id, 'cantidad' => 4]], cantidad: 4))
            ->assertStatus(422);

        $this->assertSame(1, OrdenProduccion::count());
    }

    public function test_el_reparto_debe_sumar_la_cantidad_de_la_orden(): void
    {
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 50]);

        $this->actingAs($this->admin())->postJson(route('ordenes.store'), $this->payload(
            $linea, $insumo, [['id' => $this->empleado()->id, 'cantidad' => 3]]
        ))->assertStatus(422);

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    public function test_avance_por_empleado_hasta_finalizar_completa_el_pedido(): void
    {
        $admin = $this->admin();
        [$orden, $linea, , $ana, $luis] = $this->crearOrden($admin);

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.avance', $orden),
            ['cantidad_producida' => 6, 'empleado_id' => $ana->id]));
        $this->assertSame('En Proceso', $orden->fresh()->estado);
        $this->assertSame('Procesando', $linea->pedido->fresh()->estado);

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.avance', $orden),
            ['cantidad_producida' => 4, 'empleado_id' => $luis->id]));

        $orden->refresh();
        $this->assertSame('Finalizado', $orden->estado);
        $this->assertNotNull($orden->fecha_fin_real);
        $this->assertSame('Completado', $linea->pedido->fresh()->estado);
    }

    public function test_avance_respeta_el_tope_de_cada_empleado(): void
    {
        $admin = $this->admin();
        [$orden, , , , $luis] = $this->crearOrden($admin);

        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden),
            ['cantidad_producida' => 5, 'empleado_id' => $luis->id]) // Luis tiene 4
            ->assertStatus(422);

        $this->assertSame(0, (int) $orden->fresh()->cantidad_producida);
    }

    public function test_con_equipo_el_avance_exige_empleado(): void
    {
        $admin = $this->admin();
        [$orden] = $this->crearOrden($admin);

        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 1])
            ->assertStatus(422)->assertJsonValidationErrors('empleado_id');
    }

    public function test_cancelar_en_pendiente_repone_stock(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo] = $this->crearOrden($admin);

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden)));

        $this->assertSame('Cancelado', $orden->fresh()->estado);
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    public function test_cancelar_en_proceso_exige_motivo_y_no_repone_la_merma(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo, $ana] = $this->crearOrden($admin);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 2, 'empleado_id' => $ana->id]);

        $this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden))
            ->assertStatus(422)->assertJsonValidationErrors('motivo_cancelacion');

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden),
            ['motivo_cancelacion' => 'Cliente anuló el diseño']));

        $this->assertSame('Cancelado', $orden->fresh()->estado);
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual); // merma: no vuelve
    }

    public function test_eliminar_solo_en_pendiente_y_repone_stock(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo] = $this->crearOrden($admin);

        $this->assertExito($this->actingAs($admin)->deleteJson(route('ordenes.destroy', $orden)));

        $this->assertNull(OrdenProduccion::find($orden->id));
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    public function test_no_se_elimina_una_orden_en_proceso(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana] = $this->crearOrden($admin);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 1, 'empleado_id' => $ana->id]);

        $this->actingAs($admin)->deleteJson(route('ordenes.destroy', $orden))->assertStatus(422);

        $this->assertNotNull(OrdenProduccion::find($orden->id));
    }

    /** Parte de un lote (storeBatch): una orden de la línea con su equipo. */
    private function parte(DetallePedido $linea, Insumo $insumo, array $empleados, int $cantidad, float $consumo): array
    {
        return [
            'detalle_pedido_id' => $linea->id,
            'empleados' => $empleados,
            'cantidad' => $cantidad,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin_estimada' => now()->addWeek()->toDateString(),
            'insumos' => [['id' => $insumo->id, 'cantidad_estimada' => $consumo]],
        ];
    }

    public function test_un_lote_reparte_una_linea_en_varias_ordenes(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 50]);
        [$ana, $luis] = [$this->empleado('Ana Pérez'), $this->empleado('Luis Rojas')];

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.batch'), [
            'pedido_id' => $linea->pedido_id,
            'ordenes' => [
                $this->parte($linea, $insumo, [['id' => $ana->id, 'cantidad' => 6]], 6, 12),
                $this->parte($linea, $insumo, [['id' => $luis->id, 'cantidad' => 4]], 4, 8),
            ],
        ]));

        $this->assertSame([6, 4], OrdenProduccion::orderBy('id')->pluck('cantidad_solicitada')->map(fn ($c) => (int) $c)->all());
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(2, MovimientoInsumo::count());
    }

    public function test_un_lote_que_se_pasa_de_la_linea_no_crea_ninguna(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 50]);
        $emp = $this->empleado();

        $this->actingAs($admin)->postJson(route('ordenes.batch'), [
            'pedido_id' => $linea->pedido_id,
            'ordenes' => [
                $this->parte($linea, $insumo, [['id' => $emp->id, 'cantidad' => 8]], 8, 1),
                $this->parte($linea, $insumo, [['id' => $emp->id, 'cantidad' => 3]], 3, 1),
            ],
        ])->assertStatus(422);

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    public function test_un_lote_no_acepta_lineas_de_otro_pedido(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $ajena = $this->pedidoConLinea(5);
        $insumo = $this->insumo(['stock_actual' => 50]);
        $emp = $this->empleado();

        $this->actingAs($admin)->postJson(route('ordenes.batch'), [
            'pedido_id' => $linea->pedido_id,
            'ordenes' => [$this->parte($ajena, $insumo, [['id' => $emp->id, 'cantidad' => 5]], 5, 1)],
        ])->assertStatus(422);

        $this->assertSame(0, OrdenProduccion::count());
    }

    public function test_un_lote_sin_stock_devuelve_lo_que_falta_comprar_en_total(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 15]);
        $emp = $this->empleado();

        $this->actingAs($admin)->postJson(route('ordenes.batch'), [
            'pedido_id' => $linea->pedido_id,
            'ordenes' => [
                $this->parte($linea, $insumo, [['id' => $emp->id, 'cantidad' => 6]], 6, 12),
                $this->parte($linea, $insumo, [['id' => $emp->id, 'cantidad' => 4]], 4, 8),
            ],
        ])->assertStatus(422)
            ->assertJsonPath('faltantes.0.insumo_id', $insumo->id)
            ->assertJsonPath('faltantes.0.cantidad', 5); // 20 − 15

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(15, (float) $insumo->fresh()->stock_actual);
    }

    private function edicion(OrdenProduccion $orden, array $empleados, array $extra = []): array
    {
        return array_merge([
            'empleados' => $empleados,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin_estimada' => now()->addDays(10)->toDateString(),
            'estado' => $orden->estado,
            'notas' => 'Prioridad alta',
        ], $extra);
    }

    public function test_editar_rebalancea_el_equipo_y_la_cantidad_en_pendiente(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->crearOrden($admin); // 10 unidades: 6 / 4

        $this->assertExito($this->actingAs($admin)->putJson(route('ordenes.update', $orden),
            $this->edicion($orden, [['id' => $ana->id, 'cantidad' => 3], ['id' => $luis->id, 'cantidad' => 5]], ['cantidad' => 8])));

        $orden->refresh();
        $this->assertSame(8, (int) $orden->cantidad_solicitada);
        $this->assertSame('Prioridad alta', $orden->notas);
        $this->assertEquals([$ana->id => 3, $luis->id => 5], $orden->empleadosAsignados()->get()->pluck('pivot.cantidad', 'id')->map(fn ($c) => (int) $c)->all());

        // No más que la línea (10).
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden),
            $this->edicion($orden, [['id' => $ana->id, 'cantidad' => 11]], ['cantidad' => 11]))->assertStatus(422);
        $this->assertSame(8, (int) $orden->fresh()->cantidad_solicitada);
    }

    public function test_en_proceso_no_cambia_la_cantidad_ni_se_quita_a_quien_produjo(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->crearOrden($admin);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 2, 'empleado_id' => $ana->id]);
        $orden->refresh();

        $this->actingAs($admin)->putJson(route('ordenes.update', $orden),
            $this->edicion($orden, [['id' => $ana->id, 'cantidad' => 4], ['id' => $luis->id, 'cantidad' => 4]], ['cantidad' => 8]))->assertStatus(422);
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden),
            $this->edicion($orden, [['id' => $luis->id, 'cantidad' => 10]]))->assertStatus(422);

        $this->assertSame(10, (int) $orden->fresh()->cantidad_solicitada);
        $this->assertSame(2, $orden->empleadosAsignados()->count());
    }

    public function test_etapas_con_equipo_y_la_ultima_exige_produccion_completa(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->crearOrden($admin);

        $this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), [
            'nombre' => 'Corte', 'empleados' => [['id' => $ana->id], ['id' => $ana->id]],
        ])->assertStatus(422); // empleado repetido

        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), [
            'nombre' => 'Corte', 'cantidad_asignada' => 10, 'empleados' => [['id' => $ana->id, 'rol' => 'Cortadora'], ['id' => $luis->id]],
        ]));
        $etapa = $orden->subordenes()->sole();
        $this->assertSame(2, $etapa->empleados()->count());

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.subordenes.estado', [$orden, $etapa->id]), ['estado' => 'En Proceso']));
        $this->assertSame('En Proceso', $orden->fresh()->estado);

        // Única etapa activa y sin producción registrada: no puede finalizar la orden.
        $this->actingAs($admin)->patchJson(route('ordenes.subordenes.estado', [$orden, $etapa->id]), ['estado' => 'Finalizado'])->assertStatus(422);
        $this->assertSame('En Proceso', $etapa->fresh()->estado);

        $this->assertExito($this->actingAs($admin)->deleteJson(route('ordenes.subordenes.destroy', [$orden, $etapa->id])));
        $this->assertSame(0, $orden->subordenes()->count());
    }

    public function test_los_pdf_se_generan(): void
    {
        $admin = $this->admin();
        [$orden] = $this->crearOrden($admin);

        foreach ([route('ordenes.pdf', $orden), route('ordenes.reporte.pdf', ['estado' => 'Pendiente', 'orden' => 'progreso_desc'])] as $url) {
            $r = $this->actingAs($admin)->get($url);
            $r->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $r->headers->get('Content-Type'));
        }
    }
}
