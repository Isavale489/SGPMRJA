<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Atributo;
use App\Models\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia del catálogo (/productos lista tipos de producto): contrato
 * de la fila con su interfaz TS, filtros y el camino real del formulario.
 */
class ProductosPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Productos/Index.tsx'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_cada_fila_coincide_con_su_interfaz_de_typescript(): void
    {
        $t = TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM', 'precio_confeccion' => 6]);
        $t->atributos()->attach(Atributo::create(['nombre' => 'Manga', 'codigo' => 'MNG'])->id, ['orden' => 1, 'es_obligatorio' => true]);
        $t->telas()->attach($this->insumo(['nombre' => 'Oxford', 'codigo' => 'OXF', 'tipo' => 'Tela'])->id);

        $this->actingAs($this->admin())->get(route('productos.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Productos/Index')
                ->has('registros.data', 1, fn (Assert $f) => $f->hasAll($this->clavesTs('TipoProductoFila')))
                ->where('registros.data.0.atributos.0.nombre', 'Manga')
                ->where('registros.data.0.telas.0.codigo', 'OXF')
                ->has('catalogo.atributos', 1)
                ->has('catalogo.telas', 1)
                ->has('urls.tipos'));
    }

    public function test_busca_por_nombre_o_prefijo_y_muestra_el_historial(): void
    {
        TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM']);
        TipoProducto::forceCreate(['nombre' => 'Pantalón', 'prefijo' => 'PNT']);
        TipoProducto::forceCreate(['nombre' => 'Gorra', 'prefijo' => 'GRR'])->delete();
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('productos.index', ['buscar' => 'PN']))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1)->where('registros.data.0.nombre', 'Pantalón'));
        $this->actingAs($admin)->get(route('productos.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $p) => $p->has('registros.data', 1)->where('registros.data.0.nombre', 'Gorra')->where('registros.data.0.inhabilitado', true));
    }

    public function test_editar_desde_el_formulario_con_imagen_conserva_el_prefijo(): void
    {
        // Camino real del formulario: POST multipart + _method=put (PHP no lee multipart en PUT).
        $t = TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM']);

        $this->actingAs($this->admin())
            ->from(route('productos.index'))
            ->withHeaders($this->inertia())
            ->post(url("/tipo-productos/{$t->id}"), [
                '_method' => 'put',
                'nombre' => 'Camisa de vestir',
                'prefijo' => 'ZZZ',
                'precio_confeccion' => 7.5,
                'requiere_tela' => '0',
                'requiere_produccion' => '1',
                'imagen' => UploadedFile::fake()->image('camisa.jpg', 60, 60),
            ])
            ->assertRedirect(route('productos.index'))
            ->assertSessionHas('success', 'Tipo de producto actualizado correctamente.');

        $t->refresh();
        $this->assertSame('Camisa de vestir', $t->nombre);
        $this->assertSame('CAM', $t->prefijo);
        $this->assertFalse((bool) $t->requiere_tela);
        $this->assertNotNull($t->imagen);
        $this->assertFileExists(public_path($t->imagen));
        @unlink(public_path($t->imagen)); // no dejar archivos en public/ del repo
    }

    public function test_solo_se_permiten_telas_como_telas_del_tipo(): void
    {
        $t = TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM']);
        $boton = $this->insumo(['nombre' => 'Botón', 'codigo' => 'BTN', 'tipo' => 'Mercería']);

        $this->actingAs($this->admin())
            ->putJson(url("/tipo-productos/{$t->id}"), ['nombre' => 'Camisa', 'telas' => [$boton->id]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('telas.0');
    }

    public function test_sin_permiso_no_se_ve_el_catalogo(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->get(route('productos.index'))->assertForbidden();
    }
}
