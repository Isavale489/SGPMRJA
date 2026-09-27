<?php

namespace Tests\Feature\Flujos;

use App\Models\Compra;
use App\Models\MovimientoInsumo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Flujo Compras: borrador → procesar (Entrada de stock) → anular (Salida) → clonar.
 */
class CompraFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function payload(int $proveedorId, int $insumoId, float $cantidad = 10): array
    {
        return [
            'proveedor_id' => $proveedorId,
            'fecha_compra' => now()->toDateString(),
            'tasa_cambio' => 40,
            'items' => [
                ['insumo_id' => $insumoId, 'cantidad' => $cantidad, 'costo_unitario_bs' => 80, 'aplica_iva' => 1],
            ],
        ];
    }

    public function test_el_borrador_no_mueve_inventario(): void
    {
        $insumo = $this->insumo(['stock_actual' => 5]);

        $this->assertExito($this->actingAs($this->admin())
            ->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id)));

        $compra = Compra::sole();
        $this->assertSame('borrador', $compra->estado);
        $this->assertEquals(5, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(0, MovimientoInsumo::count());
        // Costo en Bs convertido a USD con la tasa: 80 / 40 = 2.
        $this->assertEquals(2, (float) $compra->detalles()->sole()->costo_unitario);
    }

    public function test_procesar_suma_stock_y_registra_entrada(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 5]);
        $this->actingAs($admin)->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id));
        $compra = Compra::sole();

        $this->assertExito($this->actingAs($admin)->patchJson(route('compras.procesar', $compra)));

        $this->assertSame('recibida', $compra->fresh()->estado);
        $this->assertEquals(15, (float) $insumo->fresh()->stock_actual);
        $mov = MovimientoInsumo::sole();
        $this->assertSame('Entrada', $mov->tipo_movimiento);
        $this->assertEquals(5, (float) $mov->stock_anterior);
        $this->assertEquals(15, (float) $mov->stock_nuevo);
    }

    public function test_no_se_procesa_dos_veces(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo();
        $this->actingAs($admin)->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id));
        $compra = Compra::sole();
        $this->actingAs($admin)->patchJson(route('compras.procesar', $compra));

        $this->actingAs($admin)->patchJson(route('compras.procesar', $compra))->assertStatus(422);

        $this->assertEquals(10, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(1, MovimientoInsumo::count());
    }

    public function test_anular_revierte_stock_y_audita(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 5]);
        $this->actingAs($admin)->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id));
        $compra = Compra::sole();
        $this->actingAs($admin)->patchJson(route('compras.procesar', $compra));

        $this->assertExito($this->actingAs($admin)->patchJson(route('compras.anular', $compra)));

        $compra->refresh();
        $this->assertSame('anulada', $compra->estado);
        $this->assertSame($admin->id, (int) $compra->anulado_por_id);
        $this->assertNotNull($compra->fecha_anulacion);
        $this->assertEquals(5, (float) $insumo->fresh()->stock_actual);
        $this->assertSame('Salida', MovimientoInsumo::latest('id')->first()->tipo_movimiento);
    }

    public function test_anular_se_bloquea_si_el_stock_ya_se_consumio(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo();
        $this->actingAs($admin)->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id));
        $compra = Compra::sole();
        $this->actingAs($admin)->patchJson(route('compras.procesar', $compra));
        $insumo->update(['stock_actual' => 3]); // se consumieron 7 de las 10

        $this->actingAs($admin)->patchJson(route('compras.anular', $compra))->assertStatus(422);

        $this->assertSame('recibida', $compra->fresh()->estado);
        $this->assertEquals(3, (float) $insumo->fresh()->stock_actual);
    }

    public function test_no_acepta_insumos_no_inventariables(): void
    {
        $insumo = $this->insumo(['is_inventoriable' => 0]);

        $this->actingAs($this->admin())
            ->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id))
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.insumo_id');

        $this->assertSame(0, Compra::count());
    }

    public function test_sin_permiso_no_se_accede(): void
    {
        $insumo = $this->insumo();

        $this->actingAs($this->usuarioSinPermisos())
            ->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id))
            ->assertForbidden();

        $this->assertSame(0, Compra::count());
    }
}
