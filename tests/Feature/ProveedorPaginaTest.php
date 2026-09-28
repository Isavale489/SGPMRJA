<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Proveedores (piloto del paso 3): props, filtros por URL
 * y el contrato de cada fila con su interfaz de TypeScript.
 */
class ProveedorPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function crear(string $nombre, string $prefijo, string $doc, string $tipo = 'juridico'): Proveedor
    {
        $persona = Persona::create(['nombre' => $nombre, 'tipo_documento' => $prefijo, 'documento_identidad' => $doc, 'email' => strtolower(str_replace(' ', '', $nombre)).'@correo.com']);

        return Proveedor::create(['persona_id' => $persona->id, 'tipo_proveedor' => $tipo, 'estado' => 1]);
    }

    /** Claves de `export interface ProveedorFila { ... }` en tipos.ts. */
    private function clavesTs(): array
    {
        $ts = file_get_contents(resource_path('js/pages/Proveedores/tipos.ts'));
        preg_match('/export interface ProveedorFila \{(.*?)\n\}/s', $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);

        return $claves[1];
    }

    public function test_cada_fila_coincide_con_su_interfaz_de_typescript(): void
    {
        $this->crear('Textiles del Llano C.A.', 'J-', '40123456');
        $claves = $this->clavesTs();
        $this->assertNotEmpty($claves, 'No se pudo leer ProveedorFila de tipos.ts');

        $this->actingAs($this->admin())->get(route('proveedores.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Proveedores/Index')
                ->has('proveedores.data', 1, fn (Assert $fila) => $fila->hasAll($claves)) // sin ->etc(): una clave extra también falla
                ->has('estados.Portuguesa')
                ->has('urls.index'));
    }

    public function test_busca_por_nombre_documento_o_correo(): void
    {
        $this->crear('Textiles del Llano C.A.', 'J-', '40123456');
        $this->crear('Hilos Araure', 'J-', '30999888');
        $admin = $this->admin();

        foreach (['llano' => 'Textiles del Llano C.A.', 'J-3099' => 'Hilos Araure', 'hilosaraure@' => 'Hilos Araure'] as $termino => $esperado) {
            $this->actingAs($admin)->get(route('proveedores.index', ['buscar' => $termino]))
                ->assertInertia(fn (Assert $page) => $page
                    ->has('proveedores.data', 1)
                    ->where('proveedores.data.0.nombre', $esperado)
                    ->where('filtros.buscar', $termino));
        }
    }

    public function test_filtra_por_tipo_y_ordena_por_nombre(): void
    {
        $this->crear('Zapata Insumos', 'J-', '40000001');
        $this->crear('Ana Rojas', 'V-', '12000001', 'natural');
        $this->crear('Botones Acarigua', 'J-', '40000002');
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('proveedores.index', ['tipo' => 'juridico', 'orden' => 'nombre_asc']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('proveedores.data', 2)
                ->where('proveedores.data.0.nombre', 'Botones Acarigua')
                ->where('proveedores.data.1.nombre', 'Zapata Insumos'));
    }

    public function test_el_historial_muestra_solo_inhabilitados(): void
    {
        $this->crear('Activo S.A.', 'J-', '40000003');
        $this->crear('Inhabilitado S.A.', 'J-', '40000004')->delete();

        $this->actingAs($this->admin())->get(route('proveedores.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('proveedores.data', 1)
                ->where('proveedores.data.0.nombre', 'Inhabilitado S.A.')
                ->where('proveedores.data.0.inhabilitado', true));
    }

    public function test_pagina_de_a_15(): void
    {
        foreach (range(1, 17) as $i) {
            $this->crear("Proveedor {$i}", 'J-', (string) (40100000 + $i));
        }

        $this->actingAs($this->admin())->get(route('proveedores.index', ['page' => 2]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('proveedores.data', 2)
                ->where('proveedores.total', 17)
                ->where('proveedores.current_page', 2));
    }

    public function test_una_mutacion_desde_inertia_redirige_con_mensaje(): void
    {
        $p = $this->crear('Textiles del Llano C.A.', 'J-', '40123456');

        $this->actingAs($this->admin())
            ->from(route('proveedores.index'))
            ->withHeaders(['X-Inertia' => 'true'])
            ->delete(route('proveedores.destroy', $p))
            ->assertRedirect(route('proveedores.index'))
            ->assertSessionHas('success', 'Proveedor inhabilitado exitosamente.');
    }
}
