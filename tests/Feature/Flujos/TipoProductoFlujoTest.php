<?php

namespace Tests\Feature\Flujos;

use App\Models\Atributo;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Catálogo de productos = Tipos de producto (FEAT-003). Escritos ANTES de
 * migrar /productos a Inertia.
 *
 * Contratos vivos (Cotizaciones, jQuery): GET /tipo-productos/{id} (selector de
 * variante), POST /tipo-productos/{id}/telas (alta rápida de tela) y
 * GET /productos-resolver-variante (SKU/precio de la variante).
 */
class TipoProductoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function tela(string $codigo = 'OXF'): Insumo
    {
        return $this->insumo(['nombre' => "Tela {$codigo}", 'codigo' => $codigo, 'tipo' => 'Tela', 'unidad_medida' => 'Metro', 'costo_unitario' => 4]);
    }

    /** Atributo Manga (L, C) y Cuello (MAO). */
    private function atributos(): array
    {
        $manga = Atributo::create(['nombre' => 'Manga', 'codigo' => 'MNG']);
        $larga = $manga->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);
        $manga->valores()->create(['nombre' => 'Corta', 'codigo' => 'C', 'orden' => 2]);
        $cuello = Atributo::create(['nombre' => 'Cuello', 'codigo' => 'CLL']);
        $mao = $cuello->valores()->create(['nombre' => 'Mao', 'codigo' => 'MAO', 'orden' => 1]);

        return compact('manga', 'cuello', 'larga', 'mao');
    }

    private function payload(array $extra = []): array
    {
        $a = $this->atributos();

        return array_merge([
            'nombre' => 'Camisa',
            'prefijo' => 'cam',
            'descripcion' => 'Camisa de vestir',
            'precio_confeccion' => 6.5,
            'requiere_tela' => 1,
            'requiere_produccion' => 1,
            'consumo_tela_por_unidad' => 1.6,
            'atributos' => [['id' => $a['manga']->id, 'orden' => 1], ['id' => $a['cuello']->id, 'orden' => 2]],
            'insumos_default' => [['id' => $this->insumo(['nombre' => 'Botón', 'codigo' => 'BTN'])->id, 'cantidad_estimada' => 7]],
            'telas' => [$this->tela()->id],
        ], $extra);
    }

    public function test_alta_de_tipo_con_atributos_telas_e_insumos(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('tipo-productos.store'), $this->payload()));

        $t = TipoProducto::sole();
        $this->assertSame('CAM', $t->prefijo);
        $this->assertEquals(6.5, (float) $t->precio_confeccion);
        $this->assertSame(['Manga', 'Cuello'], $t->atributos()->orderBy('tipo_producto_atributo.orden')->pluck('nombre')->all());
        $this->assertSame(1, $t->telas()->count());
        $this->assertEquals(7, (float) $t->insumosDefault()->sole()->pivot->cantidad_estimada);
    }

    public function test_nombre_y_prefijo_unicos_y_prefijo_solo_letras(): void
    {
        $admin = $this->admin();
        TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM']);

        $this->actingAs($admin)->postJson(route('tipo-productos.store'), ['nombre' => 'Camisa', 'prefijo' => 'CMS'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');
        $this->actingAs($admin)->postJson(route('tipo-productos.store'), ['nombre' => 'Chemise', 'prefijo' => 'CAM'])
            ->assertStatus(422)->assertJsonValidationErrors('prefijo');
        $this->actingAs($admin)->postJson(route('tipo-productos.store'), ['nombre' => 'Chemise', 'prefijo' => 'CH3'])
            ->assertStatus(422)->assertJsonValidationErrors('prefijo');
    }

    public function test_editar_no_cambia_el_prefijo(): void
    {
        // El prefijo forma parte del SKU: inmutable tras crearlo (docs/conventions/code-immutability.md),
        // aunque el cliente lo mande cambiado.
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('tipo-productos.store'), $this->payload());
        $t = TipoProducto::sole();

        $this->assertExito($this->actingAs($admin)->put(route('tipo-productos.update', $t), [
            'nombre' => 'Camisa de vestir', 'prefijo' => 'XYZ', 'precio_confeccion' => 7,
            'atributos' => [], 'insumos_default' => [], 'telas' => [],
        ]));

        $t->refresh();
        $this->assertSame('Camisa de vestir', $t->nombre);
        $this->assertSame('CAM', $t->prefijo);
    }

    public function test_editar_resincroniza_telas_y_orden_de_atributos(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('tipo-productos.store'), $this->payload());
        $t = TipoProducto::sole();
        [$manga, $cuello] = [Atributo::where('codigo', 'MNG')->sole(), Atributo::where('codigo', 'CLL')->sole()];
        $gab = $this->tela('GBD');

        $this->assertExito($this->actingAs($admin)->put(route('tipo-productos.update', $t), [
            'nombre' => 'Camisa', 'prefijo' => 'CAM',
            'atributos' => [['id' => $cuello->id, 'orden' => 1], ['id' => $manga->id, 'orden' => 2]],
            'insumos_default' => [],
            'telas' => [$gab->id],
        ]));

        $this->assertSame(['Cuello', 'Manga'], $t->atributos()->orderBy('tipo_producto_atributo.orden')->pluck('nombre')->all());
        $this->assertSame([$gab->id], $t->telas()->pluck('insumo.id')->all());
        $this->assertSame(0, $t->insumosDefault()->count());
    }

    public function test_no_se_inhabilita_un_tipo_con_productos_y_se_restaura(): void
    {
        $admin = $this->admin();
        $t = TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM']);
        Producto::forceCreate(['tipo_producto_id' => $t->id, 'precio_base' => 10, 'codigo' => 'CAM-001']);

        $this->actingAs($admin)->deleteJson(route('tipo-productos.destroy', $t))->assertStatus(422)
            ->assertJsonPath('message', 'No se puede inhabilitar. Hay productos asociados a este tipo.');
        $this->assertNotSoftDeleted($t);

        Producto::query()->forceDelete();
        $this->assertExito($this->actingAs($admin)->delete(route('tipo-productos.destroy', $t)));
        $this->assertSoftDeleted($t);
        $this->assertExito($this->actingAs($admin)->patch(route('tipo-productos.restore', $t->id)));
        $this->assertNotSoftDeleted($t);
    }

    public function test_cotizaciones_lee_el_tipo_con_atributos_valores_y_telas(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('tipo-productos.store'), $this->payload());
        $t = TipoProducto::sole();

        $this->actingAs($admin)->getJson(url("/tipo-productos/{$t->id}"))
            ->assertOk()
            ->assertJsonPath('prefijo', 'CAM')
            ->assertJsonPath('atributos.0.nombre', 'Manga')
            ->assertJsonPath('atributos.0.valores.0.codigo', 'L')
            ->assertJsonPath('telas.0.codigo', 'OXF')
            ->assertJsonStructure(['id', 'nombre', 'prefijo', 'precio_confeccion', 'requiere_tela', 'consumo_tela_por_unidad', 'atributos', 'insumos_default', 'telas']);
    }

    public function test_cotizaciones_crea_una_tela_y_queda_asignada_al_tipo(): void
    {
        $t = TipoProducto::forceCreate(['nombre' => 'Camisa', 'prefijo' => 'CAM']);

        $this->actingAs($this->admin())
            ->postJson(url("/tipo-productos/{$t->id}/telas"), ['nombre' => 'Lino', 'codigo' => 'LIN', 'unidad_medida' => 'Metro', 'costo_unitario' => 5])
            ->assertOk()->assertJsonPath('tela.codigo', 'LIN');

        $this->assertSame('Tela', Insumo::where('codigo', 'LIN')->sole()->tipo);
        $this->assertSame(['LIN'], $t->telas()->pluck('codigo')->all());
    }

    public function test_cotizaciones_resuelve_una_variante_dinamica(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('tipo-productos.store'), $this->payload());
        $t = TipoProducto::sole();
        $valores = [Atributo::where('codigo', 'MNG')->sole()->valores()->where('codigo', 'L')->value('id'), Atributo::where('codigo', 'CLL')->sole()->valores()->value('id')];

        $this->actingAs($admin)->getJson(route('productos.resolver-variante', [
            'tipo_producto_id' => $t->id, 'insumo_tela_id' => Insumo::where('codigo', 'OXF')->value('id'), 'atributo_valor_ids' => $valores,
        ]))
            ->assertOk()
            ->assertJsonPath('dynamic', true)
            ->assertJsonPath('producto.codigo', 'CAM-OXF-L-MAO-001');
    }
}
