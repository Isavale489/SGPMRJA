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
}
