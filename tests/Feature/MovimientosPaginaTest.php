<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\MovimientoInsumo;
use App\Models\PermisoRol;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de Movimientos de insumo: pestañas movimientos/existencias,
 * historial, rotación y alertas; la salida enviada como lo hace Inertia.
 */
class MovimientosPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz, string $archivo = 'js/pages/Movimientos/Index.tsx'): array
    {
        $ts = file_get_contents(resource_path($archivo));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    private function conSalida(User $admin): \App\Models\Insumo
    {
        $insumo = $this->insumo(['nombre' => 'Hilo rojo', 'stock_actual' => 20, 'stock_minimo' => 25]);
        $this->actingAs($admin)->post(route('movimiento-insumo.store'), ['insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 5, 'motivo' => 'Consumo en taller']);

        return $insumo;
    }

    public function test_las_filas_cumplen_el_contrato_de_la_pagina(): void
    {
        $admin = $this->admin();
        $this->conSalida($admin);
        $this->insumo(['nombre' => 'Servicio de bordado', 'is_inventoriable' => 0]);

        $this->actingAs($admin)->get(route('movimiento-insumo.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Movimientos/Index')
                ->where('vista', 'movimientos')
                ->has('movimientos.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('MovimientoFila')))
                ->where('existencias', null));

        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['vista' => 'existencias']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('movimientos', null)
                ->has('existencias.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('ExistenciaFila', 'js/components/app/tabla-existencias.tsx')))
                ->where('existencias.data.0.estado', 'bajo'));
    }

    public function test_filtra_movimientos_por_tipo_y_busqueda(): void
    {
        $admin = $this->admin();
        $this->conSalida($admin);

        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['tipo' => 'Entrada']))
            ->assertInertia(fn (Assert $p) => $p->has('movimientos.data', 0));
        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['buscar' => 'taller']))
            ->assertInertia(fn (Assert $p) => $p->has('movimientos.data', 1));
        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['buscar' => 'inexistente']))
            ->assertInertia(fn (Assert $p) => $p->has('movimientos.data', 0));
    }

    public function test_una_salida_desde_inertia_redirige_con_aviso_y_los_errores_van_al_campo(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['stock_actual' => 3]);

        $this->actingAs($admin)->from(route('movimiento-insumo.index'))->withHeaders($this->inertia())
            ->post(route('movimiento-insumo.store'), ['insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 2, 'motivo' => 'Merma'])
            ->assertRedirect(route('movimiento-insumo.index'))
            ->assertSessionHas('success', 'Salida registrada correctamente.');

        $this->actingAs($admin)->from(route('movimiento-insumo.index'))->withHeaders($this->inertia())
            ->post(route('movimiento-insumo.store'), ['insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 5, 'motivo' => 'Merma'])
            ->assertRedirect(route('movimiento-insumo.index'))
            ->assertSessionHasErrors('cantidad');

        $this->assertEquals(1, (float) $insumo->fresh()->stock_actual);
        $this->assertSame(1, MovimientoInsumo::count());
    }

    public function test_historial_rotacion_y_alertas(): void
    {
        $admin = $this->admin();
        $insumo = $this->conSalida($admin);

        $this->actingAs($admin)->get(route('movimiento-insumo.historial', $insumo->id))
            ->assertInertia(fn (Assert $p) => $p->component('Movimientos/Historial')
                ->where('insumo.id', $insumo->id)
                ->has('movimientos.data', 1));

        $this->actingAs($admin)->get(route('movimiento-insumo.rotacion'))
            ->assertInertia(fn (Assert $p) => $p->component('Movimientos/Rotacion')
                ->where('insumos.0.id', $insumo->id)
                ->where('insumos.0.salidas', 5));

        $this->actingAs($admin)->get(route('movimiento-insumo.alertas'))
            ->assertInertia(fn (Assert $p) => $p->component('Movimientos/Alertas')->has('insumos', 1));
    }

    public function test_el_reporte_de_existencias_viejo_redirige_a_la_pestana(): void
    {
        $this->actingAs($this->admin())->get(route('movimiento-insumo.reporte'))
            ->assertRedirect(route('movimiento-insumo.index', ['vista' => 'existencias']));
    }

    public function test_un_rol_con_permiso_de_ver_entra_a_rotacion(): void
    {
        // Regresión: rotación no estaba en config/modulos.php y el middleware daba 403 a todo no-admin.
        $rol = Rol::create(['nombre' => 'Almacén', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'movimiento-insumo.ver']);
        $almacen = User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($almacen)->get(route('movimiento-insumo.rotacion'))->assertOk();
        $this->actingAs($almacen)->get(route('movimiento-insumo.index'))
            ->assertInertia(fn (Assert $p) => $p->where('auth.permisos', fn ($ps) => collect($ps)->contains('movimiento-insumo.ver')));
        // Ver no es gestionar.
        $this->actingAs($almacen)->post(route('movimiento-insumo.store'), [])->assertForbidden();
    }

    /** Movimiento con fecha y saldos fijos (sin pasar por el store). */
    private function movimiento(\App\Models\Insumo $insumo, array $attrs = []): MovimientoInsumo
    {
        $m = MovimientoInsumo::create(array_merge([
            'insumo_id' => $insumo->id, 'tipo_movimiento' => 'Salida', 'cantidad' => 1,
            'stock_anterior' => 10, 'stock_nuevo' => 9, 'motivo' => 'Ajuste', 'created_by' => User::query()->value('id') ?? User::factory()->create()->id,
        ], array_diff_key($attrs, ['fecha' => 1])));
        if (isset($attrs['fecha'])) {
            $m->forceFill(['created_at' => $attrs['fecha']])->save();
        }

        return $m;
    }

    /** Ids de los movimientos que devuelve el listado con esos filtros. */
    private function idsListado(User $admin, array $filtros): array
    {
        $ids = [];
        $this->actingAs($admin)->get(route('movimiento-insumo.index', $filtros))->assertOk()
            ->assertInertia(function (Assert $p) use (&$ids) {
                $ids = collect($p->toArray()['props']['movimientos']['data'])->pluck('id')->sort()->values()->all();
            });

        return $ids;
    }

    public function test_los_filtros_devuelven_exactamente_los_movimientos_que_corresponden(): void
    {
        $admin = $this->admin();
        $hilo = $this->insumo(['nombre' => 'Hilo azul']);
        $tela = $this->insumo(['nombre' => 'Tela Oxford']);
        $a = $this->movimiento($hilo, ['tipo_movimiento' => 'Entrada', 'fecha' => '2026-09-01 10:00:00']);
        $b = $this->movimiento($hilo, ['fecha' => '2026-09-10 23:30:00']);
        $c = $this->movimiento($tela, ['fecha' => '2026-09-20 08:00:00']);

        $this->assertSame([$a->id], $this->idsListado($admin, ['tipo' => 'Entrada']));
        $this->assertSame([$b->id, $c->id], $this->idsListado($admin, ['tipo' => 'Salida']));
        $this->assertSame([$a->id, $b->id], $this->idsListado($admin, ['insumo' => $hilo->id]));
        // Rango inclusivo: el día «hasta» cuenta completo (23:30 incluido).
        $this->assertSame([$b->id], $this->idsListado($admin, ['desde' => '2026-09-02', 'hasta' => '2026-09-10']));
        $this->assertSame([$b->id, $c->id], $this->idsListado($admin, ['desde' => '2026-09-10']));
        $this->assertSame([$a->id], $this->idsListado($admin, ['hasta' => '2026-09-01']));
    }

    public function test_filtra_por_estado_de_stock_del_insumo(): void
    {
        $admin = $this->admin();
        $critico = $this->insumo(['stock_actual' => 2, 'stock_minimo' => 5]);
        $optimo = $this->insumo(['stock_actual' => 10, 'stock_minimo' => 5, 'stock_maximo' => 20]);
        $exceso = $this->insumo(['stock_actual' => 30, 'stock_minimo' => 5, 'stock_maximo' => 20]);
        $mc = $this->movimiento($critico);
        $mo = $this->movimiento($optimo);
        $me = $this->movimiento($exceso);

        $this->assertSame([$mc->id], $this->idsListado($admin, ['stock' => 'critico']));
        $this->assertSame([$mo->id], $this->idsListado($admin, ['stock' => 'optimo']));
        $this->assertSame([$me->id], $this->idsListado($admin, ['stock' => 'exceso']));
        // Un valor desconocido no filtra.
        $this->assertCount(3, $this->idsListado($admin, ['stock' => 'otro']));
    }

    public function test_una_fecha_invalida_en_la_url_se_ignora(): void
    {
        $admin = $this->admin();
        $m = $this->movimiento($this->insumo());

        $this->assertSame([$m->id], $this->idsListado($admin, ['desde' => 'abc', 'hasta' => '2026-02-31']));
        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['desde' => 'abc']))
            ->assertInertia(fn (Assert $p) => $p->missing('filtros.desde'));
        $this->actingAs($admin)->get(route('movimiento-insumo.reporte.pdf', ['fecha_desde' => 'abc', 'fecha_hasta' => 'x']))->assertOk();
    }

    public function test_la_busqueda_amplia_encuentra_por_tipo_cantidad_existencia_fecha_y_codigo(): void
    {
        $admin = $this->admin();
        $hilo = $this->insumo(['nombre' => 'Hilo', 'codigo' => 'HROJ1']);
        $a = $this->movimiento($hilo, ['tipo_movimiento' => 'Entrada', 'cantidad' => 37, 'stock_anterior' => 0, 'stock_nuevo' => 37, 'motivo' => 'Compra', 'fecha' => '2026-08-15 09:00:00']);
        $b = $this->movimiento($hilo, ['cantidad' => 4, 'stock_anterior' => 37, 'stock_nuevo' => 33, 'motivo' => 'Merma', 'fecha' => '2026-09-03 09:00:00']);

        $this->assertSame([$a->id], $this->idsListado($admin, ['buscar' => 'entrada']));
        $this->assertSame([$a->id], $this->idsListado($admin, ['buscar' => '37.00']));
        $this->assertSame([$b->id], $this->idsListado($admin, ['buscar' => '33']));
        $this->assertSame([$b->id], $this->idsListado($admin, ['buscar' => '03/09/2026']));
        $this->assertSame([$a->id, $b->id], $this->idsListado($admin, ['buscar' => 'ROJ']));
    }

    public function test_la_busqueda_escapa_los_comodines_de_like(): void
    {
        $admin = $this->admin();
        $this->movimiento($this->insumo(['codigo' => 'ABC-1']), ['motivo' => 'Ajuste']);
        $conPorcentaje = $this->movimiento($this->insumo(['codigo' => 'XYZ-1']), ['motivo' => 'Descuento 10% por merma']);

        $this->assertSame([$conPorcentaje->id], $this->idsListado($admin, ['buscar' => '%']));
        $this->assertSame([], $this->idsListado($admin, ['buscar' => 'A_C']));
    }

    public function test_los_parametros_tipo_arreglo_no_rompen_el_listado(): void
    {
        $admin = $this->admin();
        $m = $this->movimiento($this->insumo());

        $this->assertSame([$m->id], $this->idsListado($admin, ['buscar' => ['x'], 'tipo' => ['Salida'], 'desde' => ['2026-01-01'], 'stock' => ['critico']]));
        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['vista' => 'existencias', 'buscar' => ['x'], 'tipo_insumo' => ['Hilo']]))->assertOk();
        $this->actingAs($admin)->get(route('compras.index', ['vista' => 'existencias', 'buscar' => ['x']]))->assertOk();
    }

    public function test_el_historial_con_id_no_numerico_da_404(): void
    {
        $this->actingAs($this->admin())->get('/movimiento-insumo/historial/abc')->assertNotFound();
    }

    public function test_el_historial_de_un_insumo_inhabilitado_se_muestra_con_aviso(): void
    {
        $admin = $this->admin();
        $insumo = $this->insumo(['nombre' => 'Hilo viejo']);
        $this->movimiento($insumo);
        $insumo->delete();

        $this->actingAs($admin)->get(route('movimiento-insumo.historial', $insumo->id))->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Movimientos/Historial')
                ->where('insumo.nombre', 'Hilo viejo')
                ->where('insumo.inhabilitado', true)
                ->has('movimientos.data', 1));
    }

    public function test_existencias_lista_primero_los_insumos_mas_recientes(): void
    {
        $admin = $this->admin();
        $viejo = $this->insumo(['nombre' => 'Aguja']);
        $nuevo = $this->insumo(['nombre' => 'Zipper']);

        $this->actingAs($admin)->get(route('movimiento-insumo.index', ['vista' => 'existencias']))
            ->assertInertia(fn (Assert $p) => $p
                ->where('existencias.data.0.id', $nuevo->id)
                ->where('existencias.data.1.id', $viejo->id));
    }
}
