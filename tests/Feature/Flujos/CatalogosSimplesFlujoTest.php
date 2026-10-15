<?php

namespace Tests\Feature\Flujos;

use App\Models\Cargo;
use App\Models\Color;
use App\Models\Departamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Catálogos simples (Departamentos, Cargos, Colores): alta, edición, reglas
 * de inhabilitación y restauración. Escritos ANTES de migrar a Inertia y
 * deben seguir en verde después.
 *
 * Contratos JSON de clientes Blade que siguen vivos:
 *   - Empleados crea departamentos/cargos al vuelo y lista departamentos (jQuery).
 *   - Cotizaciones crea colores al vuelo y consume colores.data (jQuery).
 */
class CatalogosSimplesFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function ajax()
    {
        return $this->actingAs($this->admin())->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json']);
    }

    // ── Departamentos ────────────────────────────────────────────────

    public function test_departamento_alta_edicion_y_nombre_unico(): void
    {
        $admin = $this->admin();
        $this->assertExito($this->actingAs($admin)->post(route('departamentos.store'), ['nombre' => '  Confección ']));
        $d = Departamento::sole();
        $this->assertSame('Confección', $d->nombre);

        $this->actingAs($admin)->postJson(route('departamentos.store'), ['nombre' => 'Confección'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');

        $this->assertExito($this->actingAs($admin)->put(route('departamentos.update', $d), ['nombre' => 'Corte y Confección']));
        $this->assertSame('Corte y Confección', $d->fresh()->nombre);
    }

    public function test_no_se_inhabilita_un_departamento_con_cargos(): void
    {
        $d = Departamento::create(['nombre' => 'Producción']);
        Cargo::create(['nombre' => 'Costurera', 'departamento_id' => $d->id]);

        $this->ajax()->delete(route('departamentos.destroy', $d))->assertStatus(422)
            ->assertJsonPath('message', 'No se puede inhabilitar: el departamento tiene cargos asociados.');

        $this->assertNotSoftDeleted($d);
    }

    public function test_departamento_inhabilitar_y_restaurar(): void
    {
        $admin = $this->admin();
        $d = Departamento::create(['nombre' => 'Ventas']);

        $this->assertExito($this->actingAs($admin)->delete(route('departamentos.destroy', $d)));
        $this->assertSoftDeleted($d);
        $this->assertExito($this->actingAs($admin)->patch(route('departamentos.restore', $d->id)));
        $this->assertNotSoftDeleted($d);
    }

    public function test_empleados_crea_y_lista_departamentos_por_json(): void
    {
        $this->ajax()->post(route('departamentos.store'), ['nombre' => 'Almacén'])
            ->assertOk()->assertJsonPath('departamento.nombre', 'Almacén');

        $this->ajax()->get(route('departamentos.index'))
            ->assertOk()->assertJsonCount(1)->assertJsonPath('0.nombre', 'Almacén');
    }

    // ── Cargos ───────────────────────────────────────────────────────

    public function test_cargo_nombre_unico_por_departamento(): void
    {
        $admin = $this->admin();
        $prod = Departamento::create(['nombre' => 'Producción']);
        $ventas = Departamento::create(['nombre' => 'Ventas']);

        $this->assertExito($this->actingAs($admin)->post(route('cargos.store'), ['nombre' => 'Supervisor', 'departamento_id' => $prod->id]));
        // El mismo nombre en otro departamento sí se permite.
        $this->assertExito($this->actingAs($admin)->post(route('cargos.store'), ['nombre' => 'Supervisor', 'departamento_id' => $ventas->id]));

        $this->ajax()->post(route('cargos.store'), ['nombre' => 'supervisor', 'departamento_id' => $prod->id])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Ya existe un cargo con este nombre en el departamento seleccionado.');

        $this->assertSame(2, Cargo::count());
    }

    public function test_no_se_inhabilita_un_cargo_con_empleados(): void
    {
        $d = Departamento::create(['nombre' => 'Producción']);
        $c = Cargo::create(['nombre' => 'Costurera', 'departamento_id' => $d->id]);
        $this->empleado()->update(['cargo_id' => $c->id, 'departamento_id' => $d->id]);

        $this->ajax()->delete(route('cargos.destroy', $c))->assertStatus(422)
            ->assertJsonPath('message', 'No se puede inhabilitar: el cargo tiene empleados asociados.');

        $this->assertNotSoftDeleted($c);
    }

    public function test_cargo_edicion_inhabilitar_y_restaurar(): void
    {
        $admin = $this->admin();
        $d = Departamento::create(['nombre' => 'Producción']);
        $c = Cargo::create(['nombre' => 'Cortador', 'departamento_id' => $d->id]);

        $this->assertExito($this->actingAs($admin)->put(route('cargos.update', $c), ['nombre' => 'Cortador Jefe', 'departamento_id' => $d->id]));
        $this->assertSame('Cortador Jefe', $c->fresh()->nombre);

        $this->assertExito($this->actingAs($admin)->delete(route('cargos.destroy', $c)));
        $this->assertSoftDeleted($c);
        $this->assertExito($this->actingAs($admin)->patch(route('cargos.restore', $c->id)));
        $this->assertNotSoftDeleted($c);
    }

    public function test_empleados_crea_cargos_por_json(): void
    {
        $d = Departamento::create(['nombre' => 'Producción']);

        $this->ajax()->post(route('cargos.store'), ['nombre' => 'Bordador', 'departamento_id' => $d->id])
            ->assertOk()->assertJsonPath('cargo.nombre', 'Bordador')->assertJsonPath('cargo.departamento.nombre', 'Producción');
    }

    // ── Colores ──────────────────────────────────────────────────────

    public function test_color_alta_normaliza_hex_y_valida_formato(): void
    {
        $admin = $this->admin();
        $this->assertExito($this->actingAs($admin)->post(route('colores.store'), ['nombre' => 'Azul Marino', 'hex_referencial' => '#1e3c72', 'grupo' => 'Azules']));
        $this->assertSame('#1E3C72', Color::sole()->hex_referencial);

        $this->actingAs($admin)->postJson(route('colores.store'), ['nombre' => 'Rojo', 'hex_referencial' => 'rojo'])
            ->assertStatus(422)->assertJsonValidationErrors('hex_referencial');
        $this->actingAs($admin)->postJson(route('colores.store'), ['nombre' => 'azul marino', 'hex_referencial' => '#000000'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');
    }

    public function test_color_edicion_inhabilitar_y_restaurar(): void
    {
        $admin = $this->admin();
        $c = Color::create(['nombre' => 'Vinotinto', 'hex_referencial' => '#7B1E2B']);

        $this->assertExito($this->actingAs($admin)->put(route('colores.update', $c), ['nombre' => 'Vinotinto', 'hex_referencial' => '#6b1a26', 'grupo' => 'Rojos']));
        $this->assertSame('#6B1A26', $c->fresh()->hex_referencial);
        $this->assertSame('Rojos', $c->fresh()->grupo);

        $this->assertExito($this->actingAs($admin)->delete(route('colores.destroy', $c)));
        $this->assertSoftDeleted($c);
        $this->assertExito($this->actingAs($admin)->patch(route('colores.restore', $c->id)));
        $this->assertNotSoftDeleted($c);
    }

    public function test_cotizaciones_crea_colores_y_lista_solo_activos_por_json(): void
    {
        $this->ajax()->post(route('colores.store'), ['nombre' => 'Beige', 'hex_referencial' => '#F5F5DC'])
            ->assertOk()->assertJsonPath('color.nombre', 'Beige')->assertJsonPath('message', 'Color creado correctamente.');
        Color::create(['nombre' => 'Retirado', 'hex_referencial' => '#000000'])->delete();

        $this->ajax()->get(route('colores.data'))->assertOk()->assertJsonCount(1)->assertJsonPath('0.nombre', 'Beige');
    }

    public function test_sin_permiso_no_se_gestiona_ningun_catalogo(): void
    {
        $u = $this->usuarioSinPermisos();
        $this->actingAs($u)->postJson(route('departamentos.store'), ['nombre' => 'X Y Z'])->assertForbidden();
        $this->actingAs($u)->postJson(route('colores.store'), ['nombre' => 'Negro', 'hex_referencial' => '#000000'])->assertForbidden();

        $this->assertSame(0, Departamento::count() + Color::count());
    }
}
