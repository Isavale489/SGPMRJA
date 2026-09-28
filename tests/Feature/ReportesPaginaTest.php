<?php

namespace Tests\Feature;

use App\Models\DetallePedido;
use App\Models\OrdenProduccion;
use App\Models\PermisoRol;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de Consultas y Reportes: hub filtrado por permisos y los
 * cuatro reportes con gráficos. Incluye la regresión del consumo de insumos.
 */
class ReportesPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Reportes/tipos.ts'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    /** Orden de $cantidad con un solo empleado; consume $consumo del insumo. */
    private function orden(User $admin, $insumo, int $cantidad = 10, float $consumo = 20, ?DetallePedido $linea = null, string $empleado = 'Ana Pérez'): OrdenProduccion
    {
        $linea ??= $this->pedidoConLinea($cantidad);
        $emp = $this->empleado($empleado);
        $this->actingAs($admin)->postJson(route('ordenes.store'), [
            'detalle_pedido_id' => $linea->id,
            'empleados' => [['id' => $emp->id, 'cantidad' => $cantidad]],
            'cantidad' => $cantidad,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin_estimada' => now()->addWeek()->toDateString(),
            'insumos' => [['id' => $insumo->id, 'cantidad_estimada' => $consumo]],
        ])->assertOk();

        return OrdenProduccion::latest('id')->firstOrFail();
    }

    public function test_el_hub_filtra_el_catalogo_por_permisos(): void
    {
        $this->actingAs($this->admin())->get(route('reportes.general'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Reportes/General')
                ->has('grupos.0', fn (Assert $g) => $g->hasAll($this->clavesTs('GrupoHub')))
                ->has('grupos.0.reportes.0', fn (Assert $r) => $r->hasAll($this->clavesTs('ReporteHub')))
                ->has('kpis', fn (Assert $k) => $k->hasAll($this->clavesTs('KpisHub'))));

        // Solo reportes.ver: ve las vistas de análisis, no los PDF de cada módulo.
        $rol = Rol::create(['nombre' => 'Analista', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'reportes.ver']);
        $analista = User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($analista)->get(route('reportes.general'))
            ->assertInertia(fn (Assert $p) => $p->has('grupos', 1)
                ->where('grupos.0.reportes', fn ($rs) => collect($rs)->every(fn ($r) => $r['formato'] === 'vista') && count($rs) === 4));
    }

    public function test_produccion_eficiencia_y_empleados(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 100]);
        $orden = $this->orden($admin, $insumo, 10, 5);
        $this->actingAs($admin)->postJson(route('ordenes.avance', $orden), ['cantidad_producida' => 8]);
        OrdenProduccion::whereKey($orden->id)->update(['cantidad_defectuosa' => 2]); // como tras una inspección

        $this->actingAs($admin)->get(route('reportes.produccion'))
            ->assertInertia(fn (Assert $p) => $p->component('Reportes/Produccion')
                ->where('estados.0', ['estado' => 'En Proceso', 'total' => 1])
                ->has('mensual.0', fn (Assert $m) => $m->hasAll($this->clavesTs('MesProduccion')))
                ->where('mensual.0.producido', 8)->where('mensual.0.eficiencia', 80));

        $this->actingAs($admin)->get(route('reportes.eficiencia'))
            ->assertInertia(fn (Assert $p) => $p->component('Reportes/Eficiencia')
                ->has('pedidos.0', fn (Assert $x) => $x->hasAll($this->clavesTs('PedidoEficiencia')))
                ->has('pedidos.0.ordenes.0', fn (Assert $x) => $x->hasAll($this->clavesTs('OrdenEficiencia')))
                ->where('pedidos.0.pedido_id', $orden->pedido_id)
                ->has('kpis', fn (Assert $k) => $k->hasAll($this->clavesTs('KpisEficiencia')))
                ->where('kpis.eficiencia_global', 80));

        $this->actingAs($admin)->get(route('reportes.empleados'))
            ->assertInertia(fn (Assert $p) => $p->component('Reportes/Empleados')
                ->has('empleados', 1, fn (Assert $x) => $x->hasAll($this->clavesTs('RendimientoEmpleado')))
                ->where('empleados.0.total_asignado', 10)->where('empleados.0.total_producido', 8));
    }

    public function test_el_consumo_no_cuenta_lo_que_volvio_al_inventario(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 100]);
        $this->orden($admin, $insumo, 10, 20);                                  // consume 20
        $cancelada = $this->orden($admin, $insumo, 5, 7, null, 'Luis Rojas');    // se cancela en Pendiente
        $eliminada = $this->orden($admin, $insumo, 4, 3, null, 'Marta Díaz');    // se elimina
        $this->actingAs($admin)->patchJson(route('ordenes.cancelar', $cancelada))->assertOk();
        $this->actingAs($admin)->deleteJson(route('ordenes.destroy', $eliminada))->assertOk();

        $this->assertEquals(80, (float) $insumo->fresh()->stock_actual);
        $this->assertEquals(0, (float) DB::table('detalle_orden_insumo')->where('orden_produccion_id', $cancelada->id)->value('cantidad_utilizada'));

        $this->actingAs($admin)->get(route('reportes.insumos'))
            ->assertInertia(fn (Assert $p) => $p->component('Reportes/Insumos')
                ->has('insumos', 1, fn (Assert $x) => $x->hasAll($this->clavesTs('ConsumoInsumo')))
                ->where('insumos.0.total', 20)
                ->where('insumos.0.ordenes', 1));
    }

    public function test_la_migracion_repara_el_consumo_historico_de_ordenes_repuestas(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 100]);
        $orden = $this->orden($admin, $insumo, 5, 7);
        $this->actingAs($admin)->patchJson(route('ordenes.cancelar', $orden));
        // Estado de antes del arreglo: la reposición no ponía en 0 el consumo.
        DB::table('detalle_orden_insumo')->where('orden_produccion_id', $orden->id)->update(['cantidad_utilizada' => 7]);
        $activa = $this->orden($admin, $insumo, 3, 2, null, 'Luis Rojas');

        (require database_path('migrations/2026_09_28_000001_reset_consumo_de_ordenes_repuestas.php'))->up();

        $this->assertEquals(0, (float) DB::table('detalle_orden_insumo')->where('orden_produccion_id', $orden->id)->value('cantidad_utilizada'));
        $this->assertEquals(2, (float) DB::table('detalle_orden_insumo')->where('orden_produccion_id', $activa->id)->value('cantidad_utilizada'));
    }

    public function test_los_iconos_del_catalogo_existen_en_la_plataforma(): void
    {
        preg_match('/export const ICONOS[^{]*\{([^}]*)\}/s', file_get_contents(resource_path('js/components/app/icono.tsx')), $m);
        $iconos = array_map('trim', explode(',', trim($m[1] ?? '', " \n,")));

        foreach (config('reportes.grupos') as $g) {
            $this->assertContains($g['icono'], $iconos, "Grupo «{$g['titulo']}»");
            foreach ($g['reportes'] as $r) {
                $this->assertContains($r['icono'], $iconos, "Reporte «{$r['titulo']}»");
            }
        }
    }

    public function test_sin_permiso_no_se_ven(): void
    {
        $u = $this->usuarioSinPermisos();
        foreach (['general', 'produccion', 'eficiencia', 'insumos', 'empleados'] as $r) {
            $this->actingAs($u)->get(route("reportes.{$r}"))->assertForbidden();
        }
    }
}
