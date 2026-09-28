<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\DetallePedido;
use App\Models\OrdenProduccion;
use App\Models\PermisoRol;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de Órdenes de Producción: listado por pedido con órdenes,
 * detalle y «por empleado» por recarga parcial; formulario de alta (lote) y
 * de edición en páginas propias.
 */
class OrdenesPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Ordenes/tipos.ts'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    /** Orden de 10 unidades: Ana 6 (responsable) / Luis 4. */
    private function orden(User $admin, ?DetallePedido $linea = null): array
    {
        $linea ??= $this->pedidoConLinea(10);
        $insumo = $this->insumo(['stock_actual' => 50]);
        [$ana, $luis] = [$this->empleado('Ana Pérez'), $this->empleado('Luis Rojas')];
        $this->actingAs($admin)->postJson(route('ordenes.store'), [
            'detalle_pedido_id' => $linea->id,
            'empleados' => [['id' => $ana->id, 'cantidad' => 6], ['id' => $luis->id, 'cantidad' => 4]],
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin_estimada' => now()->addWeek()->toDateString(),
            'insumos' => [['id' => $insumo->id, 'cantidad_estimada' => 20]],
        ]);

        return [OrdenProduccion::latest('id')->firstOrFail(), $linea, $insumo, $ana, $luis];
    }

    public function test_el_listado_y_sus_recargas_parciales_cumplen_el_contrato(): void
    {
        $admin = $this->admin();
        [$orden, , , , $luis] = $this->orden($admin);

        $this->actingAs($admin)->get(route('ordenes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Ordenes/Index')
                ->has('registros.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('PedidoConOrdenes')))
                ->where('registros.data.0.pendientes', 1)
                ->where('ordenes', null)->where('orden', null)->where('misOrdenes', null));

        $this->actingAs($admin)->get(route('ordenes.index', ['pedido' => $orden->pedido_id]))
            ->assertInertia(fn (Assert $p) => $p->has('ordenes', 1, fn (Assert $o) => $o->hasAll($this->clavesTs('OrdenFila')))->has('ordenes.0.equipo', 2));

        $this->actingAs($admin)->get(route('ordenes.index', ['ver' => $orden->id]))
            ->assertInertia(fn (Assert $p) => $p->has('orden', fn (Assert $o) => $o->hasAll($this->clavesTs('OrdenDetalle')))->where('orden.insumos.0.estimada', 20));

        // Regresión: Luis no es el responsable principal, pero trabaja en la orden.
        $this->actingAs($admin)->get(route('ordenes.index', ['empleado' => $luis->id]))
            ->assertInertia(fn (Assert $p) => $p
                ->has('misOrdenes.ordenes', 1, fn (Assert $o) => $o->hasAll($this->clavesTs('OrdenDeEmpleado')))
                ->where('misOrdenes.ordenes.0.mi_cantidad', 4));
    }

    public function test_filtra_por_estado_y_busqueda(): void
    {
        $admin = $this->admin();
        [$orden] = $this->orden($admin);

        $this->actingAs($admin)->get(route('ordenes.index', ['estado' => 'Finalizado']))->assertInertia(fn (Assert $p) => $p->has('registros.data', 0));
        $this->actingAs($admin)->get(route('ordenes.index', ['buscar' => "Pedido #{$orden->pedido_id}"]))->assertInertia(fn (Assert $p) => $p->has('registros.data', 1));
        $this->actingAs($admin)->get(route('ordenes.index', ['buscar' => 'María González']))->assertInertia(fn (Assert $p) => $p->has('registros.data', 1));
    }

    public function test_el_formulario_de_alta_trae_lo_que_falta_asignar(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $this->orden($admin, $linea); // toma las 10
        $otra = $this->pedidoConLinea(8);

        $this->actingAs($admin)->get(route('ordenes.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Ordenes/Formulario')
                ->has('pedidos', 2)
                ->has('pedidos.0', fn (Assert $x) => $x->hasAll($this->clavesTs('PedidoDisponible')))
                ->has('pedidos.0.lineas.0', fn (Assert $x) => $x->hasAll($this->clavesTs('LineaDisponible')))
                ->where('pedidos', fn ($ps) => collect($ps)->firstWhere('id', $otra->pedido_id)['lineas'][0]['pendiente'] === 8
                    && collect($ps)->firstWhere('id', $linea->pedido_id)['lineas'][0]['pendiente'] === 0));
    }

    public function test_crear_desde_inertia_vuelve_al_pedido_y_sin_stock_ofrece_la_compra(): void
    {
        $admin = $this->admin();
        $linea = $this->pedidoConLinea(10);
        $emp = $this->empleado();
        $insumo = $this->insumo(['stock_actual' => 5]);
        $cuerpo = fn (float $consumo) => ['pedido_id' => $linea->pedido_id, 'ordenes' => [[
            'detalle_pedido_id' => $linea->id, 'cantidad' => 10, 'empleados' => [['id' => $emp->id, 'cantidad' => 10]],
            'fecha_inicio' => now()->toDateString(), 'fecha_fin_estimada' => now()->addWeek()->toDateString(),
            'insumos' => [['id' => $insumo->id, 'cantidad_estimada' => $consumo]],
        ]]];

        $this->actingAs($admin)->from(route('ordenes.create'))->withHeaders($this->inertia())
            ->post(route('ordenes.batch'), $cuerpo(8))
            ->assertRedirect(route('ordenes.create'))
            ->assertSessionHasErrors('general')
            ->assertSessionHas(SessionKey::FLASH_DATA, fn ($f) => ($f['faltantes'][0]['cantidad'] ?? null) == 3);
        $this->assertSame(0, OrdenProduccion::count());

        $this->actingAs($admin)->from(route('ordenes.create'))->withHeaders($this->inertia())
            ->post(route('ordenes.batch'), $cuerpo(4))
            ->assertRedirect(route('ordenes.index', ['pedido' => $linea->pedido_id]))
            ->assertSessionHas('success', '1 orden creada correctamente.');
    }

    public function test_editar_es_una_pagina_y_un_equipo_invalido_no_guarda_nada(): void
    {
        $admin = $this->admin();
        [$orden, , , $ana, $luis] = $this->orden($admin);

        $this->actingAs($admin)->get(route('ordenes.edit', $orden))
            ->assertInertia(fn (Assert $p) => $p->component('Ordenes/Editar')
                ->has('orden', fn (Assert $o) => $o->hasAll($this->clavesTs('OrdenEditable')))
                ->where('orden.cantidad_maxima', 10));

        // Regresión: antes se guardaban fechas y notas aunque el reparto fallara.
        $this->actingAs($admin)->from(route('ordenes.edit', $orden))->withHeaders($this->inertia())
            ->put(route('ordenes.update', $orden), [
                'empleados' => [['id' => $ana->id, 'cantidad' => 3], ['id' => $luis->id, 'cantidad' => 3]], // suma 6, no 10
                'fecha_inicio' => now()->toDateString(), 'fecha_fin_estimada' => now()->addMonth()->toDateString(),
                'estado' => 'Pendiente', 'notas' => 'No debería quedar',
            ])
            ->assertRedirect(route('ordenes.edit', $orden))
            ->assertSessionHasErrors('empleados');

        $this->assertNull($orden->fresh()->notas);
        $this->assertSame(now()->addWeek()->toDateString(), $orden->fresh()->fecha_fin_estimada->toDateString());
    }

    public function test_avance_y_cancelacion_desde_inertia(): void
    {
        $admin = $this->admin();
        [$orden, , , , $luis] = $this->orden($admin);
        $volver = route('ordenes.index', ['pedido' => $orden->pedido_id, 'ver' => $orden->id]);

        $this->actingAs($admin)->from($volver)->withHeaders($this->inertia())
            ->post(route('ordenes.avance', $orden), ['cantidad_producida' => 9, 'empleado_id' => $luis->id])
            ->assertSessionHasErrors('cantidad_producida');

        $this->actingAs($admin)->from($volver)->withHeaders($this->inertia())
            ->post(route('ordenes.avance', $orden), ['cantidad_producida' => 4, 'empleado_id' => $luis->id])
            ->assertRedirect($volver)->assertSessionHas('success', 'Avance registrado correctamente.');

        $this->actingAs($admin)->from($volver)->withHeaders($this->inertia())
            ->patch(route('ordenes.cancelar', $orden), [])
            ->assertSessionHasErrors('motivo_cancelacion');
        $this->assertSame('En Proceso', $orden->fresh()->estado);

        // Una orden cancelada ya no se edita.
        $this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden), ['motivo_cancelacion' => 'Cambio de diseño']);
        $this->actingAs($admin)->get(route('ordenes.edit', $orden))->assertRedirect()->assertSessionHas('error');
    }

    public function test_ver_no_es_gestionar(): void
    {
        $rol = Rol::create(['nombre' => 'Supervisión', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'ordenes.ver']);
        $u = User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($u)->get(route('ordenes.index'))->assertOk();
        $this->actingAs($u)->get(route('ordenes.create'))->assertForbidden();
    }
}
