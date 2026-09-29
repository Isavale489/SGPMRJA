<?php

namespace Tests\Feature\Flujos;

use App\Models\Cotizacion;
use App\Models\Pedido;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Reglas de Cotizaciones que la vista Blade solo aplicaba en el navegador
 * (ocultando botones) y que ahora valida el servidor. Cada test falla con el
 * código anterior a la migración a Inertia.
 */
class CotizacionReglasTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function crear($admin, array $extra = []): Cotizacion
    {
        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.store'), array_merge($this->payloadCotizacion($this->cliente()->id), $extra)));

        return Cotizacion::latest('id')->first();
    }

    public function test_la_prioridad_se_guarda_y_se_hereda_al_pedido(): void
    {
        $admin = $this->admin();
        $cot = $this->crear($admin, ['prioridad' => 'Urgente']);
        $this->assertSame('Urgente', $cot->prioridad);

        $this->actingAs($admin)->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => 'Aprobada']);
        $this->assertExito($this->actingAs($admin)->postJson(route('cotizaciones.convertirAPedido', $cot)));
        $this->assertSame('Urgente', Pedido::sole()->prioridad);
    }

    public function test_editar_no_cambia_el_estado_por_el_formulario(): void
    {
        $admin = $this->admin();
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload);
        $cot = Cotizacion::sole();

        $this->assertExito($this->actingAs($admin)->putJson(route('cotizaciones.update', $cot), $payload + ['estado' => 'Convertida', 'prioridad' => 'Alta']));

        $this->assertSame('Pendiente', $cot->fresh()->estado);
        $this->assertSame('Alta', $cot->fresh()->prioridad);
    }

    public function test_no_se_edita_ni_elimina_una_cotizacion_convertida_cancelada_o_vencida(): void
    {
        $admin = $this->admin();
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload);
        $cot = Cotizacion::sole();

        foreach (['Convertida', 'Cancelada', 'Vencida'] as $estado) {
            $cot->update(['estado' => $estado]);
            $payload['productos'][0]['cantidad'] = 99;
            $this->actingAs($admin)->putJson(route('cotizaciones.update', $cot), $payload)->assertStatus(422);
            $this->actingAs($admin)->deleteJson(route('cotizaciones.destroy', $cot))->assertStatus(422);
            $this->assertSame(12, (int) $cot->productos()->sole()->cantidad, $estado);
            $this->assertNotSoftDeleted($cot);
        }
    }

    public function test_el_cambio_de_estado_solo_admite_las_transiciones_del_menu(): void
    {
        $admin = $this->admin();
        $cot = $this->crear($admin);
        $cambiar = fn (string $a) => $this->actingAs($admin)->putJson(route('cotizaciones.updateEstado', $cot), ['estado' => $a]);

        // Los estados del sistema no se ponen a mano.
        $cambiar('Convertida')->assertStatus(422)->assertJsonStructure(['error']);
        $cambiar('Vencida')->assertStatus(422);
        $this->assertSame('Pendiente', $cot->fresh()->estado);

        $cambiar('Aprobada')->assertOk();
        $cambiar('Pendiente')->assertOk();
        $cambiar('Cancelada')->assertOk();
        $cambiar('Aprobada')->assertStatus(422); // Cancelada solo vuelve a Pendiente
        $cambiar('Pendiente')->assertOk();

        // Una vencida se reactiva con su acción (renueva la validez), no volviendo a Pendiente.
        $cot->update(['estado' => 'Vencida']);
        $cambiar('Pendiente')->assertStatus(422);
        $this->assertSame('Vencida', $cot->fresh()->estado);
    }

    public function test_el_precio_unitario_no_puede_ser_negativo(): void
    {
        $admin = $this->admin();
        $payload = $this->payloadCotizacion($this->cliente()->id);
        $payload['productos'][0]['precio_unitario'] = -5;

        $this->actingAs($admin)->postJson(route('cotizaciones.store'), $payload)
            ->assertStatus(422)->assertJsonValidationErrors('productos.0.precio_unitario');
        $this->assertSame(0, Cotizacion::count());
    }

    public function test_reactivar_una_que_no_esta_vencida_se_rechaza_sin_error_500(): void
    {
        $admin = $this->admin();
        $cot = $this->crear($admin);

        $this->actingAs($admin)->postJson(route('cotizaciones.reactivar', $cot))->assertStatus(422);
        $this->assertSame('Pendiente', $cot->fresh()->estado);
    }
}
