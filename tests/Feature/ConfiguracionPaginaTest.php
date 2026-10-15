<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\Configuracion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/** Página Inertia de Configuración: registry por módulo, impuestos y errores por campo. */
class ConfiguracionPaginaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake();
    }

    private function clavesTs(string $interfaz): array
    {
        $ts = file_get_contents(resource_path('js/pages/Configuracion/Index.tsx'));
        preg_match("/export interface {$interfaz} \\{(.*?)\\n\\}/s", $ts, $m);
        preg_match_all('/^    (\w+)\??:/m', $m[1] ?? '', $claves);
        $this->assertNotEmpty($claves[1], "No se pudo leer {$interfaz}");

        return $claves[1];
    }

    private function inertia(): array
    {
        return ['X-Inertia' => 'true', 'X-Inertia-Version' => (string) app(HandleInertiaRequests::class)->version(request()), 'X-Requested-With' => 'XMLHttpRequest'];
    }

    public function test_la_pagina_trae_el_registry_y_los_impuestos(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->putJson(route('configuracion.update', 'cotizaciones'), ['valores' => ['cotizaciones.dias_vigencia' => 20]]);

        $this->actingAs($admin)->get(route('configuracion.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p->component('Configuracion/Index')
                ->has('modulos.0', fn (Assert $m) => $m->hasAll($this->clavesTs('ModuloConfig')))
                ->has('modulos.0.parametros.0', fn (Assert $x) => $x->hasAll($this->clavesTs('Parametro')))
                ->where('modulos', fn ($ms) => collect($ms)->flatMap(fn ($m) => $m['parametros'])->firstWhere('clave', 'cotizaciones.dias_vigencia')['es_default'] === false
                    && collect($ms)->flatMap(fn ($m) => $m['parametros'])->firstWhere('clave', 'cotizaciones.dias_vigencia')['default'] === 15)
                ->where('impuestos', fn ($is) => collect($is)->every(fn ($i) => array_keys($i) == $this->clavesTs('ImpuestoFila'))));
    }

    public function test_un_valor_invalido_llega_al_campo_y_uno_valido_avisa(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->from(route('configuracion.index'))->withHeaders($this->inertia())
            ->put(route('configuracion.update', 'pedidos'), ['valores' => ['pedidos.abono_minimo' => 150]])
            ->assertRedirect(route('configuracion.index'))
            ->assertSessionHasErrors('valores.pedidos.abono_minimo');
        $this->assertSame(0, Configuracion::count());

        $this->actingAs($admin)->from(route('configuracion.index'))->withHeaders($this->inertia())
            ->put(route('configuracion.update', 'pedidos'), ['valores' => ['pedidos.abono_minimo' => 60]])
            ->assertSessionHas('success', 'Configuración guardada correctamente.');
        $this->assertEquals(60, parametro('pedidos.abono_minimo'));
    }

    public function test_solo_ver_no_gestiona(): void
    {
        $rol = \App\Models\Rol::create(['nombre' => 'Consulta config', 'es_sistema' => false]);
        \App\Models\PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'configuracion.ver']);
        $u = \App\Models\User::factory()->create(['role_id' => $rol->id]);

        $this->actingAs($u)->get(route('configuracion.index'))->assertOk();
        $this->actingAs($u)->put(route('configuracion.update', 'pedidos'), ['valores' => ['pedidos.abono_minimo' => 60]])->assertForbidden();
    }
}
