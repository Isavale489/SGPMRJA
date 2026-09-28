<?php

namespace Tests\Feature\Flujos;

use App\Models\Persona;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Proveedores: alta (natural/jurídico), edición, inhabilitar/restaurar.
 * Verifican efectos en BD: escritos ANTES de migrar el módulo a Inertia y
 * deben seguir en verde después (piloto del paso 3).
 *
 * El alta rápida desde Compras (jQuery, espera JSON) usa el mismo endpoint:
 * su contrato se prueba aparte y no puede romperse al migrar.
 */
class ProveedorFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function juridico(array $extra = []): array
    {
        return array_merge([
            'tipo_proveedor' => 'juridico',
            'rif' => 'J-40123456',
            'razon_social' => 'Textiles del Llano C.A.',
            'direccion' => 'Av. Libertador, Galpón 4',
            'email' => 'ventas@textilesllano.com',
            'telefonos' => [['numero' => '0255-6211234', 'tipo' => 'trabajo', 'es_principal' => 1]],
            'contacto' => 'Carmen Silva',
            'telefono_contacto' => '0424-5551234',
            'estado_territorial' => 'Portuguesa',
            'ciudad' => 'Araure',
        ], $extra);
    }

    private function natural(array $extra = []): array
    {
        return array_merge([
            'tipo_proveedor' => 'natural',
            'tipo_documento' => 'V-',
            'documento_identidad' => '14567890',
            'nombre' => 'Luis',
            'apellido' => 'Mendoza',
            'direccion' => 'Calle 5, Acarigua',
            'email' => 'luis.mendoza@correo.com',
            'telefonos' => [['numero' => '0414-5550000', 'tipo' => 'movil', 'es_principal' => 1]],
            'estado_territorial' => 'Portuguesa',
            'ciudad' => 'Araure',
        ], $extra);
    }

    public function test_alta_de_proveedor_juridico(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('proveedores.store'), $this->juridico()));

        $p = Proveedor::with('persona.telefonos', 'persona.direccion')->sole();
        $this->assertSame('juridico', $p->tipo_proveedor);
        $this->assertSame('J-', $p->persona->tipo_documento);
        $this->assertSame('40123456', $p->persona->documento_identidad);
        $this->assertSame('Textiles del Llano C.A.', $p->persona->nombre);
        $this->assertSame('Carmen Silva', $p->contacto);
        $this->assertSame('0424-5551234', $p->telefono_contacto);
        $this->assertSame('0255-6211234', $p->persona->telefonos->sole()->numero);
        $this->assertSame('Portuguesa', $p->persona->direccion->estado);
        $this->assertSame('Araure', $p->persona->direccion->ciudad);
    }

    public function test_alta_de_proveedor_natural_une_nombre_y_apellido(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('proveedores.store'), $this->natural()));

        $p = Proveedor::with('persona')->sole();
        $this->assertSame('natural', $p->tipo_proveedor);
        $this->assertSame('Luis Mendoza', $p->persona->nombre);
        $this->assertSame('14567890', $p->persona->documento_identidad);
    }

    public function test_no_se_repite_el_rif(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('proveedores.store'), $this->juridico());

        $this->actingAs($admin)
            ->postJson(route('proveedores.store'), $this->juridico(['email' => 'otro@correo.com']))
            ->assertStatus(422)->assertJsonValidationErrors('rif');

        $this->assertSame(1, Proveedor::count());
    }

    public function test_telefono_con_formato_invalido_se_rechaza(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('proveedores.store'), $this->juridico(['telefonos' => [['numero' => '4245551234', 'tipo' => 'movil', 'es_principal' => 1]]]))
            ->assertStatus(422)->assertJsonValidationErrors('telefonos.0.numero');

        $this->assertSame(0, Proveedor::count());
    }

    public function test_el_alta_rapida_desde_compras_recibe_json_con_el_proveedor(): void
    {
        // Contrato de resources/views/admin/compras/scripts/create.blade.php (jQuery).
        $resp = $this->actingAs($this->admin())
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('proveedores.store'), $this->juridico());

        $resp->assertOk()->assertJsonStructure(['success', 'proveedor' => ['id', 'nombre', 'doc', 'tel', 'email', 'tipo']]);
        $this->assertSame(Proveedor::sole()->id, $resp->json('proveedor.id'));
    }

    public function test_editar_un_proveedor_natural_conserva_su_nombre(): void
    {
        // Regresión: `nombre` consolida nombre + apellido desde jun-2026; la
        // edición exigía un apellido y lo volvía a concatenar ("Luis Mendoza Mendoza").
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('proveedores.store'), $this->natural());
        $p = Proveedor::sole();

        $this->assertExito($this->actingAs($admin)->put(route('proveedores.update', $p), [
            ...$this->natural(['direccion' => 'Calle 9, Acarigua']),
            'nombre' => 'Luis Mendoza',
            'apellido' => null,
        ]));

        $p->refresh()->load('persona.direccion');
        $this->assertSame('Luis Mendoza', $p->persona->nombre);
        $this->assertSame('Calle 9, Acarigua', $p->persona->direccion->direccion);
    }

    public function test_editar_un_proveedor_juridico_no_cambia_su_rif(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('proveedores.store'), $this->juridico());
        $p = Proveedor::sole();

        $this->assertExito($this->actingAs($admin)->put(route('proveedores.update', $p),
            $this->juridico(['rif' => 'J-99999999', 'razon_social' => 'Textiles del Llano, C.A.'])));

        $persona = Persona::find($p->persona_id);
        $this->assertSame('Textiles del Llano, C.A.', $persona->nombre);
        $this->assertSame('40123456', $persona->documento_identidad); // documento inmutable
    }

    public function test_inhabilitar_y_restaurar(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('proveedores.store'), $this->juridico());
        $p = Proveedor::sole();

        $this->assertExito($this->actingAs($admin)->delete(route('proveedores.destroy', $p)));
        $this->assertSoftDeleted($p);

        $this->assertExito($this->actingAs($admin)->post(route('proveedores.restore', $p->id)));
        $this->assertNotSoftDeleted($p);
    }

    public function test_sin_permiso_de_gestion_no_se_crea(): void
    {
        $this->actingAs($this->usuarioSinPermisos())
            ->postJson(route('proveedores.store'), $this->juridico())
            ->assertForbidden();

        $this->assertSame(0, Proveedor::count());
    }
}
