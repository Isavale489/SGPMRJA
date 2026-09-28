<?php

namespace Tests\Feature;

use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Insumos: contrato de la fila con tipos.ts, filtros,
 * historial, catálogo de tipos y respuestas para Inertia.
 */
class InsumoPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
        TipoInsumo::firstOrCreate(['nombre' => 'Hilo'], ['activo' => true]);
        TipoInsumo::firstOrCreate(['nombre' => 'Tela'], ['activo' => true]);
    }

    /** Claves de `export interface <Nombre> { ... }` en tipos.ts. */
    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Insumos/tipos.ts'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz} de tipos.ts");

        return $claves[1];
    }

    public function test_las_filas_coinciden_con_sus_interfaces_de_typescript(): void
    {
        $this->insumo(['nombre' => 'Hilo negro']);

        $this->actingAs($this->admin())->get(route('insumos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Insumos/Index')
                ->has('insumos.data', 1, fn (Assert $fila) => $fila->hasAll($this->clavesTs('InsumoFila'))) // sin ->etc()
                ->has('tiposInsumo.0', fn (Assert $fila) => $fila->hasAll($this->clavesTs('TipoInsumoFila')))
                ->has('unidades', 7)
                ->has('urls.tipos'));
    }

    public function test_busca_y_filtra_por_tipo_y_existencia(): void
    {
        $this->insumo(['nombre' => 'Hilo negro', 'codigo' => 'HN01', 'stock_actual' => 20, 'stock_minimo' => 5]);
        $this->insumo(['nombre' => 'Hilo blanco', 'stock_actual' => 3, 'stock_minimo' => 5]);
        $this->insumo(['nombre' => 'Popelina azul', 'tipo' => 'Tela', 'stock_actual' => 0, 'stock_minimo' => 0]);
        $this->insumo(['nombre' => 'Etiqueta de servicio', 'is_inventoriable' => 0, 'stock_actual' => 0, 'stock_minimo' => 0]);
        $admin = $this->admin();

        $casos = [
            [['buscar' => 'HN0'], ['Hilo negro']],
            [['buscar' => 'popel'], ['Popelina azul']],
            [['tipo' => 'Tela'], ['Popelina azul']],
            [['stock' => 'agotado', 'orden' => 'nombre'], ['Etiqueta de servicio', 'Popelina azul']],
            // En o bajo el mínimo: solo inventariables (el no inventariable no se repone).
            [['stock' => 'bajo', 'orden' => 'nombre'], ['Hilo blanco', 'Popelina azul']],
        ];
        foreach ($casos as [$filtros, $esperados]) {
            $this->actingAs($admin)->get(route('insumos.index', $filtros))
                ->assertInertia(fn (Assert $page) => $page->where('insumos.data', fn ($filas) => collect($filas)->pluck('nombre')->all() === $esperados));
        }
    }

    public function test_nivel_de_stock_y_no_inventariable(): void
    {
        $this->insumo(['nombre' => 'A', 'stock_actual' => 7, 'stock_minimo' => 5]);
        $this->insumo(['nombre' => 'B', 'is_inventoriable' => 0, 'stock_actual' => 0, 'stock_minimo' => 0]);

        $this->actingAs($this->admin())->get(route('insumos.index', ['orden' => 'nombre']))
            ->assertInertia(fn (Assert $p) => $p->where('insumos.data.0.nivel_stock', 'medio')->where('insumos.data.1.nivel_stock', null));
    }

    public function test_el_historial_muestra_solo_inhabilitados(): void
    {
        $this->insumo(['nombre' => 'Activo']);
        $this->insumo(['nombre' => 'Retirado'])->delete();

        $this->actingAs($this->admin())->get(route('insumos.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $p) => $p->has('insumos.data', 1)->where('insumos.data.0.nombre', 'Retirado')->where('insumos.data.0.inhabilitado', true));
    }

    public function test_el_catalogo_de_tipos_vive_en_la_pagina_de_insumos(): void
    {
        $this->actingAs($this->admin())->get(route('tipo-insumos.index'))->assertRedirect(route('insumos.index'));
    }

    public function test_inhabilitar_un_tipo_en_uso_llega_a_inertia_como_aviso(): void
    {
        $this->insumo(['tipo' => 'Hilo']);
        $tipo = TipoInsumo::where('nombre', 'Hilo')->sole();

        $this->actingAs($this->admin())->from(route('insumos.index'))->withHeaders(['X-Inertia' => 'true'])
            ->delete(route('tipo-insumos.destroy', $tipo))
            ->assertRedirect(route('insumos.index'))
            ->assertSessionHas('error', 'No se puede inhabilitar: hay insumos que usan este tipo.');
    }

    public function test_una_mutacion_desde_inertia_redirige_con_mensaje(): void
    {
        $i = $this->insumo();

        $this->actingAs($this->admin())->from(route('insumos.index'))->withHeaders(['X-Inertia' => 'true'])
            ->delete(route('insumos.destroy', $i->id))
            ->assertRedirect(route('insumos.index'))
            ->assertSessionHas('success', 'Insumo inhabilitado exitosamente.');
    }
}
