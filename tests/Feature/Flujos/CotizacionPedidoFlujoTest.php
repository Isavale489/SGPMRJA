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
}
