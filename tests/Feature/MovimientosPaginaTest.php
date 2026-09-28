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
}
