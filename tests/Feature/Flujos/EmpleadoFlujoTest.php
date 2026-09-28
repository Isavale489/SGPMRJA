<?php

namespace Tests\Feature\Flujos;

use App\Models\Cargo;
use App\Models\Departamento;
use App\Models\Direccion;
use App\Models\Empleado;
use App\Models\Persona;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Empleados: alta con código autogenerado, cargo dentro de su departamento,
 * persona compartida con otro rol, edición, inhabilitar/restaurar. Escritos
 * ANTES de migrar a Inertia.
 */
class EmpleadoFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private Departamento $produccion;
    private Cargo $costurera;

    protected function setUp(): void
    {
        parent::setUp();
        $this->produccion = Departamento::create(['nombre' => 'Producción']);
        $this->costurera = Cargo::create(['nombre' => 'Costurera', 'departamento_id' => $this->produccion->id]);
    }

    private function payload(array $extra = []): array
    {
        return array_merge([
            'tipo_documento' => 'V-',
            'documento_identidad' => '18765432',
            'nombre' => 'Rosa',
            'apellido' => 'Linares',
            'email' => 'rosa.linares@correo.com',
            'telefonos' => [['numero' => '0414-5550000', 'tipo' => 'movil', 'es_principal' => 1]],
            'direccion' => 'Calle 3, Acarigua',
            'estado_geografico' => 'Portuguesa',
            'ciudad' => 'Páez',
            'fecha_nacimiento' => '1990-05-10',
            'genero' => 'F',
            'fecha_ingreso' => now()->subMonth()->toDateString(),
            'departamento_id' => $this->produccion->id,
            'cargo_id' => $this->costurera->id,
        ], $extra);
    }

    public function test_alta_genera_el_codigo_y_une_nombre_y_apellido(): void
    {
        Empleado::forceCreate(['persona_id' => Persona::create(['nombre' => 'Viejo', 'tipo_documento' => 'V-', 'documento_identidad' => '1111111'])->id,
            'codigo_empleado' => 'EMP-007', 'fecha_ingreso' => '2020-01-01'])->delete(); // el inhabilitado también cuenta

        $this->assertExito($this->actingAs($this->admin())->post(route('empleados.store'), $this->payload()));

        $e = Empleado::with('persona.direccion')->where('codigo_empleado', '!=', 'EMP-007')->sole();
        $this->assertSame('EMP-008', $e->codigo_empleado);
        $this->assertSame('Rosa Linares', $e->persona->nombre);
        $this->assertSame($this->costurera->id, $e->cargo_id);
        $this->assertSame('F', $e->genero);
        $this->assertSame('Páez', $e->persona->direccion->ciudad);
    }

    public function test_el_cargo_debe_pertenecer_al_departamento(): void
    {
        $ventas = Departamento::create(['nombre' => 'Ventas']);

        $this->actingAs($this->admin())->postJson(route('empleados.store'), $this->payload(['departamento_id' => $ventas->id]))
            ->assertStatus(422)->assertJsonValidationErrors('cargo_id');
    }

    public function test_menor_de_edad_o_ingreso_futuro_se_rechaza(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->postJson(route('empleados.store'), $this->payload(['fecha_nacimiento' => now()->subYears(17)->toDateString()]))
            ->assertStatus(422)->assertJsonValidationErrors('fecha_nacimiento');
        $this->actingAs($admin)->postJson(route('empleados.store'), $this->payload(['fecha_ingreso' => now()->addDay()->toDateString()]))
            ->assertStatus(422)->assertJsonValidationErrors('fecha_ingreso');
    }

    public function test_un_documento_que_ya_es_empleado_se_rechaza(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('empleados.store'), $this->payload());

        $this->actingAs($admin)->postJson(route('empleados.store'), $this->payload(['email' => 'otra@correo.com']))
            ->assertStatus(422)->assertJsonValidationErrors('documento_identidad');
        $this->assertSame(1, Empleado::count());
    }

    public function test_una_persona_cliente_se_reutiliza_sin_duplicar_su_direccion(): void
    {
        $persona = $this->cliente()->persona; // "María González"
        Direccion::create(['persona_id' => $persona->id, 'direccion' => 'Barrio Sucre', ...Direccion::resolverUbicacion('Portuguesa', 'Páez')]);

        $this->assertExito($this->actingAs($this->admin())->post(route('empleados.store'), $this->payload([
            'documento_identidad' => $persona->documento_identidad, 'nombre' => 'María González', 'apellido' => null, 'email' => null,
            'direccion' => 'Barrio Sucre, calle 3',
        ])));

        $this->assertSame(1, Persona::count());
        $this->assertSame($persona->id, Empleado::sole()->persona_id);
        // La dirección es 1:1 (Persona::direccion hasOne): se actualiza, no se agrega otra.
        $this->assertSame(1, Direccion::where('persona_id', $persona->id)->count());
        $this->assertSame('Barrio Sucre, calle 3', $persona->fresh()->direccion->direccion);
    }

    public function test_editar_conserva_documento_y_codigo_sin_duplicar_el_apellido(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('empleados.store'), $this->payload());
        $e = Empleado::sole();
        $operador = Cargo::create(['nombre' => 'Operador', 'departamento_id' => $this->produccion->id]);

        // `persona.nombre` ya trae el nombre completo; el apellido llega vacío.
        $this->assertExito($this->actingAs($admin)->put(route('empleados.update', $e->id), $this->payload([
            'documento_identidad' => '99999999', 'nombre' => 'Rosa Linares', 'apellido' => '',
            'codigo_empleado' => $e->codigo_empleado, 'cargo_id' => $operador->id,
        ])));

        $e->refresh();
        $this->assertSame('Rosa Linares', $e->persona->nombre);
        $this->assertSame('18765432', $e->persona->documento_identidad);
        $this->assertSame('EMP-001', $e->codigo_empleado);
        $this->assertSame($operador->id, $e->cargo_id);
    }

    public function test_inhabilitar_y_restaurar(): void
    {
        $admin = $this->admin();
        $e = $this->empleado();

        $this->assertExito($this->actingAs($admin)->delete(route('empleados.destroy', $e->id)));
        $this->assertSoftDeleted($e);
        $this->assertExito($this->actingAs($admin)->post(route('empleados.restore', $e->id)));
        $this->assertNotSoftDeleted($e);
    }

    public function test_sin_permiso_de_gestion_no_se_crea(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->post(route('empleados.store'), $this->payload());

        $this->assertSame(0, Empleado::count());
    }

    public function test_check_documento_avisa_duplicado_y_persona_cliente(): void
    {
        $admin = $this->admin();
        $empleado = $this->empleado('Ana Pérez');
        $cliente = $this->cliente();

        $this->actingAs($admin)->getJson(route('empleados.check-documento', ['numero' => $empleado->persona->documento_identidad]))
            ->assertOk()->assertJson(['exists' => true]);
        $this->actingAs($admin)->getJson(route('empleados.check-documento', ['numero' => $cliente->persona->documento_identidad]))
            ->assertOk()->assertJson(['exists' => false, 'other_role' => 'cliente', 'persona' => ['nombre' => 'María González']]);
    }
}
