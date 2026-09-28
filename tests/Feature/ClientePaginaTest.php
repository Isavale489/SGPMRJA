<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Página Inertia de Clientes: contrato de la fila con tipos.ts, búsqueda,
 * filtros, historial y respuesta de las mutaciones para Inertia.
 */
class ClientePaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function crear(string $nombre, string $prefijo, string $numero, string $tipo = 'natural', ?string $email = null): Cliente
    {
        $persona = Persona::create(['nombre' => $nombre, 'tipo_documento' => $prefijo, 'documento_identidad' => $numero, 'email' => $email]);

        return Cliente::forceCreate(['persona_id' => $persona->id, 'tipo_cliente' => $tipo, 'estatus' => 1]);
    }

    /** Claves de `export interface ClienteFila { ... }` en tipos.ts. */
    private function clavesTs(): array
    {
        $ts = file_get_contents(resource_path('js/pages/Clientes/tipos.ts'));
        preg_match('/export interface ClienteFila \{(.*?)\n\}/s', $ts, $m);
        preg_match_all('/^\s+(\w+)\??:/m', $m[1] ?? '', $claves);

        return $claves[1];
    }

    public function test_cada_fila_coincide_con_su_interfaz_de_typescript(): void
    {
        $this->crear('María González', 'V-', '14567890');
        $claves = $this->clavesTs();
        $this->assertNotEmpty($claves, 'No se pudo leer ClienteFila de tipos.ts');

        $this->actingAs($this->admin())->get(route('clientes.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Clientes/Index')
                ->has('clientes.data', 1, fn (Assert $fila) => $fila->hasAll($claves)) // sin ->etc(): una clave extra también falla
                ->has('estados.Portuguesa')
                ->has('urls.checkDocumento'));
    }

    public function test_busca_por_nombre_documento_o_correo(): void
    {
        $this->crear('María González', 'V-', '14567890');
        $this->crear('Uniformes Llaneros C.A.', 'J-', '40999888', 'juridico', 'ventas@llaneros.com');
        $admin = $this->admin();

        foreach (['gonzál' => 'María González', 'J-4099' => 'Uniformes Llaneros C.A.', 'ventas@' => 'Uniformes Llaneros C.A.'] as $termino => $esperado) {
            $this->actingAs($admin)->get(route('clientes.index', ['buscar' => $termino]))
                ->assertInertia(fn (Assert $page) => $page
                    ->has('clientes.data', 1)
                    ->where('clientes.data.0.nombre', $esperado)
                    ->where('filtros.buscar', $termino));
        }
    }

    public function test_filtra_por_tipo_y_ordena_por_nombre(): void
    {
        $this->crear('Zamora Uniformes', 'J-', '40000001', 'juridico');
        $this->crear('Ana Rojas', 'V-', '12000001');
        $this->crear('Alcaldía de Páez', 'G-', '20000001', 'gubernamental');
        $this->crear('Bordados Acarigua', 'J-', '40000002', 'juridico');
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('clientes.index', ['tipo' => 'juridico', 'orden' => 'nombre_asc']))
            ->assertInertia(fn (Assert $page) => $page
                ->has('clientes.data', 2)
                ->where('clientes.data.0.nombre', 'Bordados Acarigua')
                ->where('clientes.data.1.nombre', 'Zamora Uniformes'));
        $this->actingAs($admin)->get(route('clientes.index', ['tipo' => 'gubernamental']))
            ->assertInertia(fn (Assert $page) => $page->has('clientes.data', 1)->where('clientes.data.0.tipo', 'gubernamental'));
    }

    public function test_el_historial_muestra_solo_inhabilitados(): void
    {
        $this->crear('Activo', 'V-', '10000001');
        $this->crear('Inhabilitado', 'V-', '10000002')->delete();

        $this->actingAs($this->admin())->get(route('clientes.index', ['historial' => 1]))
            ->assertInertia(fn (Assert $page) => $page
                ->has('clientes.data', 1)
                ->where('clientes.data.0.nombre', 'Inhabilitado')
                ->where('clientes.data.0.inhabilitado', true));
    }

    public function test_la_fila_avisa_si_la_persona_tiene_otro_rol(): void
    {
        $c = $this->crear('Ana Pérez', 'V-', '15000001');
        Empleado::forceCreate(['persona_id' => $c->persona_id, 'codigo_empleado' => 'EMP-001', 'fecha_ingreso' => now()->toDateString()]);

        $this->actingAs($this->admin())->get(route('clientes.index'))
            ->assertInertia(fn (Assert $page) => $page->where('clientes.data.0.otros_roles', ['empleado']));
    }

    public function test_una_mutacion_desde_inertia_redirige_con_mensaje(): void
    {
        $c = $this->crear('María González', 'V-', '14567890');

        $this->actingAs($this->admin())
            ->from(route('clientes.index'))
            ->withHeaders(['X-Inertia' => 'true'])
            ->delete(route('clientes.destroy', $c->id))
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHas('success', 'Cliente inhabilitado exitosamente.');
    }

    public function test_un_error_de_validacion_vuelve_al_formulario(): void
    {
        $this->crear('María González', 'V-', '14567890');

        $this->actingAs($this->admin())
            ->from(route('clientes.index'))
            ->withHeaders(['X-Inertia' => 'true'])
            ->post(route('clientes.store'), [
                'documento' => 'V-14567890', 'tipo_cliente' => 'natural', 'nombre' => 'Otra María',
                'telefonos' => [['numero' => '0414-5550000', 'tipo' => 'movil', 'es_principal' => 1]],
            ])
            ->assertRedirect(route('clientes.index'))
            ->assertSessionHasErrors(['documento' => 'Este documento ya está registrado como cliente.']);
    }
}
