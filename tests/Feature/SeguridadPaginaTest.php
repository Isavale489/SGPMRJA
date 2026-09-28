<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\PermisoRol;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/** Página Inertia de Roles y permisos: contrato, matriz por rol y respuestas a Inertia. */
class SeguridadPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Seguridad/Index.tsx'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_la_pagina_trae_roles_matriz_y_permisos_sin_el_administrador(): void
    {
        $rol = Rol::create(['nombre' => 'Vendedor', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'clientes.ver']);
        $admin = Rol::where('nombre', 'Administrador')->sole();

        $this->actingAs($this->admin())->get(route('seguridad.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Seguridad/Index')
                ->has('roles.0', fn (Assert $r) => $r->hasAll($this->clavesTs('RolFila')))
                ->has('secciones.0', fn (Assert $s) => $s->hasAll($this->clavesTs('SeccionMatriz')))
                ->has('secciones.0.modulos.0', fn (Assert $m) => $m->hasAll($this->clavesTs('ModuloMatriz')))
                ->where("permisos.{$rol->id}", ['clientes.ver'])
                ->missing("permisos.{$admin->id}"));
    }

    public function test_los_iconos_de_la_matriz_existen_en_la_plataforma(): void
    {
        preg_match('/export const ICONOS[^{]*\{([^}]*)\}/s', file_get_contents(resource_path('js/components/app/icono.tsx')), $m);
        $iconos = array_map('trim', explode(',', trim($m[1] ?? '', " \n,")));

        $props = $this->actingAs($this->admin())->get(route('seguridad.index'))->viewData('page')['props'];
        foreach ($props['secciones'] as $s) {
            $this->assertContains($s['icono'], $iconos, "Sección {$s['nombre']}");
            foreach ($s['modulos'] as $mod) {
                $this->assertContains($mod['icono'], $iconos, "Módulo {$mod['slug']}");
            }
        }
    }

    public function test_crear_un_rol_desde_inertia_devuelve_su_id_y_la_matriz_avisa(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->from(route('seguridad.index'))->withHeaders($this->inertia())
            ->post(route('seguridad.roles.store'), ['nombre' => 'Almacén'])
            ->assertRedirect(route('seguridad.index'))
            ->assertSessionHas(SessionKey::FLASH_DATA, fn ($f) => ($f['rol'] ?? null) === Rol::where('nombre', 'Almacén')->value('id'));

        $rol = Rol::where('nombre', 'Almacén')->sole();
        $this->actingAs($admin)->from(route('seguridad.index'))->withHeaders($this->inertia())
            ->put(route('seguridad.permisos.update', $rol), ['permisos' => ['insumos.gestionar']])
            ->assertSessionHas('success', 'Permisos actualizados correctamente.');
        $this->assertEqualsCanonicalizing(['insumos.gestionar', 'insumos.ver'], $rol->permisos()->pluck('permiso')->all());

        $this->actingAs($admin)->from(route('seguridad.index'))->withHeaders($this->inertia())
            ->delete(route('seguridad.roles.destroy', Rol::where('nombre', 'Administrador')->sole()))
            ->assertSessionHas('error', 'No se puede eliminar un rol de sistema.');
    }
}
