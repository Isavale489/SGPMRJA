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

        $this->actingAs($admin)
            ->postJson(route('cotizaciones.convertirAPedido', Cotizacion::sole()))
            ->assertStatus(422);

        $this->assertSame(0, Pedido::count());
    }

    public function test_convertir_crea_pedido_con_sus_lineas(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);

        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot)));

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
        $this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot));

        $this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot))->assertStatus(422);

        $this->assertSame(1, Pedido::count());
    }

    public function test_cotizacion_vencida_no_se_convierte_y_queda_marcada(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $cot->update(['fecha_cotizacion' => now()->subDays(30), 'fecha_validez' => now()->subDay()]);

        $this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot))->assertStatus(422);

        // Regresión: el marcado 'Vencida' se hacía dentro de la transacción y la
        // excepción lo revertía (la cotización quedaba 'Aprobada').
        $this->assertSame('Vencida', $cot->fresh()->estado);
        $this->assertSame(0, Pedido::count());
    }

    public function test_eliminar_pedido_revierte_cotizacion_y_permite_reconvertir(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot));
        $pedido = Pedido::sole();

        $this->assertExito($this->actingAs($admin)->deleteJson(route('pedidos.destroy', $pedido)));

        $this->assertSame('Aprobada', $cot->fresh()->estado);
        $this->assertSoftDeleted($pedido);
        $this->assertNull(Pedido::withTrashed()->find($pedido->id)->cotizacion_id);

        // El índice único pedido.cotizacion_id quedó libre: se puede volver a convertir.
        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot)));
        $this->assertSame(1, Pedido::count());
    }

    public function test_cancelar_pedido_no_revierte_la_cotizacion(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacionAprobada($admin);
        $this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot));

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

    /** Contrato JSON que consume el asistente de Pedidos (Blade): pedidos/scripts/main.blade.php. */
    public function test_contrato_json_de_datos_para_pedido(): void
    {
        $this->seed(\Database\Seeders\BordadoUbicacionSeeder::class);
        $admin = $this->admin();
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $this->payloadConBordado($this->cliente()->id));
        $cot = Cotizacion::sole();
        $this->actingAs($admin)->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => 'Aprobada']);

        $this->actingAs($admin)->getJson(route('cotizaciones.datosParaPedido', $cot))
            ->assertOk()
            ->assertJsonStructure([
                'cotizacion_id', 'cliente_id', 'total',
                'cliente' => ['id', 'nombre', 'apellido', 'email', 'telefono', 'documento'],
                'productos' => [['producto_id', 'tipo_producto_id', 'insumo_tela_id', 'atributo_valor_ids', 'sku', 'imagen_url', 'producto_nombre',
                    'cantidad', 'descripcion', 'lleva_bordado', 'nombre_logo', 'recargo_bordado_unitario', 'ubicacion_logo', 'cantidad_logo',
                    'talla_id', 'color_id', 'genero_id', 'precio_unitario',
                    'bordados' => [['ubicacion_bordado_id', 'logo_id', 'nombre_aplicado', 'nombre_logo', 'nombre_logo_aplicado', 'es_personalizada', 'cantidad', 'precio_aplicado']]]],
            ])
            ->assertJsonPath('cotizacion_id', $cot->id)
            ->assertJsonPath('productos.0.recargo_bordado_unitario', 6)
            ->assertJsonPath('productos.0.cantidad', 5);
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
