<?php

namespace Tests\Feature\Flujos;

use App\Models\Atributo;
use App\Models\AtributoValor;
use App\Models\Producto;
use App\Models\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Atributos y sus valores (maestro-detalle). Escritos ANTES de migrar a
 * Inertia. Reglas clave: los códigos son inmutables (forman el SKU) y no se
 * borra lo que usa un tipo de producto o un producto.
 *
 * Contrato vivo: Productos (jQuery) lee atributos.index con $.getJSON.
 */
class AtributoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function manga(): Atributo
    {
        return Atributo::create(['nombre' => 'Manga', 'codigo' => 'MNG']);
    }

    public function test_alta_normaliza_el_codigo_y_asigna_tipos(): void
    {
        $tipo = TipoProducto::forceCreate(['nombre' => 'Chemise', 'prefijo' => 'CHE']);

        $this->assertExito($this->actingAs($this->admin())->post(route('atributos.store'), [
            'nombre' => ' Manga ', 'codigo' => 'MNG', 'descripcion' => 'Largo de la manga', 'tipos_producto' => [$tipo->id],
        ]));

        $a = Atributo::sole();
        $this->assertSame('Manga', $a->nombre);
        $this->assertSame('MNG', $a->codigo);
        $this->assertSame([$tipo->id], $a->tiposProducto()->pluck('tipo_producto.id')->all());
    }

    public function test_codigo_invalido_o_repetido_se_rechaza(): void
    {
        $admin = $this->admin();
        $this->manga();

        $this->actingAs($admin)->postJson(route('atributos.store'), ['nombre' => 'Cuello', 'codigo' => 'cu-1'])
            ->assertStatus(422)->assertJsonValidationErrors('codigo');
        $this->actingAs($admin)->postJson(route('atributos.store'), ['nombre' => 'Otra', 'codigo' => 'MNG'])
            ->assertStatus(422)->assertJsonValidationErrors('codigo');
    }

    public function test_editar_no_cambia_el_codigo(): void
    {
        $a = $this->manga();

        $this->assertExito($this->actingAs($this->admin())->put(route('atributos.update', $a), ['nombre' => 'Tipo de manga', 'codigo' => 'XXX']));

        $a->refresh();
        $this->assertSame('Tipo de manga', $a->nombre);
        $this->assertSame('MNG', $a->codigo);
    }

    public function test_no_se_borra_un_atributo_asignado_a_un_tipo(): void
    {
        $a = $this->manga();
        $a->tiposProducto()->attach(TipoProducto::forceCreate(['nombre' => 'Chemise', 'prefijo' => 'CHE'])->id);

        $this->actingAs($this->admin())->deleteJson(route('atributos.destroy', $a))->assertStatus(422)
            ->assertJsonPath('message', 'No se puede eliminar: el atributo está asignado a uno o más tipos de producto.');

        $this->assertModelExists($a);
    }

    public function test_valores_unicos_por_atributo_con_codigo_inmutable(): void
    {
        $admin = $this->admin();
        $a = $this->manga();

        $this->assertExito($this->actingAs($admin)->post(route('atributos.valores.store', $a), ['nombre' => 'Larga', 'codigo' => 'L']));
        $this->actingAs($admin)->postJson(route('atributos.valores.store', $a), ['nombre' => 'Larga', 'codigo' => 'LG'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');
        $this->actingAs($admin)->postJson(route('atributos.valores.store', $a), ['nombre' => 'Larguísima', 'codigo' => 'L'])
            ->assertStatus(422)->assertJsonValidationErrors('codigo');

        $v = AtributoValor::sole();
        $this->assertSame(1, (int) $v->orden); // al final de la lista
        $this->assertExito($this->actingAs($admin)->put(route('atributos.valores.update', [$a, $v]), ['nombre' => 'Manga larga', 'codigo' => 'ZZ']));
        $this->assertSame('Manga larga', $v->fresh()->nombre);
        $this->assertSame('L', $v->fresh()->codigo);
    }

    public function test_reordenar_valores(): void
    {
        $a = $this->manga();
        $larga = $a->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);
        $corta = $a->valores()->create(['nombre' => 'Corta', 'codigo' => 'C', 'orden' => 2]);
        $sisa = $a->valores()->create(['nombre' => 'Sisa', 'codigo' => 'S', 'orden' => 3]);

        $this->assertExito($this->actingAs($this->admin())->put(route('atributos.valores.reorder', $a), ['ids' => [$sisa->id, $larga->id, $corta->id]]));

        $this->assertSame(['Sisa', 'Larga', 'Corta'], $a->valores()->pluck('nombre')->all());
    }

    public function test_no_se_reordena_con_valores_de_otro_atributo(): void
    {
        $a = $this->manga();
        $v = $a->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);
        $ajeno = Atributo::create(['nombre' => 'Cuello', 'codigo' => 'CLL'])->valores()->create(['nombre' => 'V', 'codigo' => 'V', 'orden' => 1]);

        $this->actingAs($this->admin())->putJson(route('atributos.valores.reorder', $a), ['ids' => [$ajeno->id, $v->id]])->assertStatus(422);

        $this->assertSame(1, (int) $ajeno->fresh()->orden);
    }

    public function test_no_se_borra_un_valor_usado_por_un_producto(): void
    {
        $a = $this->manga();
        $v = $a->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);
        $producto = Producto::forceCreate(['precio_base' => 10]);
        $v->productos()->attach($producto->id);

        $this->actingAs($this->admin())->deleteJson(route('atributos.valores.destroy', [$a, $v]))->assertStatus(422)
            ->assertJsonPath('message', 'No se puede eliminar: 1 producto(s) usan este valor.');

        $this->assertModelExists($v);
    }

    public function test_borrar_valor_y_atributo_libres(): void
    {
        $admin = $this->admin();
        $a = $this->manga();
        $v = $a->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);

        $this->assertExito($this->actingAs($admin)->delete(route('atributos.valores.destroy', [$a, $v])));
        $this->assertModelMissing($v);
        $this->assertExito($this->actingAs($admin)->delete(route('atributos.destroy', $a)));
        $this->assertModelMissing($a);
    }

    public function test_productos_lee_los_atributos_por_json(): void
    {
        $a = $this->manga();
        $a->valores()->create(['nombre' => 'Larga', 'codigo' => 'L', 'orden' => 1]);

        $this->actingAs($this->admin())->getJson(route('atributos.index'))
            ->assertOk()
            ->assertJsonPath('0.codigo', 'MNG')
            ->assertJsonPath('0.valores_count', 1)
            ->assertJsonStructure([['id', 'nombre', 'codigo', 'descripcion', 'valores_count', 'tipos_producto_count', 'tipos_producto_ids']]);
    }
}
