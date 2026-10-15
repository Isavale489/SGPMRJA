<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Atributo;
use App\Models\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Atributos (maestro-detalle): contrato de las filas con
 * tipos.ts, atributo seleccionado en la URL y respuestas para Inertia.
 */
class AtributoPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    /** Claves de `export interface <Nombre> { ... }` en tipos.ts. */
    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Atributos/tipos.ts'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz} de tipos.ts");

        return $claves[1];
    }

    private function inertia(): array
    {
        return [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()),
            'X-Requested-With' => 'XMLHttpRequest',
        ];
    }

    private function mangaConValores(): Atributo
    {
        $a = Atributo::create(['nombre' => 'Manga', 'codigo' => 'MNG']);
        $a->valores()->create(['nombre' => 'Corta', 'codigo' => 'C', 'orden' => 2]);
        $a->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);

        return $a;
    }

    public function test_las_filas_coinciden_con_sus_interfaces_de_typescript(): void
    {
        $a = $this->mangaConValores();
        $a->tiposProducto()->attach(TipoProducto::forceCreate(['nombre' => 'Chemise', 'prefijo' => 'CHE'])->id);

        $this->actingAs($this->admin())->get(route('atributos.index', ['atributo' => $a->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Atributos/Index')
                ->has('atributos', 1, fn (Assert $fila) => $fila->hasAll($this->clavesTs('AtributoFila'))) // sin ->etc()
                ->has('valores', 2, fn (Assert $fila) => $fila->hasAll($this->clavesTs('ValorFila')))
                ->where('seleccionado', $a->id)
                ->where('atributos.0.tipos_producto_ids', [TipoProducto::sole()->id])
                ->has('tiposProducto', 1)
                ->has('urls.index'));
    }

    public function test_los_valores_llegan_en_su_orden_y_sin_seleccion_no_hay(): void
    {
        $a = $this->mangaConValores();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('atributos.index', ['atributo' => $a->id]))
            ->assertInertia(fn (Assert $p) => $p->where('valores.0.nombre', 'Larga')->where('valores.1.nombre', 'Corta'));
        $this->actingAs($admin)->get(route('atributos.index'))
            ->assertInertia(fn (Assert $p) => $p->where('seleccionado', null)->has('valores', 0));
        $this->actingAs($admin)->get(route('atributos.index', ['atributo' => 999]))
            ->assertInertia(fn (Assert $p) => $p->where('seleccionado', null)->has('valores', 0));
    }

    public function test_elegir_otro_atributo_recarga_solo_sus_valores(): void
    {
        $a = $this->mangaConValores();

        // Regresión del diseño: Inertia también manda X-Requested-With; no debe recibir el JSON de Productos.
        $this->actingAs($this->admin())
            ->withHeaders([...$this->inertia(), 'X-Inertia-Partial-Component' => 'Atributos/Index', 'X-Inertia-Partial-Data' => 'seleccionado,valores'])
            ->get(route('atributos.index', ['atributo' => $a->id]))
            ->assertOk()
            ->assertJsonPath('component', 'Atributos/Index')
            ->assertJsonCount(2, 'props.valores')
            ->assertJsonMissingPath('props.atributos');
    }

    public function test_crear_desde_inertia_deja_seleccionado_el_atributo_nuevo(): void
    {
        $resp = $this->actingAs($this->admin())->withHeaders($this->inertia())
            ->post(route('atributos.store'), ['nombre' => 'Cuello', 'codigo' => 'cll']);

        $a = Atributo::sole();
        $this->assertSame('CLL', $a->codigo); // se normaliza a mayúsculas
        $resp->assertRedirect(route('atributos.index', ['atributo' => $a->id]))
            ->assertSessionHas('success', 'Atributo creado correctamente.');
    }

    public function test_una_regla_de_negocio_llega_a_inertia_como_aviso_de_error(): void
    {
        $a = $this->mangaConValores();
        $a->tiposProducto()->attach(TipoProducto::forceCreate(['nombre' => 'Chemise', 'prefijo' => 'CHE'])->id);

        $this->actingAs($this->admin())->from(route('atributos.index', ['atributo' => $a->id]))->withHeaders($this->inertia())
            ->delete(route('atributos.destroy', $a))
            ->assertRedirect(route('atributos.index', ['atributo' => $a->id]))
            ->assertSessionHas('error', 'No se puede eliminar: el atributo está asignado a uno o más tipos de producto.');

        $this->assertModelExists($a);
    }

    public function test_reordenar_desde_inertia_no_deja_aviso_pendiente(): void
    {
        // Las flechas recargan solo los valores; un flash quedaría colgado para la próxima visita.
        $a = $this->mangaConValores();
        $ids = $a->valores()->pluck('id')->reverse()->values()->all();

        $this->actingAs($this->admin())->from(route('atributos.index', ['atributo' => $a->id]))->withHeaders($this->inertia())
            ->put(route('atributos.valores.reorder', $a), ['ids' => $ids])
            ->assertRedirect()
            ->assertSessionMissing('success');

        $this->assertSame(['Corta', 'Larga'], $a->valores()->pluck('nombre')->all());
    }
}
