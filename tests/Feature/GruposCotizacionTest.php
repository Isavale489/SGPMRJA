<?php

namespace Tests\Feature;

use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Support\GruposCotizacion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Los grupos de una cotización llevan el precio de catálogo de la variante
 * materializada: al reabrir «Editar producto», «Precio base» y «Restaurar» lo
 * usan (el precio del grupo puede ser el negociado con el cliente).
 */
class GruposCotizacionTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    public function test_la_variante_materializada_trae_su_precio_de_catalogo_y_la_dinamica_no(): void
    {
        $tipo = TipoProducto::firstOrCreate(['prefijo' => 'CHE'], ['nombre' => 'Chemise']);
        $producto = Producto::create(['tipo_producto_id' => $tipo->id, 'codigo' => 'CHE-001', 'precio_base' => 20, 'estado' => true]);

        $payload = $this->payloadCotizacion($this->cliente()->id);
        $materializada = array_merge($payload['productos'][0], ['producto_id' => $producto->id]);
        unset($materializada['tipo_producto_id']);
        $payload['productos'][] = $materializada;

        $this->assertExito($this->actingAs($this->admin())->postJson(route('cotizaciones.store'), $payload));

        $grupos = collect(GruposCotizacion::desde(GruposCotizacion::cargar(Cotizacion::latest('id')->firstOrFail())->productos));
        $this->assertCount(2, $grupos);
        $this->assertSame(20.0, $grupos->firstWhere('producto_id', $producto->id)['precio_catalogo']);
        $this->assertNull($grupos->firstWhere('producto_id', null)['precio_catalogo']);
    }
}
