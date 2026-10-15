<?php

namespace Tests\Feature\Flujos;

use App\Models\Cliente;
use App\Models\Direccion;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Clientes: alta (natural, jurídico, gubernamental), persona compartida con
 * otro rol, edición, inhabilitar/restaurar. Escritos ANTES de migrar a Inertia.
 *
 * Contratos vivos (jQuery): Cotizaciones y Pedidos crean clientes con
 * POST /clientes (esperan `cliente_id`), buscan con clientes.search, revisan el
 * documento con clientes.check-documento y reutilizan personas con
 * clientes.from-persona.
 */
class ClienteFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function payload(array $extra = []): array
    {
        return array_merge([
            'documento' => 'V-14567890',
            'tipo_cliente' => 'natural',
            'nombre' => 'María',
            'apellido' => 'González',
            'email' => 'maria.gonzalez@correo.com',
            'telefonos' => [['numero' => '0414-5550000', 'tipo' => 'movil', 'es_principal' => 1]],
            'direccion' => 'Calle 5, Acarigua',
            'estado_territorial' => 'Portuguesa',
            'ciudad' => 'Araure',
        ], $extra);
    }

    public function test_alta_de_cliente_natural_une_nombre_y_apellido(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('clientes.store'), $this->payload()));

        $c = Cliente::with('persona.telefonos', 'persona.direccion')->sole();
        $this->assertSame('natural', $c->tipo_cliente);
        $this->assertSame('María González', $c->persona->nombre);
        $this->assertSame('V-', $c->persona->tipo_documento);
        $this->assertSame('14567890', $c->persona->documento_identidad);
        $this->assertSame(['0414-5550000'], $c->persona->telefonos->pluck('numero')->all());
        $this->assertSame('Portuguesa', $c->persona->direccion->estado);
        $this->assertSame('Araure', $c->persona->direccion->ciudad);
    }

    public function test_alta_de_cliente_juridico(): void
    {
        $this->assertExito($this->actingAs($this->admin())->post(route('clientes.store'), $this->payload([
            'documento' => 'J-40123456', 'tipo_cliente' => 'juridico', 'nombre' => 'Uniformes Llaneros C.A.', 'apellido' => null,
        ])));

        $c = Cliente::with('persona')->sole();
        $this->assertSame('juridico', $c->tipo_cliente);
        $this->assertSame('Uniformes Llaneros C.A.', $c->persona->nombre);
        $this->assertSame('J-', $c->persona->tipo_documento);
    }

    public function test_alta_de_cliente_gubernamental(): void
    {
        // Regla del sistema: el prefijo G- define un cliente gubernamental (la UI lo ofrece).
        $this->assertExito($this->actingAs($this->admin())->post(route('clientes.store'), $this->payload([
            'documento' => 'G-20000123', 'tipo_cliente' => 'gubernamental', 'nombre' => 'Alcaldía de Páez', 'apellido' => null,
        ])));

        $this->assertSame('gubernamental', Cliente::sole()->tipo_cliente);
    }

    public function test_un_documento_ya_registrado_como_cliente_se_rechaza(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('clientes.store'), $this->payload());

        $this->actingAs($admin)->postJson(route('clientes.store'), $this->payload(['email' => 'otra@correo.com']))
            ->assertStatus(422)->assertJsonValidationErrors('documento');
        $this->assertSame(1, Cliente::count());
    }

    public function test_un_correo_de_otra_persona_se_rechaza(): void
    {
        Persona::create(['nombre' => 'Otra', 'tipo_documento' => 'V-', 'documento_identidad' => '9999999', 'email' => 'maria.gonzalez@correo.com']);

        $this->actingAs($this->admin())->postJson(route('clientes.store'), $this->payload())
            ->assertStatus(422)->assertJsonValidationErrors('email');
        $this->assertSame(0, Cliente::count());
    }

    public function test_telefono_con_formato_invalido_se_rechaza(): void
    {
        $this->actingAs($this->admin())->postJson(route('clientes.store'), $this->payload([
            'telefonos' => [['numero' => '4145550000', 'tipo' => 'movil', 'es_principal' => 1]],
        ]))->assertStatus(422)->assertJsonValidationErrors('telefonos.0.numero');
    }

    public function test_una_persona_de_otro_rol_se_reutiliza_sin_duplicar_su_direccion(): void
    {
        $empleado = $this->empleado('Ana Pérez');
        $persona = $empleado->persona;
        Direccion::create(['persona_id' => $persona->id, 'direccion' => 'Barrio Sucre', ...Direccion::resolverUbicacion('Portuguesa', 'Páez')]);

        $this->assertExito($this->actingAs($this->admin())->post(route('clientes.store'), $this->payload([
            'documento' => 'V-'.$persona->documento_identidad, 'nombre' => 'Ana Pérez', 'apellido' => null, 'email' => null,
            'direccion' => 'Barrio Sucre, calle 3', 'estado_territorial' => 'Portuguesa', 'ciudad' => 'Páez',
        ])));

        $this->assertSame(1, Persona::count());
        $this->assertSame($persona->id, Cliente::sole()->persona_id);
        // La dirección es 1:1 (Persona::direccion hasOne): se actualiza, no se agrega otra.
        $this->assertSame(1, Direccion::where('persona_id', $persona->id)->count());
        $this->assertSame('Barrio Sucre, calle 3', $persona->fresh()->direccion->direccion);
    }

    public function test_editar_conserva_el_documento_y_no_duplica_el_apellido(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('clientes.store'), $this->payload());
        $c = Cliente::sole();

        $this->assertExito($this->actingAs($admin)->put(route('clientes.update', $c->id), $this->payload([
            'documento' => 'V-99999999', 'nombre' => 'María González', 'apellido' => '',
            'telefonos' => [
                ['numero' => '0424-1112233', 'tipo' => 'movil', 'es_principal' => 1],
                ['numero' => '0255-6210000', 'tipo' => 'casa', 'es_principal' => 0],
            ],
            'direccion' => 'Av. Libertador', 'ciudad' => 'Guanare', 'estado_territorial' => 'Portuguesa',
        ])));

        $persona = $c->fresh()->persona;
        $this->assertSame('María González', $persona->nombre);
        $this->assertSame('14567890', $persona->documento_identidad);
        $this->assertSame(['0424-1112233', '0255-6210000'], $persona->telefonos()->pluck('numero')->all());
        $this->assertSame('Av. Libertador', $persona->direccion->direccion);
        $this->assertSame(1, $persona->direcciones()->count());
    }

    public function test_inhabilitar_y_restaurar(): void
    {
        $admin = $this->admin();
        $c = $this->cliente();

        $this->assertExito($this->actingAs($admin)->delete(route('clientes.destroy', $c->id)));
        $this->assertSoftDeleted($c);
        $this->assertExito($this->actingAs($admin)->post(route('clientes.restore', $c->id)));
        $this->assertNotSoftDeleted($c);
    }

    public function test_sin_permiso_de_gestion_no_se_crea(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->post(route('clientes.store'), $this->payload());

        $this->assertSame(0, Cliente::count());
    }

    public function test_el_alta_rapida_de_cotizaciones_recibe_json_con_el_cliente(): void
    {
        $resp = $this->actingAs($this->admin())->post(route('clientes.store'), $this->payload(), ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->assertOk()->assertJsonStructure(['message', 'cliente_id']);

        $this->assertSame(Cliente::sole()->id, $resp->json('cliente_id'));
    }

    public function test_cotizaciones_busca_clientes_activos_por_json(): void
    {
        $c = $this->cliente();

        $this->actingAs($this->admin())->getJson(route('clientes.search', ['q' => 'maría']))
            ->assertOk()
            ->assertJsonPath('0.id', $c->id)
            ->assertJsonPath('0.nombre', 'María González')
            ->assertJsonStructure([['id', 'nombre', 'apellido', 'email', 'telefono', 'documento']]);
    }

    public function test_check_documento_avisa_duplicado_y_persona_de_otro_rol(): void
    {
        $admin = $this->admin();
        $cliente = $this->cliente();
        $empleado = $this->empleado('Ana Pérez');

        $this->actingAs($admin)->getJson(route('clientes.check-documento', ['numero' => $cliente->persona->documento_identidad]))
            ->assertOk()->assertJson(['exists' => true, 'other_role' => null]);
        $this->actingAs($admin)->getJson(route('clientes.check-documento', ['numero' => $empleado->persona->documento_identidad]))
            ->assertOk()->assertJson(['exists' => false, 'other_role' => 'empleado', 'persona' => ['nombre' => 'Ana Pérez', 'tipo_documento' => 'V-']]);
    }

    public function test_from_persona_es_idempotente(): void
    {
        $admin = $this->admin();
        $persona = $this->empleado('Ana Pérez')->persona;

        $this->actingAs($admin)->postJson(route('clientes.from-persona', $persona->id))
            ->assertOk()->assertJson(['success' => true, 'reused' => false]);
        $this->actingAs($admin)->postJson(route('clientes.from-persona', $persona->id))
            ->assertOk()->assertJson(['success' => true, 'reused' => true]);

        $this->assertSame('natural', Cliente::sole()->tipo_cliente);
    }
}
