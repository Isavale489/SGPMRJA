<?php

namespace Tests\Feature;

use App\Models\Cargo;
use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Empleado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Empleados: contrato de la fila con tipos.ts, búsqueda,
 * filtros, historial y alta rápida de departamento desde el formulario.
 */
class EmpleadoPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(\App\Http\Middleware\HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    private function conCargo(string $nombre, string $cargo, string $depto): Empleado
    {
        $d = Departamento::firstOrCreate(['nombre' => $depto]);
        $c = Cargo::firstOrCreate(['nombre' => $cargo, 'departamento_id' => $d->id]);
        $e = $this->empleado($nombre);
        $e->update(['cargo_id' => $c->id, 'departamento_id' => $d->id]);

        return $e;
    }

    /** Claves de `export interface EmpleadoFila { ... }` en tipos.ts. */
    private function clavesTs(): array
    {
        $ts = file_get_contents(resource_path('js/pages/Empleados/tipos.ts'));
        preg_match('/export interface EmpleadoFila \{(.*?)\n\}/s', $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);

        return $claves[1];
    }

    public function test_cada_fila_coincide_con_su_interfaz_de_typescript(): void
    {
        $this->conCargo('Ana Pérez', 'Costurera', 'Producción');
        $claves = $this->clavesTs();
        $this->assertNotEmpty($claves, 'No se pudo leer EmpleadoFila de tipos.ts');

        $this->actingAs($this->admin())->get(route('empleados.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Empleados/Index')
                ->has('empleados.data', 1, fn (Assert $fila) => $fila->hasAll($claves)) // sin ->etc()
                ->where('empleados.data.0.cargo', 'Costurera')
                ->has('departamentos', 1)
                ->has('cargos.0.departamento_id')
                ->has('urls.departamentos'));
    }

    public function test_busca_y_filtra_por_departamento_y_cargo(): void
    {
        $this->conCargo('Ana Pérez', 'Costurera', 'Producción');
        $this->conCargo('Luis Rojas', 'Cortador', 'Producción');
        $vendedor = $this->conCargo('Carmen Silva', 'Vendedora', 'Ventas');
        $admin = $this->admin();

        $casos = [
            [['buscar' => 'rojas'], ['Luis Rojas']],
            [['buscar' => 'vended'], ['Carmen Silva']],
            [['buscar' => $vendedor->codigo_empleado], ['Carmen Silva']],
            [['departamento' => Departamento::where('nombre', 'Producción')->value('id'), 'orden' => 'nombre_asc'], ['Ana Pérez', 'Luis Rojas']],
            [['cargo' => Cargo::where('nombre', 'Cortador')->value('id')], ['Luis Rojas']],
        ];
        foreach ($casos as [$filtros, $esperados]) {
            $this->actingAs($admin)->get(route('empleados.index', $filtros))
                ->assertInertia(fn (Assert $page) => $page->where('empleados.data', fn ($filas) => collect($filas)->pluck('nombre')->all() === $esperados));
        }
    }

    public function test_el_historial_muestra_solo_inhabilitados_y_avisa_otros_roles(): void
    {
        $this->empleado('Activo');
        $retirado = $this->empleado('Retirado');
        Cliente::forceCreate(['persona_id' => $retirado->persona_id, 'tipo_cliente' => 'natural', 'estatus' => 1]);
        $retirado->delete();

        $this->actingAs($this->admin())->get(route('empleados.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $p) => $p->has('empleados.data', 1)
                ->where('empleados.data.0.nombre', 'Retirado')
                ->where('empleados.data.0.inhabilitado', true)
                ->where('empleados.data.0.otros_roles', ['cliente']));
    }

    public function test_alta_rapida_de_departamento_recarga_solo_el_catalogo(): void
    {
        // El formulario crea el departamento con router.post(only: departamentos, flash).
        $this->actingAs($this->admin())->from(route('empleados.index'))->withHeaders($this->inertia())
            ->post(route('departamentos.store'), ['nombre' => 'Logística'])
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('success', 'Departamento creado correctamente.');

        $this->assertDatabaseHas('departamento', ['nombre' => 'Logística']);
    }

    public function test_una_mutacion_desde_inertia_redirige_con_mensaje(): void
    {
        $e = $this->empleado();

        $this->actingAs($this->admin())->from(route('empleados.index'))->withHeaders(['X-Inertia' => 'true'])
            ->delete(route('empleados.destroy', $e->id))
            ->assertRedirect(route('empleados.index'))
            ->assertSessionHas('success', 'Empleado inhabilitado exitosamente.');
    }
}
