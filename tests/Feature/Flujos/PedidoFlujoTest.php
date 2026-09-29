<?php

namespace Tests\Feature\Flujos;

use App\Models\Banco;
use App\Models\Cotizacion;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Pedidos: nacen de una cotización aprobada (líneas copiadas en el servidor) con
 * el abono mínimo; después se editan pagos, entrega y prioridad. Las reglas de
 * pago que antes solo validaba el navegador ahora las valida el servidor.
 */
class PedidoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    /** Cotización Aprobada de 12 unidades × $10 = $120 (abono mínimo 50 % = $60). */
    private function cotizacion($admin): Cotizacion
    {
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $payload['productos'][0]['precio_unitario'] = 10;
        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload));
        $cot = Cotizacion::latest('id')->first();
        $this->actingAs($admin)->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => 'Aprobada']);

        return $cot->fresh();
    }

    private function datos(Cotizacion $cot, array $pagos, array $extra = []): array
    {
        return array_merge(['cotizacion_id' => $cot->id, 'fecha_entrega_estimada' => now()->addDays(20)->toDateString(), 'prioridad' => 'Alta', 'pagos' => $pagos], $extra);
    }

    public function test_crear_exige_el_abono_minimo_y_formaliza(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $this->assertEquals(120, (float) $cot->total);

        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 50]]))
            ->assertStatus(422)->assertJsonFragment(['error' => 'El abono registrado ($50.00) no alcanza el mínimo requerido de $60.00 (50% del total del pedido).']);
        $this->assertSame(0, Pedido::count());

        $entrega = now()->addDays(20)->toDateString();
        $this->assertExito($this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60]])));
        $pedido = Pedido::sole();
        $this->assertEquals(60, (float) $pedido->abono);
        $this->assertTrue($pedido->estaFormalizado());
        $this->assertSame($entrega, $pedido->fecha_entrega_estimada->toDateString());
        $this->assertSame('Alta', $pedido->prioridad);
        $this->assertSame('Convertida', $cot->fresh()->estado);
    }

    /** Regresión (B-02): las reglas de pago solo existían en el navegador. */
    public function test_las_reglas_de_pago_las_valida_el_servidor(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $banco = Banco::create(['nombre' => 'Banco de Venezuela']);

        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'transferencia', 'monto' => 60]]))
            ->assertStatus(422)->assertJsonValidationErrors(['pagos.0.banco_id', 'pagos.0.referencia']);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 30], ['metodo' => 'efectivo', 'monto' => 30]]))
            ->assertStatus(422)->assertJsonValidationErrors('pagos');
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 0]]))
            ->assertStatus(422)->assertJsonValidationErrors('pagos.0.monto');
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 130]]))
            ->assertStatus(422)->assertJsonFragment(['error' => 'Los pagos ($130.00) superan el total del pedido ($120.00).']);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60]], ['fecha_entrega_estimada' => now()->subDay()->toDateString()]))
            ->assertStatus(422)->assertJsonValidationErrors('fecha_entrega_estimada');
        $this->assertSame(0, Pedido::count());

        $this->assertExito($this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [
            ['metodo' => 'pago_movil', 'monto' => 40, 'banco_id' => $banco->id, 'referencia' => '0412-1234'],
            ['metodo' => 'efectivo', 'monto' => 20, 'banco_id' => $banco->id, 'referencia' => 'no aplica'],
        ])));
        $pagos = Pedido::sole()->pagos()->orderBy('id')->get();
        $this->assertSame($banco->id, (int) $pagos[0]->banco_id);
        $this->assertNull($pagos[1]->banco_id); // el efectivo no lleva banco ni referencia
        $this->assertNull($pagos[1]->referencia);
    }

    /** Regresión (B-06): el navegador mandaba las líneas y el servidor las creía. */
    public function test_las_lineas_que_manda_el_navegador_se_ignoran(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);

        $this->assertExito($this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60]], [
            'cliente_id' => $this->cliente()->id,
            'productos' => [['tipo_producto_id' => $cot->productos()->first()->tipo_producto_id, 'cantidad' => 999, 'precio_unitario' => 0.01]],
        ])));

        $pedido = Pedido::sole();
        $this->assertEquals(120, (float) $pedido->total);
        $this->assertSame($cot->cliente_id, (int) $pedido->cliente_id);
        $this->assertSame(12, (int) $pedido->productos()->sole()->cantidad);
    }

    /** Decisión de producto: tras crearlo se editan pagos, entrega y prioridad (antes se descartaban en silencio). */
    public function test_editar_cambia_pagos_entrega_y_prioridad(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60]]));
        $pedido = Pedido::sole();
        $entrega = now()->addDays(40)->toDateString();

        $this->assertExito($this->actingAs($admin)->putJson(route('pedidos.update', $pedido), [
            'fecha_entrega_estimada' => $entrega, 'prioridad' => 'Urgente',
            'pagos' => [['metodo' => 'efectivo', 'monto' => 60], ['metodo' => 'transferencia', 'monto' => 60, 'banco_id' => Banco::create(['nombre' => 'Banesco'])->id, 'referencia' => '778899']],
        ]));
        $pedido->refresh();
        $this->assertSame($entrega, $pedido->fecha_entrega_estimada->toDateString());
        $this->assertSame('Urgente', $pedido->prioridad);
        $this->assertEquals(120, (float) $pedido->abono);

        // El abono no puede bajar de lo que ya estaba registrado (ni del mínimo).
        $this->actingAs($admin)->putJson(route('pedidos.update', $pedido), ['fecha_entrega_estimada' => $entrega, 'prioridad' => 'Urgente', 'pagos' => [['metodo' => 'efectivo', 'monto' => 10]]])
            ->assertStatus(422);
        $this->assertEquals(120, (float) $pedido->fresh()->abono);
    }

    public function test_completado_solo_recibe_pagos_y_cancelado_no_se_edita(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60]]));
        $pedido = Pedido::sole();
        $entregaOriginal = $pedido->fecha_entrega_estimada->toDateString();

        $pedido->update(['estado' => 'Completado']);
        $this->assertExito($this->actingAs($admin)->putJson(route('pedidos.update', $pedido), [
            'fecha_entrega_estimada' => now()->addDays(60)->toDateString(), 'prioridad' => 'Urgente', 'pagos' => [['metodo' => 'efectivo', 'monto' => 120]],
        ]));
        $pedido->refresh();
        $this->assertEquals(120, (float) $pedido->abono);
        $this->assertSame($entregaOriginal, $pedido->fecha_entrega_estimada->toDateString());
        $this->assertSame('Alta', $pedido->prioridad);
        $this->actingAs($admin)->get(route('pedidos.edit', $pedido))->assertOk(); // «Registrar pago» también completado (B-05)

        $pedido->update(['estado' => 'Cancelado']);
        $this->actingAs($admin)->putJson(route('pedidos.update', $pedido), ['fecha_entrega_estimada' => $entregaOriginal, 'prioridad' => 'Alta', 'pagos' => [['metodo' => 'efectivo', 'monto' => 120]]])
            ->assertStatus(422)->assertJsonFragment(['error' => 'No se puede editar un pedido cancelado.']);
        $this->actingAs($admin)->get(route('pedidos.edit', $pedido))->assertRedirect();
    }

    /** Revisión: un banco que se inhabilitó después bloqueaba editar el pedido (había que falsear el pago). */
    public function test_un_banco_inhabilitado_no_bloquea_editar_sus_pagos(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $banco = Banco::create(['nombre' => 'Banco Cerrado']);
        $pago = ['metodo' => 'transferencia', 'monto' => 60, 'banco_id' => $banco->id, 'referencia' => '123'];
        $this->assertExito($this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [$pago])));
        $pedido = Pedido::sole();
        $banco->delete();

        $this->assertExito($this->actingAs($admin)->putJson(route('pedidos.update', $pedido), ['fecha_entrega_estimada' => now()->addDays(25)->toDateString(), 'prioridad' => 'Urgente', 'pagos' => [$pago]]));
        $this->assertSame('Urgente', $pedido->fresh()->prioridad);

        // Pero un pedido nuevo no puede usar un banco inhabilitado (en el mismo pedido sí: es el banco de su historial).
        $otra = $this->cotizacion($admin);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($otra, [$pago]))->assertStatus(422)->assertJsonValidationErrors('pagos.0.banco_id');
    }

    /** Revisión: un completado con entrega legada anterior a la fecha del pedido no podía registrar el saldo. */
    public function test_completado_con_entrega_legada_registra_el_saldo(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60]]));
        $pedido = Pedido::sole();
        $pedido->update(['estado' => 'Completado', 'fecha_entrega_estimada' => now()->subDays(5)->toDateString()]);

        $this->assertExito($this->actingAs($admin)->putJson(route('pedidos.update', $pedido), ['pagos' => [['metodo' => 'efectivo', 'monto' => 120]]]));
        $this->assertEquals(120, (float) $pedido->fresh()->abono);
    }

    public function test_un_monto_de_centesimas_no_se_guarda_como_cero(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);
        $this->actingAs($admin)->postJson(route('pedidos.store'), $this->datos($cot, [['metodo' => 'efectivo', 'monto' => 60], ['metodo' => 'transferencia', 'monto' => 0.001, 'banco_id' => Banco::create(['nombre' => 'Mercantil'])->id, 'referencia' => '9']]))
            ->assertStatus(422)->assertJsonValidationErrors('pagos.1.monto');
    }

    /** Decisión de producto: el endpoint viejo que creaba pedidos sin abono ni entrega ya no existe. */
    public function test_el_endpoint_viejo_de_conversion_no_existe(): void
    {
        $admin = $this->admin();
        $cot = $this->cotizacion($admin);

        $this->actingAs($admin)->postJson("/cotizaciones/{$cot->id}/convertir-a-pedido")->assertNotFound();
        $this->assertSame(0, Pedido::count());
    }
}
