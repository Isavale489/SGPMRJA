<?php

namespace Tests\Feature\Flujos;

use App\Models\Cotizacion;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Flujo central: Cotización (Pendiente → Aprobada) → Pedido, y reversión
 * Convertida → Aprobada al eliminar el pedido (commit f3922f9).
 */
class CotizacionPedidoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function cotizacionAprobada($admin): Cotizacion
    {
        $this->assertExito($this->actingAs($admin)
            ->postJson(route('cotizaciones.store'), $this->payloadCotizacion($this->cliente()->id)));
        $cot = Cotizacion::sole();
        $this->assertSame('Pendiente', $cot->estado);

        $this->assertExito($this->actingAs($admin)
            ->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => 'Aprobada']));

        return $cot->fresh();
    }

    /** Crear el pedido desde la cotización con el abono completo en efectivo (el asistente). */
    private function convertir($admin, Cotizacion $cot)
    {
        return $this->actingAs($admin)->postJson(route('pedidos.store'), [
            'cotizacion_id' => $cot->id,
            'fecha_entrega_estimada' => now()->addDays(20)->toDateString(),
            'prioridad' => $cot->prioridad ?? 'Normal',
            // Con total 0 (cotización sin precio) no hay nada que abonar.
            'pagos' => (float) $cot->total > 0 ? [['metodo' => 'efectivo', 'monto' => (float) $cot->total]] : [],
        ]);
    }

    public function test_crear_cotizacion_guarda_detalle_y_snapshot(): void
    {
        $cot = $this->cotizacionAprobada($this->admin());

        $detalle = $cot->productos()->sole();
        $this->assertSame(12, (int) $detalle->cantidad);
        $this->assertNotEmpty($detalle->sku_snapshot);
    }

    public function test_solo_una_cotizacion_aprobada_se_convierte(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $this->payloadCotizacion($this->cliente()->id));

        $this->convertir($admin, Cotizacion::sole())
            ->assertStatus(422);

        $this->assertSame(0, Pedido::count());
    }

    public function test_convertir_crea_pedido_con_sus_lineas(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);

        $this->assertExito($this->convertir($admin, $cot));

        $this->assertSame('Convertida', $cot->fresh()->estado);
        $pedido = Pedido::sole();
        $this->assertSame($cot->id, (int) $pedido->cotizacion_id);
        $this->assertSame('Pendiente', $pedido->estado);
        $this->assertEquals((float) $cot->total, (float) $pedido->total);
        $this->assertSame(12, (int) $pedido->productos()->sole()->cantidad);
    }

    public function test_no_se_convierte_dos_veces(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $this->convertir($admin, $cot);

        $this->convertir($admin, $cot)->assertStatus(422);

        $this->assertSame(1, Pedido::count());
    }

    public function test_cotizacion_vencida_no_se_convierte_y_queda_marcada(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $cot->update(['fecha_cotizacion' => now()->subDays(30), 'fecha_validez' => now()->subDay()]);

        $this->convertir($admin, $cot)->assertStatus(422);

        // Regresión: el marcado 'Vencida' se hacía dentro de la transacción y la
        // excepción lo revertía (la cotización quedaba 'Aprobada').
        $this->assertSame('Vencida', $cot->fresh()->estado);
        $this->assertSame(0, Pedido::count());
    }

    public function test_eliminar_pedido_revierte_cotizacion_y_permite_reconvertir(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $this->convertir($admin, $cot);
        $pedido = Pedido::sole();

        $this->assertExito($this->actingAs($admin)->deleteJson(route('pedidos.destroy', $pedido)));

        $this->assertSame('Aprobada', $cot->fresh()->estado);
        $this->assertSoftDeleted($pedido);
        $this->assertNull(Pedido::withTrashed()->find($pedido->id)->cotizacion_id);

        // El índice único pedido.cotizacion_id quedó libre: se puede volver a convertir.
        $this->assertExito($this->convertir($admin, $cot));
        $this->assertSame(1, Pedido::count());
    }

    public function test_cancelar_pedido_no_revierte_la_cotizacion(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $this->convertir($admin, $cot);

        $this->actingAs($admin)->patchJson(route('pedidos.cancelar', Pedido::sole()));

        $this->assertSame('Cancelado', Pedido::sole()->estado);
        $this->assertSame('Convertida', $cot->fresh()->estado);
    }

    /** Payload con una línea con bordado: base 10 + (2 × 3) de bordado = 16 por unidad. */
    private function payloadConBordado(int $clienteId): array
    {
        $payload = $this->payloadCotizacion($clienteId, 5);
        $payload['productos'][0] = array_merge($payload['productos'][0], ['precio_unitario' => 10, 'lleva_bordado' => true, 'descripcion' => 'Logo en el pecho', 'bordados' => [[
            'ubicacion_bordado_id' => \App\Models\BordadoUbicacion::query()->value('id'),
            'nombre_aplicado' => 'Pecho izquierdo', 'precio_aplicado' => 2, 'cantidad' => 3, 'es_personalizada' => false,
        ]]]);

        return $payload;
    }

    public function test_los_bordados_suman_al_precio_unitario_y_al_total(): void
    {
        $this->seed(\Database\Seeders\BordadoUbicacionSeeder::class);
        $admin = $this->admin();
        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.store'), $this->payloadConBordado($this->cliente()->id)));

        $cot = Cotizacion::sole();
        $detalle = $cot->productos()->with('bordados')->sole();
        $this->assertEquals(16, (float) $detalle->precio_unitario);
        $this->assertEquals(80, (float) $cot->total);
        $this->assertTrue((bool) $detalle->lleva_bordado);
        $this->assertSame('Logo en el pecho', $detalle->descripcion);
        $this->assertSame(3, (int) $detalle->bordados->sole()->cantidad);
    }

    public function test_el_maximo_de_bordados_por_producto_se_valida_en_el_servidor(): void
    {
        $this->seed(\Database\Seeders\BordadoUbicacionSeeder::class);
        $admin = $this->admin();
        $payload = $this->payloadConBordado($this->cliente()->id);
        $payload['productos'][0]['bordados'][0]['cantidad'] = (int) parametro('cotizaciones.max_bordados_producto') + 1;

        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload)
            ->assertStatus(422)->assertJsonValidationErrors('productos.0.bordados');
        $this->assertSame(0, Cotizacion::count());
    }

    /** El pedido copia en el servidor las líneas de la cotización, con sus bordados (también sin logo: 8177e91). */
    public function test_el_pedido_copia_las_lineas_y_los_bordados_de_la_cotizacion(): void
    {
        $this->seed(\Database\Seeders\BordadoUbicacionSeeder::class);
        $admin = $this->admin();
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $this->payloadConBordado($this->cliente()->id));
        $cot = Cotizacion::sole();
        $this->actingAs($admin)->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => 'Aprobada']);

        $this->assertExito($this->convertir($admin, $cot->fresh()));

        $linea = Pedido::sole()->productos()->with('bordados')->sole();
        $this->assertSame(5, (int) $linea->cantidad);
        $this->assertEquals(16, (float) $linea->precio_unitario);
        $this->assertSame('Logo en el pecho', $linea->descripcion);
        $this->assertNull($linea->bordados->sole()->logo_id); // bordado sin logo: antes el pedido lo rechazaba
        $this->assertSame(3, (int) $linea->bordados->sole()->cantidad);
    }

    /** Contrato JSON de las mutaciones (clientes jQuery). */
    public function test_contrato_json_de_las_mutaciones(): void
    {
        $admin = $this->admin();
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload)
            ->assertOk()->assertExactJson(['success' => 'Cotización creada exitosamente.']);
        $cot = Cotizacion::sole();

        $payload['productos'][0]['cantidad'] = 20;
        $this->actingAs($admin)->putJson(route('cotizaciones.update', $cot), $payload + ['estado' => 'Pendiente'])
            ->assertOk()->assertExactJson(['success' => 'Cotización actualizada exitosamente.']);
        $this->assertSame(20, (int) $cot->productos()->sole()->cantidad);

        $this->actingAs($admin)->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => 'Aprobada'])
            ->assertOk()->assertExactJson(['success' => 'Estado actualizado a: Aprobada', 'estado' => 'Aprobada']);

        $this->actingAs($admin)->deleteJson(route('cotizaciones.destroy', $cot))
            ->assertOk()->assertExactJson(['success' => 'Cotización eliminada exitosamente.']);
        $this->assertSoftDeleted($cot);
    }

    public function test_reactivar_una_vencida_renueva_la_validez_y_la_tasa(): void
    {
        $admin = $this->admin();
        \App\Models\TasaCambio::create(['moneda' => 'USD', 'valor' => 50, 'fecha_bcv' => today(), 'fuente' => 'test']);
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $this->payloadCotizacion($this->cliente()->id));
        $cot = Cotizacion::sole();
        $cot->update(['estado' => 'Vencida', 'fecha_cotizacion' => now()->subDays(40), 'fecha_validez' => now()->subDays(20), 'tasa_cambio_valor' => 30]);

        $this->actingAs($admin)->postJson(route('cotizaciones.reactivar', $cot))
            ->assertOk()->assertJsonPath('success', 'Cotización reactivada correctamente. Nueva validez: '.Cotizacion::diasVigencia().' días.');

        $cot->refresh();
        $this->assertSame('Pendiente', $cot->estado);
        $this->assertSame(now()->addDays(Cotizacion::diasVigencia())->toDateString(), $cot->fecha_validez->toDateString());
        $this->assertEquals(50, (float) $cot->tasa_cambio_valor);
    }
}
