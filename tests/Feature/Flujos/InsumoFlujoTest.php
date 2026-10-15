<?php

namespace Tests\Feature\Flujos;

use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Insumos y su catálogo de tipos. Escritos ANTES de migrar a Inertia.
 * Reglas: código en MAYÚSCULAS e inmutable una vez asignado; tipo del
 * catálogo activo; un insumo no inventariable no lleva stock.
 *
 * Contratos vivos (jQuery): el alta rápida de Compras y la de Movimientos de
 * insumo usan POST /insumos y leen `insumo` del JSON.
 */
class InsumoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        TipoInsumo::firstOrCreate(['nombre' => 'Hilo'], ['activo' => true]);
        TipoInsumo::firstOrCreate(['nombre' => 'Tela'], ['activo' => true]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'nombre' => 'Hilo poliéster negro',
            'codigo' => 'hpn01',
            'tipo' => 'Hilo',
            'unidad_medida' => 'Cono',
            'is_inventoriable' => 1,
            'aplica_iva' => 1,
            'costo_unitario' => 2.5,
            'stock_actual' => 10,
            'stock_minimo' => 5,
            'stock_maximo' => 50,
        ], $extra);
    }

    public function test_alta_normaliza_el_codigo_y_nace_habilitado(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('insumos.store'), $this->payload()));

        $i = Insumo::sole();
        $this->assertSame('HPN01', $i->codigo);
        $this->assertTrue($i->estado);
        $this->assertTrue($i->is_inventoriable);
        $this->assertTrue($i->aplica_iva);
        $this->assertSame('10.00', $i->stock_actual);
        $this->assertSame('50.00', $i->stock_maximo);
    }

    public function test_codigo_invalido_o_repetido_se_rechaza(): void
    {
        $admin = $this->admin();
        $this->insumo(['codigo' => 'HPN01']);

        $this->actingAs($admin)->postJson(route('insumos.store'), $this->payload(['codigo' => 'H-1']))
            ->assertStatus(422)->assertJsonValidationErrors('codigo');
        $this->actingAs($admin)->postJson(route('insumos.store'), $this->payload(['codigo' => 'hpn01']))
            ->assertStatus(422)->assertJsonValidationErrors('codigo');
    }

    public function test_tipo_fuera_del_catalogo_activo_se_rechaza(): void
    {
        $admin = $this->admin();
        TipoInsumo::create(['nombre' => 'Retirado', 'activo' => true])->delete();

        foreach (['Inventado', 'Retirado'] as $tipo) {
            $this->actingAs($admin)->postJson(route('insumos.store'), $this->payload(['tipo' => $tipo, 'codigo' => null]))
                ->assertStatus(422)->assertJsonValidationErrors('tipo');
        }
    }

    public function test_existencia_maxima_menor_que_la_minima_se_rechaza(): void
    {
        $this->actingAs($this->admin())->postJson(route('insumos.store'), $this->payload(['stock_minimo' => 20, 'stock_maximo' => 10]))
            ->assertStatus(422)->assertJsonValidationErrors('stock_maximo');
    }

    public function test_un_insumo_no_inventariable_no_lleva_stock(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('insumos.store'), $this->payload(['is_inventoriable' => 0])));

        $i = Insumo::sole();
        $this->assertFalse($i->is_inventoriable);
        $this->assertSame('0.00', $i->stock_actual);
        $this->assertSame('0.00', $i->stock_minimo);
    }

    public function test_editar_no_cambia_un_codigo_ya_asignado_y_asigna_uno_vacio(): void
    {
        $admin = $this->admin();
        $conCodigo = $this->insumo(['codigo' => 'HPN01']);
        $sinCodigo = $this->insumo(['codigo' => null]);

        $this->assertExito($this->actingAs($admin)->put(route('insumos.update', $conCodigo->id), $this->payload(['codigo' => 'OTRO1', 'nombre' => 'Hilo renombrado'])));
        $this->assertExito($this->actingAs($admin)->put(route('insumos.update', $sinCodigo->id), $this->payload(['codigo' => 'nuevo1', 'nombre' => 'Hilo sin código'])));

        $this->assertSame('HPN01', $conCodigo->fresh()->codigo);
        $this->assertSame('Hilo renombrado', $conCodigo->fresh()->nombre);
        $this->assertSame('NUEVO1', $sinCodigo->fresh()->codigo);
    }

    public function test_inhabilitar_y_habilitar(): void
    {
        $admin = $this->admin();
        $i = $this->insumo();

        $this->assertExito($this->actingAs($admin)->delete(route('insumos.destroy', $i->id)));
        $this->assertSoftDeleted($i);
        $this->assertFalse(Insumo::withTrashed()->find($i->id)->estado);

        $this->assertExito($this->actingAs($admin)->post(route('insumos.restore', $i->id)));
        $this->assertNotSoftDeleted($i);
        $this->assertTrue($i->fresh()->estado);
    }

    public function test_sin_permiso_de_gestion_no_se_crea(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->post(route('insumos.store'), $this->payload());

        $this->assertSame(0, Insumo::count());
    }

    public function test_el_alta_rapida_de_compras_recibe_json_con_el_insumo(): void
    {
        $this->actingAs($this->admin())->postJson(route('insumos.store'), $this->payload(['codigo' => null, 'stock_actual' => 0, 'stock_minimo' => 0, 'stock_maximo' => 0]))
            ->assertOk()
            ->assertJsonPath('success', 'Insumo creado exitosamente.')
            ->assertJsonPath('insumo.nombre', 'Hilo poliéster negro')
            ->assertJsonStructure(['insumo' => ['id', 'nombre', 'codigo', 'tipo', 'unidad_medida', 'costo_unitario', 'aplica_iva', 'stock_actual']]);
    }

    public function test_el_alta_rapida_de_movimientos_crea_un_insumo_inventariable(): void
    {
        // Movimientos no manda is_inventoriable, pero sí su existencia inicial:
        // el insumo tiene que poder moverse (is_inventoriable es vinculante).
        $this->actingAs($this->admin())->postJson(route('insumos.store'), [
            'nombre' => 'Botón nácar', 'tipo' => 'Hilo', 'unidad_medida' => 'Docena',
            'stock_actual' => 30, 'stock_minimo' => 5, 'costo_unitario' => 1.2, 'estado' => 1,
        ])->assertOk()->assertJsonPath('insumo.stock_actual', '30.00');

        $i = Insumo::sole();
        $this->assertTrue($i->is_inventoriable);
        $this->assertSame('30.00', $i->stock_actual);
    }

    public function test_catalogo_de_tipos_renombrar_propaga_e_inhabilitar_exige_que_no_se_use(): void
    {
        $admin = $this->admin();
        $this->assertExito($this->actingAs($admin)->post(route('tipo-insumos.store'), ['nombre' => 'Entretela fusionable']));
        $tipo = TipoInsumo::where('nombre', 'Entretela fusionable')->sole();
        $insumo = $this->insumo(['tipo' => 'Entretela fusionable']);
        $inhabilitado = $this->insumo(['tipo' => 'Entretela fusionable']);
        $inhabilitado->delete();

        $this->assertExito($this->actingAs($admin)->put(route('tipo-insumos.update', $tipo), ['nombre' => 'Entretelas']));
        $this->assertSame('Entretelas', $insumo->fresh()->tipo); // insumo.tipo es texto: se propaga
        // También a los inhabilitados: al habilitarlos, su tipo debe seguir existiendo.
        $this->assertSame('Entretelas', Insumo::withTrashed()->find($inhabilitado->id)->tipo);

        $this->actingAs($admin)->deleteJson(route('tipo-insumos.destroy', $tipo))->assertStatus(422);
        $this->assertNotSoftDeleted($tipo);

        $insumo->update(['tipo' => 'Hilo']);
        $this->assertExito($this->actingAs($admin)->delete(route('tipo-insumos.destroy', $tipo)));
        $this->assertSoftDeleted($tipo);
        $this->assertExito($this->actingAs($admin)->patch(route('tipo-insumos.restore', $tipo->id)));
        $this->assertNotSoftDeleted($tipo);
    }

    public function test_un_tipo_repetido_se_rechaza(): void
    {
        $this->actingAs($this->admin())->postJson(route('tipo-insumos.store'), ['nombre' => 'Hilo'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');
    }
}
