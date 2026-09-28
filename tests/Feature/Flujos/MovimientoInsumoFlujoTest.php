<?php

namespace Tests\Feature\Flujos;

use App\Models\MovimientoInsumo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Movimientos de insumo: solo salidas manuales (las entradas llegan por
 * Compras y Producción). Escritos ANTES de migrar a Inertia.
 *
 * Contrato vivo: Compras (Blade/DataTables) lee `compras.existencias.data`.
 */
class MovimientoInsumoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    public function test_una_salida_manual_descuenta_stock_y_queda_registrada(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 20]);

        $this->assertExito($this->actingAs($admin)->post(route('movimiento-insumo.store'), [
            'insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 7.5, 'motivo' => 'Merma por corte defectuoso',
        ]));

        $this->assertEquals(12.5, (float) $insumo->fresh()->stock_actual);
        $m = MovimientoInsumo::sole();
        $this->assertSame('Salida', $m->tipo_movimiento);
        $this->assertEquals(20, (float) $m->stock_anterior);
        $this->assertEquals(12.5, (float) $m->stock_nuevo);
        $this->assertSame($admin->id, (int) $m->created_by);
    }

    public function test_no_se_registran_entradas_manuales(): void
    {
        // Las entradas entran solo por Compras (con proveedor, costo y factura) o Producción.
        $insumo = $this->insumo(['stock_actual' => 5]);

        $this->actingAs($this->admin())->postJson(route('movimiento-insumo.store'), [
            'insumo_id' => $insumo->id, 'tipo_movimiento' => 'Entrada', 'cantidad' => 10, 'motivo' => 'Ajuste',
        ])->assertStatus(422)->assertJsonValidationErrors('tipo_movimiento');

        $this->assertEquals(5, (float) $insumo->fresh()->stock_actual);
    }

    public function test_no_se_saca_mas_de_lo_que_hay(): void
    {
        $insumo = $this->insumo(['stock_actual' => 3]);

        $this->actingAs($this->admin())->postJson(route('movimiento-insumo.store'), [
            'insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 4, 'motivo' => 'Consumo',
        ])->assertStatus(422);

        $this->assertEquals(3, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(0, MovimientoInsumo::count());
    }

    public function test_un_insumo_no_inventariable_no_admite_movimientos(): void
    {
        $insumo = $this->insumo(['is_inventoriable' => 0, 'stock_actual' => 0]);

        $this->actingAs($this->admin())->postJson(route('movimiento-insumo.store'), [
            'insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 1, 'motivo' => 'Consumo',
        ])->assertStatus(422);

        $this->assertSame(0, MovimientoInsumo::count());
    }

    public function test_compras_lee_las_existencias_en_formato_datatables(): void
    {
        $this->insumo(['nombre' => 'Hilo rojo', 'stock_actual' => 2, 'stock_minimo' => 5]);
        $this->insumo(['nombre' => 'Hilo azul', 'stock_actual' => 50, 'stock_minimo' => 5]);
        $this->insumo(['nombre' => 'Servicio', 'is_inventoriable' => 0]);

        $this->actingAs($this->admin())->getJson(route('compras.existencias.data', ['draw' => 1, 'start' => 0, 'length' => 10]))
            ->assertOk()
            ->assertJsonPath('recordsTotal', 2) // solo inventariables
            ->assertJsonStructure(['draw', 'recordsTotal', 'recordsFiltered', 'data' => [['id', 'nombre', 'stock_actual', 'stock_minimo', 'stock_status']]]);

        $this->actingAs($this->admin())->getJson(route('compras.existencias.data', ['draw' => 1, 'start' => 0, 'length' => 10, 'filter_estado' => 'alerta']))
            ->assertJsonPath('recordsFiltered', 1)
            ->assertJsonPath('data.0.nombre', 'Hilo rojo')
            ->assertJsonPath('data.0.stock_status', 'bajo');
    }

    public function test_el_reporte_pdf_se_genera(): void
    {
        $insumo = $this->insumo(['stock_actual' => 20]);
        $this->actingAs($this->admin())->post(route('movimiento-insumo.store'), ['insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 1, 'motivo' => 'Consumo']);

        $r = $this->actingAs($this->admin())->get(route('movimiento-insumo.reporte.pdf', ['tipo_movimiento' => 'Salida']));
        $r->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $r->headers->get('Content-Type'));
    }

    public function test_sin_permiso_no_se_registra_nada(): void
    {
        $insumo = $this->insumo(['stock_actual' => 20]);

        $this->actingAs($this->usuarioSinPermisos())->postJson(route('movimiento-insumo.store'), [
            'insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 1, 'motivo' => 'Consumo',
        ])->assertForbidden();

        $this->assertEquals(20, (float) $insumo->fresh()->stock_actual);
    }
}
