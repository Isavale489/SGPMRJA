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

    public function test_una_orden_cancelada_no_se_edita(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo, $ana, $luis] = $this->crearOrden($admin);
        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden)));
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);

        // Antes volvía a Pendiente y eliminarla reponía el stock por segunda vez.
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden),
            $this->edicion($orden, [['id' => $ana->id, 'cantidad' => 6], ['id' => $luis->id, 'cantidad' => 4]], ['estado' => 'Pendiente']))->assertStatus(422);
        $this->assertSame('Cancelado', $orden->fresh()->estado);
    }

    public function test_el_estado_manual_respeta_lo_producido(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->crearOrden($admin);
        $equipo = [['id' => $ana->id, 'cantidad' => 6], ['id' => $luis->id, 'cantidad' => 4]];

        // Sin producir todo no se finaliza.
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden), $this->edicion($orden, $equipo, ['estado' => 'Finalizado']))
            ->assertStatus(422)->assertJsonFragment(['message' => 'No se puede finalizar: faltan 10 de 10 unidades por producir. Registra el avance primero.']);

        // Con producción no vuelve a Pendiente.
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 2, 'empleado_id' => $ana->id]);
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden), $this->edicion($orden, $equipo, ['estado' => 'Pendiente']))->assertStatus(422);
        $this->assertSame('En Proceso', $orden->fresh()->estado);

        $this->assertExito($this->actingAs($admin)->putJson(route('ordenes.update', $orden), $this->edicion($orden, $equipo, ['estado' => 'En Proceso'])));
    }

    public function test_el_reparto_no_baja_de_lo_producido(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->crearOrden($admin);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 5, 'empleado_id' => $ana->id]);

        // Ana ya hizo 5: su parte no puede quedar en 3 (quedaría con más producido que asignado).
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden),
            $this->edicion($orden->fresh(), [['id' => $ana->id, 'cantidad' => 3], ['id' => $luis->id, 'cantidad' => 7]]))->assertStatus(422);
        $this->assertEquals([$ana->id => 6, $luis->id => 4], $orden->empleadosAsignados()->get()->pluck('pivot.cantidad', 'id')->map(fn ($c) => (int) $c)->all());
    }

    public function test_el_avance_no_supera_lo_que_le_falta_a_la_orden(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana] = $this->crearOrden($admin);
        // Orden cuyos totales ya no cuadran con el reparto (datos previos).
        OrdenProduccion::whereKey($orden->id)->update(['cantidad_producida' => 9, 'estado' => 'En Proceso']);

        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 2, 'empleado_id' => $ana->id])
            ->assertStatus(422)->assertJsonFragment(['message' => 'Solo quedan 1 unidades por producir en esta orden.']);
        $this->assertSame(9, (int) $orden->fresh()->cantidad_producida);
    }

    public function test_con_produccion_ni_se_elimina_ni_se_repone_al_cancelar(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo] = $this->crearOrden($admin);
        // Pendiente con unidades producidas (datos previos a la regla de estados).
        OrdenProduccion::whereKey($orden->id)->update(['cantidad_producida' => 3]);

        $this->actingAs($admin)->deleteJson(route('ordenes.destroy', $orden))->assertStatus(422);

        $this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden))->assertStatus(422); // exige motivo de merma
        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden), ['motivo_cancelacion' => 'Tela manchada']));
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual);
    }

    public function test_las_etapas_no_devuelven_a_pendiente_una_orden_con_produccion(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana] = $this->crearOrden($admin);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 2, 'empleado_id' => $ana->id]);
        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), ['nombre' => 'Corte', 'empleados' => [['id' => $ana->id]]]));

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.subordenes.estado', [$orden, $orden->subordenes()->sole()->id]), ['estado' => 'Pendiente']));
        $this->assertSame('En Proceso', $orden->fresh()->estado);
    }

    public function test_no_se_producen_reventas_ni_pedidos_cancelados(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 50]);
        $equipo = [['id' => $this->empleado()->id, 'cantidad' => 10]];

        $reventa = $this->pedidoConLinea(10);
        $reventa->tipoProducto()->update(['requiere_produccion' => false]);
        $this->actingAs($admin)->postJson(route('ordenes.store'), $this->payload($reventa, $insumo, $equipo))
            ->assertStatus(422)->assertJsonFragment(['message' => '"línea #'.$reventa->id.'" es un producto de reventa: no se fabrica.']);
        $reventa->tipoProducto()->update(['requiere_produccion' => true]);

        $cancelado = $this->pedidoConLinea(10);
        $cancelado->pedido()->update(['estado' => 'Cancelado']);
        $this->actingAs($admin)->postJson(route('ordenes.store'), $this->payload($cancelado, $insumo, $equipo))
            ->assertStatus(422)->assertJsonFragment(['message' => 'El pedido está Cancelado: no admite nuevas órdenes de producción.']);
        $this->actingAs($admin)->postJson(route('ordenes.batch'), [
            'pedido_id' => $cancelado->pedido_id,
            'ordenes' => [$this->parte($cancelado, $insumo, $equipo, 10, 20)],
        ])->assertStatus(422);

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    public function test_no_se_repiten_empleados_ni_insumos(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 50]);
        $ana = $this->empleado();

        $this->actingAs($admin)->postJson(route('ordenes.store'), $this->payload($linea, $insumo, [['id' => $ana->id, 'cantidad' => 5], ['id' => $ana->id, 'cantidad' => 5]]))
            ->assertStatus(422)->assertJsonFragment(['message' => 'Hay empleados repetidos en el reparto.']);

        $payload = $this->payload($linea, $insumo, [['id' => $ana->id, 'cantidad' => 10]]);
        $payload['insumos'][] = ['id' => $insumo->id, 'cantidad_estimada' => 5];
        $this->actingAs($admin)->postJson(route('ordenes.store'), $payload)->assertStatus(422)->assertJsonFragment(['message' => 'Hay insumos repetidos en la orden.']);

        $this->assertSame(0, OrdenProduccion::count());
        $this->assertEquals(50, (float) $insumo->fresh()->stock_actual);
    }

    /** Regresión (revisión del fix): Calidad resta lo rechazado de producida, pero la tela ya se cortó. */
    public function test_un_reproceso_total_no_devuelve_la_orden_a_pendiente_ni_repone(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo, $ana, $luis] = $this->crearOrden($admin);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 6, 'empleado_id' => $ana->id]);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 4, 'empleado_id' => $luis->id]);
        $this->assertExito($this->actingAs($admin)->postJson(route('calidad.inspeccionar', $orden), [
            'cantidad_inspeccionada' => 10, 'cantidad_aprobada' => 0, 'cantidad_rechazada' => 10,
            'resultado' => 'rechazado', 'observaciones' => 'Talla equivocada',
            'rechazos' => [['empleado_id' => $ana->id, 'cantidad' => 6], ['empleado_id' => $luis->id, 'cantidad' => 4]],
        ]));
        $orden->refresh();
        $this->assertSame(0, (int) $orden->cantidad_producida);
        $this->assertTrue($orden->tieneProduccion());

        $equipo = [['id' => $ana->id, 'cantidad' => 6], ['id' => $luis->id, 'cantidad' => 4]];
        $this->actingAs($admin)->putJson(route('ordenes.update', $orden), $this->edicion($orden, $equipo, ['estado' => 'Pendiente']))
            ->assertStatus(422)->assertJsonFragment(['message' => 'La orden ya tiene unidades producidas (o rechazadas en Calidad): no puede volver a Pendiente.']);
        // Sigue exigiendo motivo de merma al cancelar (no repone la tela cortada).
        $this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden))->assertStatus(422)->assertJsonValidationErrors('motivo_cancelacion');

        $this->assertSame('En Proceso', $orden->fresh()->estado);
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual);
    }

    public function test_mover_o_quitar_etapas_no_saca_de_finalizado(): void
    {
        $admin = $this->admin();
        [$orden, $linea, , $ana, $luis] = $this->crearOrden($admin);
        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), ['nombre' => 'Corte', 'empleados' => [['id' => $ana->id]]]));
        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), ['nombre' => 'Costura', 'empleados' => [['id' => $luis->id]]]));
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 6, 'empleado_id' => $ana->id]);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 4, 'empleado_id' => $luis->id]);
        $this->assertSame('Finalizado', $orden->fresh()->estado);
        [$corte, $costura] = $orden->subordenes()->orderBy('id')->get()->all();

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.subordenes.estado', [$orden, $corte->id]), ['estado' => 'En Proceso']));
        $this->assertSame('Finalizado', $orden->fresh()->estado);

        $this->assertExito($this->actingAs($admin)->deleteJson(route('ordenes.subordenes.destroy', [$orden, $costura->id])));
        $this->assertSame('Finalizado', $orden->fresh()->estado);
        $this->assertSame('Completado', $linea->pedido->fresh()->estado);
    }

    public function test_quitar_una_etapa_recalcula_la_orden(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana] = $this->crearOrden($admin);
        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), ['nombre' => 'Corte', 'empleados' => [['id' => $ana->id]]]));
        $this->assertExito($this->actingAs($admin)->postJson(route('ordenes.subordenes.store', $orden), ['nombre' => 'Costura', 'empleados' => [['id' => $ana->id]]]));
        [$corte, $costura] = $orden->subordenes()->orderBy('id')->get()->all();
        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.subordenes.estado', [$orden, $costura->id]), ['estado' => 'En Proceso']));
        $this->assertSame('En Proceso', $orden->fresh()->estado);

        // Sin la etapa en marcha y sin producción, la orden vuelve a Pendiente.
        $this->assertExito($this->actingAs($admin)->deleteJson(route('ordenes.subordenes.destroy', [$orden, $costura->id])));
        $this->assertSame('Pendiente', $orden->fresh()->estado);
    }

    /** Regresión: la reposición devolvía el estimado de hoy; ahora devuelve lo que se descontó. */
    public function test_cancelar_repone_lo_descontado_aunque_el_insumo_cambie_o_se_inhabilite(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo] = $this->crearOrden($admin); // 50 → 30
        $insumo->update(['is_inventoriable' => false]);
        $insumo->delete(); // inhabilitado

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden)));
        $this->assertEquals(50, (float) Insumo::withTrashed()->find($insumo->id)->stock_actual);
        $this->assertSame(0.0, (float) $orden->insumos()->withTrashed()->first()->pivot->cantidad_utilizada);
    }

    /** Órdenes anteriores al descuento de inventario (nunca descontaron): cancelarlas no crea stock. */
    public function test_una_orden_que_nunca_desconto_no_repone_al_cancelar(): void
    {
        $admin = $this->admin();
        [$orden, , $insumo] = $this->crearOrden($admin);
        $orden->insumos()->updateExistingPivot($insumo->id, ['cantidad_utilizada' => 0]);
        $insumo->update(['stock_actual' => 30]);

        $this->assertExito($this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden)));
        $this->assertEquals(30, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(1, MovimientoInsumo::count()); // solo la salida original
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
