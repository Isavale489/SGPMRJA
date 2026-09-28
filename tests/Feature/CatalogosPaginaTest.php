<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Color;
use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Http\Middleware\HandleInertiaRequests;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Páginas Inertia de los catálogos simples: contrato de cada fila con la
 * interfaz TS exportada por su página, filtros y rechazo de reglas de negocio.
 */
class CatalogosPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    /** Claves de `export interface <Nombre> { ... }` en la página. */
    private function clavesTs(string $pagina, string $interfaz): array
    {
        $ts = file_get_contents(resource_path("js/pages/{$pagina}/Index.tsx"));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz} de {$pagina}/Index.tsx");

        return $claves[1];
    }

    public static function paginas(): array
    {
        return [
            'departamentos' => ['departamentos.index', 'Departamentos', 'Departamento'],
            'cargos' => ['cargos.index', 'Cargos', 'Cargo'],
            'colores' => ['colores.index', 'Colores', 'Color'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('paginas')]
    public function test_cada_fila_coincide_con_su_interfaz_de_typescript(string $ruta, string $pagina, string $interfaz): void
    {
        $d = Departamento::create(['nombre' => 'Producción']);
        Cargo::create(['nombre' => 'Costurera', 'departamento_id' => $d->id]);
        Color::create(['nombre' => 'Azul Marino', 'hex_referencial' => '#1E3C72']);
        $claves = $this->clavesTs($pagina, $interfaz);

        $this->actingAs($this->admin())->get(route($ruta))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component("{$pagina}/Index")
                ->has('registros.data', 1, fn (Assert $fila) => $fila->hasAll($claves)));
    }

    public function test_cargos_filtra_por_departamento_y_busca(): void
    {
        $prod = Departamento::create(['nombre' => 'Producción']);
        $ventas = Departamento::create(['nombre' => 'Ventas']);
        Cargo::create(['nombre' => 'Costurera', 'departamento_id' => $prod->id]);
        Cargo::create(['nombre' => 'Vendedor', 'departamento_id' => $ventas->id]);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('cargos.index', ['departamento' => $ventas->id]))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1)->where('registros.data.0.nombre', 'Vendedor')->has('departamentos', 2));
        $this->actingAs($admin)->get(route('cargos.index', ['buscar' => 'costur']))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1)->where('registros.data.0.nombre', 'Costurera'));
    }

    public function test_el_historial_muestra_solo_inhabilitados(): void
    {
        Color::create(['nombre' => 'Activo', 'hex_referencial' => '#FFFFFF']);
        Color::create(['nombre' => 'Retirado', 'hex_referencial' => '#000000'])->delete();

        $this->actingAs($this->admin())->get(route('colores.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1)->where('registros.data.0.nombre', 'Retirado'));
    }

    public function test_una_regla_de_negocio_llega_a_inertia_como_aviso_de_error(): void
    {
        $d = Departamento::create(['nombre' => 'Producción']);
        Cargo::create(['nombre' => 'Costurera', 'departamento_id' => $d->id]);

        $this->actingAs($this->admin())
            ->from(route('departamentos.index'))
            ->withHeaders(['X-Inertia' => 'true', 'X-Requested-With' => 'XMLHttpRequest'])
            ->delete(route('departamentos.destroy', $d))
            ->assertRedirect(route('departamentos.index'))
            ->assertSessionHas('error', 'No se puede inhabilitar: el departamento tiene cargos asociados.');

        $this->assertNotSoftDeleted($d);
    }

    public function test_una_recarga_parcial_de_inertia_no_recibe_el_json_de_empleados(): void
    {
        // Regresión del diseño: Inertia también manda X-Requested-With; index()
        // debe responder la página, no el JSON que pide el formulario de Empleados.
        Departamento::create(['nombre' => 'Producción']);

        $this->actingAs($this->admin())
            // Con la versión de assets vigente (si no, Inertia responde 409 y pide recargar).
            ->withHeaders(['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'])
            ->get(route('departamentos.index'))
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Departamentos/Index');
    }
}
