<?php

namespace Tests\Feature\Flujos;

use App\Models\Configuracion;
use App\Models\Impuesto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Panel de configuración: parámetros del registry (config/parametros.php) con
 * overrides en la tabla `configuracion`, y catálogo de impuestos con el IVA
 * protegido. Escritos ANTES de migrar a Inertia.
 */
class ConfiguracionFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    public function test_guardar_un_parametro_lo_aplica_y_restablecer_vuelve_al_defecto(): void
    {
        $admin = $this->admin();

        $this->assertExito($this->actingAs($admin)->putJson(route('configuracion.update', 'cotizaciones'), [
            'valores' => ['cotizaciones.dias_vigencia' => 30, 'cotizaciones.max_bordados_producto' => 4],
        ]));
        $this->assertEquals(30, parametro('cotizaciones.dias_vigencia'));
        $this->assertSame($admin->id, (int) Configuracion::where('clave', 'cotizaciones.dias_vigencia')->value('updated_by_id'));

        $this->assertExito($this->actingAs($admin)->deleteJson(route('configuracion.reset', ['cotizaciones', 'cotizaciones.dias_vigencia'])));
        $this->assertEquals(15, parametro('cotizaciones.dias_vigencia'));
        $this->assertEquals(4, parametro('cotizaciones.max_bordados_producto'));
    }

    public function test_las_reglas_del_registry_se_validan_y_no_se_guarda_nada(): void
    {
        $this->actingAs($this->admin())->putJson(route('configuracion.update', 'pedidos'), [
            'valores' => ['pedidos.abono_minimo' => 150],
        ])->assertStatus(422)->assertJsonStructure(['errors' => ['pedidos.abono_minimo']]);

        $this->assertSame(0, Configuracion::count());
    }

    public function test_no_se_aceptan_claves_de_otro_modulo_ni_modulos_inexistentes(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->putJson(route('configuracion.update', 'pedidos'), [
            'valores' => ['cotizaciones.dias_vigencia' => 30],
        ])->assertStatus(422);
        $this->actingAs($admin)->putJson(route('configuracion.update', 'pedidos'), ['valores' => []])->assertStatus(422);
        $this->actingAs($admin)->putJson(route('configuracion.update', 'inventado'), ['valores' => ['x' => 1]])->assertNotFound();
        $this->actingAs($admin)->deleteJson(route('configuracion.reset', ['pedidos', 'cotizaciones.dias_vigencia']))->assertNotFound();

        $this->assertSame(0, Configuracion::count());
    }

    public function test_crear_editar_y_eliminar_impuestos(): void
    {
        $admin = $this->admin();
        $datos = ['codigo' => 'igtf', 'nombre' => 'IGTF', 'porcentaje' => 3, 'estado' => 'activo'];

        $this->assertExito($this->actingAs($admin)->postJson(route('impuestos.store'), $datos));
        $igtf = Impuesto::where('codigo', 'IGTF')->sole(); // el código se guarda en mayúsculas

        $this->actingAs($admin)->postJson(route('impuestos.store'), $datos)
            ->assertStatus(422)->assertJsonValidationErrors('codigo');

        $this->assertExito($this->actingAs($admin)->putJson(route('impuestos.update', $igtf), [...$datos, 'porcentaje' => 2]));
        $this->assertEquals(2, (float) $igtf->fresh()->porcentaje);

        $this->assertExito($this->actingAs($admin)->deleteJson(route('impuestos.destroy', $igtf)));
        $this->assertSoftDeleted($igtf);

        // Reusar el código de un impuesto borrado restaura esa fila (el UNIQUE incluye borrados).
        $this->assertExito($this->actingAs($admin)->postJson(route('impuestos.store'), [...$datos, 'porcentaje' => 1]));
        $this->assertSame($igtf->id, Impuesto::where('codigo', 'IGTF')->sole()->id);
    }

    public function test_el_iva_no_se_elimina_ni_se_desactiva_ni_cambia_de_codigo(): void
    {
        $admin = $this->admin();
        $iva = Impuesto::firstOrCreate(['codigo' => Impuesto::CODIGO_IVA], ['nombre' => 'IVA', 'porcentaje' => 16, 'estado' => 'activo']);

        $this->actingAs($admin)->deleteJson(route('impuestos.destroy', $iva))->assertStatus(422);
        $this->assertExito($this->actingAs($admin)->putJson(route('impuestos.update', $iva), [
            'codigo' => 'OTRO', 'nombre' => 'IVA general', 'porcentaje' => 8, 'estado' => 'inactivo',
        ]));

        $iva->refresh();
        $this->assertSame(Impuesto::CODIGO_IVA, $iva->codigo);
        $this->assertSame('activo', $iva->estado);
        $this->assertEquals(8, Impuesto::tasaIva());
    }

    public function test_sin_permiso_no_se_configura(): void
    {
        $u = $this->usuarioSinPermisos();
        $this->actingAs($u)->putJson(route('configuracion.update', 'cotizaciones'), ['valores' => ['cotizaciones.dias_vigencia' => 30]])->assertForbidden();
        $this->actingAs($u)->postJson(route('impuestos.store'), ['codigo' => 'X', 'nombre' => 'X', 'porcentaje' => 1, 'estado' => 'activo'])->assertForbidden();
        $this->assertSame(0, Configuracion::count());
    }
}
