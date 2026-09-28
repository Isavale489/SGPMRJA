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

    /** Compra en borrador con dos líneas: una gravada y una exenta. */
    private function borradorDosLineas($admin, $proveedor, $gravado, $exento, array $extra = []): Compra
    {
        $this->actingAs($admin)->postJson(route('compras.store'), array_merge([
            'proveedor_id' => $proveedor->id,
            'numero_factura' => '0001-0456',
            'fecha_compra' => now()->toDateString(),
            'tasa_cambio' => 40,
            'observaciones' => 'Pedido semanal',
            'items' => [
                ['insumo_id' => $gravado->id, 'cantidad' => 10, 'costo_unitario_bs' => 80, 'aplica_iva' => 1],
                ['insumo_id' => $exento->id, 'cantidad' => 5, 'costo_unitario_bs' => 40, 'aplica_iva' => 0],
            ],
        ], $extra));

        return Compra::latest('id')->firstOrFail();
    }

    public function test_el_iva_solo_grava_las_lineas_marcadas(): void
    {
        $c = $this->borradorDosLineas($this->admin(), $this->proveedor(), $this->insumo(), $this->insumo());

        // $: 10×2 + 5×1 = 25; IVA 16 % solo sobre los 20 gravados = 3,20.
        $this->assertEquals(25, (float) $c->subtotal);
        $this->assertEquals(3.2, (float) $c->iva);
        $this->assertEquals(28.2, (float) $c->total);
        $this->assertEquals(16, (float) $c->iva_porcentaje);
        $this->assertEquals(40, (float) $c->tasa_cambio);
        $this->assertSame([true, false], $c->detalles()->orderBy('id')->pluck('aplica_iva')->map(fn ($v) => (bool) $v)->all());
    }

    public function test_editar_un_borrador_reemplaza_sus_lineas(): void
    {
        $admin = $this->admin();
        $proveedor = $this->proveedor();
        [$a, $b, $nuevo] = [$this->insumo(), $this->insumo(), $this->insumo()];
        $c = $this->borradorDosLineas($admin, $proveedor, $a, $b);

        $this->assertExito($this->actingAs($admin)->putJson(route('compras.update', $c), [
            'proveedor_id' => $proveedor->id,
            'numero_factura' => '0001-0999',
            'fecha_compra' => now()->toDateString(),
            'tasa_cambio' => 50,
            'items' => [['insumo_id' => $nuevo->id, 'cantidad' => 4, 'costo_unitario_bs' => 100, 'aplica_iva' => 1]],
        ]));

        $c->refresh();
        $this->assertSame('0001-0999', $c->numero_factura);
        $this->assertSame('borrador', $c->estado);
        $linea = $c->detalles()->sole();
        $this->assertSame($nuevo->id, (int) $linea->insumo_id);
        $this->assertEquals(2, (float) $linea->costo_unitario); // 100 / 50
        $this->assertEquals(100, (float) $linea->costo_unitario_bs);
    }

    public function test_una_compra_recibida_no_se_edita_ni_se_elimina(): void
    {
        $admin = $this->admin();
        $proveedor = $this->proveedor();
        $insumo = $this->insumo();
        $this->actingAs($admin)->postJson(route('compras.store'), $this->payload($proveedor->id, $insumo->id));
        $c = Compra::sole();
        $this->actingAs($admin)->patchJson(route('compras.procesar', $c));

        $this->actingAs($admin)->putJson(route('compras.update', $c), $this->payload($proveedor->id, $insumo->id, 99))->assertStatus(422);
        $this->actingAs($admin)->deleteJson(route('compras.destroy', $c))->assertStatus(422);

        $this->assertEquals(10, (float) $c->detalles()->sole()->cantidad);
        $this->assertNotNull(Compra::find($c->id));
    }

    public function test_eliminar_un_borrador_lo_borra_con_sus_lineas(): void
    {
        $admin = $this->admin();
        $c = $this->borradorDosLineas($admin, $this->proveedor(), $this->insumo(), $this->insumo());

        $this->assertExito($this->actingAs($admin)->deleteJson(route('compras.destroy', $c)));

        $this->assertSame(0, Compra::withTrashed()->count());
        $this->assertSame(0, \App\Models\CompraDetalle::count());
    }

    public function test_clonar_una_anulada_crea_un_borrador_y_solo_una_vez(): void
    {
        $admin = $this->admin();
        $c = $this->borradorDosLineas($admin, $this->proveedor(), $this->insumo(), $this->insumo());
        $this->actingAs($admin)->patchJson(route('compras.procesar', $c));
        $this->actingAs($admin)->patchJson(route('compras.anular', $c));

        $this->assertExito($this->actingAs($admin)->postJson(route('compras.clonar', $c)));

        $clon = Compra::latest('id')->first();
        $this->assertNotSame($c->id, $clon->id);
        $this->assertSame('borrador', $clon->estado);
        $this->assertNull($clon->numero_factura);
        $this->assertSame(2, $clon->detalles()->count());
        $this->assertStringStartsWith("Clonada de Compra #{$c->id}", $clon->observaciones);
        $this->assertTrue((bool) $c->fresh()->clonada);

        $this->actingAs($admin)->postJson(route('compras.clonar', $c))->assertStatus(422);
        $this->assertSame(2, Compra::count());
    }

    public function test_procesar_actualiza_el_costo_del_insumo_en_dolares(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['costo_unitario' => 1]);
        $this->actingAs($admin)->postJson(route('compras.store'), $this->payload($this->proveedor()->id, $insumo->id));

        $this->actingAs($admin)->patchJson(route('compras.procesar', Compra::sole()));

        $this->assertEquals(2, (float) $insumo->fresh()->costo_unitario); // 80 Bs / 40
    }

    public function test_la_factura_no_se_repite_para_el_mismo_proveedor_ni_el_insumo_en_la_compra(): void
    {
        $admin = $this->admin();
        $proveedor = $this->proveedor();
        $this->borradorDosLineas($admin, $proveedor, $this->insumo(), $this->insumo());
        $insumo = $this->insumo();

        $this->actingAs($admin)->postJson(route('compras.store'), [...$this->payload($proveedor->id, $insumo->id), 'numero_factura' => '0001-0456'])
            ->assertStatus(422)->assertJsonValidationErrors('numero_factura');

        $repetido = $this->payload($proveedor->id, $insumo->id);
        $repetido['items'][] = $repetido['items'][0];
        $this->actingAs($admin)->postJson(route('compras.store'), $repetido)
            ->assertStatus(422)->assertJsonValidationErrors('items');

        // Otro proveedor sí puede tener esa factura.
        $this->assertExito($this->actingAs($admin)->postJson(route('compras.store'), [...$this->payload($this->proveedor()->id, $insumo->id), 'numero_factura' => '0001-0456']));
        $this->assertSame(2, Compra::count());
    }

    public function test_los_pdf_se_generan(): void
    {
        $admin = $this->admin();
        $c = $this->borradorDosLineas($admin, $this->proveedor(), $this->insumo(), $this->insumo());

        foreach ([route('compras.pdf', $c), route('compras.reporte.pdf', ['estado' => 'borrador', 'orden' => 'monto_desc'])] as $url) {
            $r = $this->actingAs($admin)->get($url);
            $r->assertOk();
            $this->assertStringContainsString('application/pdf', (string) $r->headers->get('Content-Type'));
        }
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
