<?php

namespace Tests\Feature\Flujos;

use App\Models\PermisoRol;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Panel de seguridad: roles y matriz de permisos (tabla permiso_rol).
 * Acceso solo del Administrador (gate, fuera de la matriz: anti-escalada).
 * Escritos ANTES de migrar a Inertia.
 */
class SeguridadFlujoTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private function rol(string $nombre = 'Vendedor'): Rol
    {
        return Rol::create(['nombre' => $nombre, 'es_sistema' => false]);
    }

    public function test_solo_el_administrador_entra_aunque_tenga_todos_los_permisos_de_la_matriz(): void
    {
        $rol = $this->rol();
        foreach (array_keys(config('modulos')) as $m) {
            if ($m !== 'comunes') {
                foreach (array_keys(config("modulos.{$m}.acciones") ?? []) as $a) {
                    PermisoRol::create(['rol_id' => $rol->id, 'permiso' => "{$m}.{$a}"]);
                }
            }
        }
        $casiAdmin = User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($casiAdmin)->get(route('seguridad.index'))->assertForbidden();
        $this->actingAs($casiAdmin)->putJson(route('seguridad.permisos.update', $rol), ['permisos' => ['clientes.ver']])->assertForbidden();
        $this->actingAs($casiAdmin)->postJson(route('seguridad.roles.store'), ['nombre' => 'Nuevo'])->assertForbidden();
        $this->actingAs($this->admin())->get(route('seguridad.index'))->assertOk();
    }

    public function test_crear_renombrar_y_eliminar_un_rol(): void
    {
        $admin = $this->admin();
        $this->assertExito($this->actingAs($admin)->postJson(route('seguridad.roles.store'), ['nombre' => 'Vendedor', 'descripcion' => 'Ventas']));
        $rol = Rol::where('nombre', 'Vendedor')->sole();
        $this->assertFalse((bool) $rol->es_sistema);

        $this->actingAs($admin)->postJson(route('seguridad.roles.store'), ['nombre' => 'Vendedor'])
            ->assertStatus(422)->assertJsonValidationErrors('nombre');

        $this->assertExito($this->actingAs($admin)->putJson(route('seguridad.roles.update', $rol), ['nombre' => 'Vendedor senior']));
        $this->assertSame('Vendedor senior', $rol->fresh()->nombre);

        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'clientes.ver']);
        $this->assertExito($this->actingAs($admin)->deleteJson(route('seguridad.roles.destroy', $rol)));
        $this->assertNull(Rol::find($rol->id));
        $this->assertSame(0, PermisoRol::where('rol_id', $rol->id)->count());
    }

    public function test_los_roles_de_sistema_solo_cambian_su_descripcion_y_no_se_eliminan(): void
    {
        $admin = $this->admin();
        $sistema = Rol::where('nombre', 'Administrador')->sole();

        $this->assertExito($this->actingAs($admin)->putJson(route('seguridad.roles.update', $sistema), ['nombre' => 'Jefe', 'descripcion' => 'Acceso total']));
        $sistema->refresh();
        $this->assertSame('Administrador', $sistema->nombre);
        $this->assertSame('Acceso total', $sistema->descripcion);

        $this->actingAs($admin)->deleteJson(route('seguridad.roles.destroy', $sistema))->assertForbidden();
        $this->assertNotNull(Rol::find($sistema->id));
    }

    public function test_un_rol_con_usuarios_no_se_elimina(): void
    {
        $rol = $this->rol();
        User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($this->admin())->deleteJson(route('seguridad.roles.destroy', $rol))->assertStatus(422);
        $this->assertNotNull(Rol::find($rol->id));
    }

    public function test_la_matriz_filtra_claves_fuerza_ver_y_aplica_sin_volver_a_entrar(): void
    {
        $admin = $this->admin();
        $rol = $this->rol();
        $vendedor = User::factory()->create(['role_id' => $rol->id]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'colores.ver']);

        $this->actingAs($vendedor)->get(route('clientes.index'))->assertForbidden();

        $this->assertExito($this->actingAs($admin)->putJson(route('seguridad.permisos.update', $rol), [
            'permisos' => ['clientes.gestionar', 'inventado.hackear', 'seguridad.ver'],
        ]));

        $permisos = PermisoRol::where('rol_id', $rol->id)->pluck('permiso')->sort()->values()->all();
        $this->assertSame(['clientes.gestionar', 'clientes.ver'], $permisos); // «ver» forzado; reemplaza colores.ver; basura descartada

        $this->actingAs($vendedor)->get(route('clientes.index'))->assertOk(); // la caché del rol se purgó
        $this->actingAs($vendedor)->get(route('colores.index'))->assertForbidden();
    }

    /** Regresión: un arreglo anidado en `permisos` daba 500 (array_intersect). */
    public function test_la_matriz_rechaza_claves_que_no_son_texto(): void
    {
        $admin = $this->admin();
        $rol = $this->rol('Taller');

        $this->actingAs($admin)->putJson(route('seguridad.permisos.update', $rol), ['permisos' => [['clientes.ver']]])
            ->assertStatus(422)->assertJsonValidationErrors('permisos.0');
        $this->assertSame(0, $rol->permisos()->count());
    }

    public function test_el_administrador_no_se_edita_en_la_matriz(): void
    {
        $admin = $this->admin();
        $sistema = Rol::where('nombre', 'Administrador')->sole();

        $this->actingAs($admin)->putJson(route('seguridad.permisos.update', $sistema), ['permisos' => []])->assertForbidden();
        $this->actingAs($admin)->get(route('seguridad.index'))->assertOk();
        // Sigue con acceso total.
        $this->actingAs($admin)->get(route('clientes.index'))->assertOk();
    }
}
