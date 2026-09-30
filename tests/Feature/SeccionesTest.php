<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Identidad por sección (config/secciones.php): cada módulo en exactamente una
 * sección, el menú y el hub de reportes coherentes con ella, y la sección de la
 * página actual compartida con el cliente (prop `seccion`).
 */
class SeccionesTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    private const CLAVES = ['maestros', 'operativa', 'reportes', 'admin'];

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // la tasa BCV nunca llama a la API real
    }

    public function test_cada_modulo_del_registry_esta_en_exactamente_una_seccion(): void
    {
        $modulos = array_keys(collect(config('modulos'))->except('comunes')->all());
        $asignados = collect(config('secciones'))->flatMap(fn ($s) => $s['modulos'])->all();

        $this->assertSame(self::CLAVES, array_keys(config('secciones')), 'Las claves de sección son las de [data-seccion] en plataforma.css y el tipo Seccion.');
        foreach ($modulos as $modulo) {
            $veces = count(array_keys($asignados, $modulo, true));
            $this->assertSame(1, $veces, "El módulo '{$modulo}' está en {$veces} secciones de config/secciones.php (debe estar en una).");
        }
        $this->assertSame([], array_values(array_diff($asignados, $modulos)), 'config/secciones.php nombra módulos que no existen en config/modulos.php.');
    }

    public function test_los_iconos_de_las_secciones_existen(): void
    {
        $iconosTsx = file_get_contents(resource_path('js/components/app/icono.tsx'));
        preg_match('/export const ICONOS[^{]*\{([^}]*)\}/s', $iconosTsx, $m);
        $iconos = array_map('trim', explode(',', trim($m[1] ?? '', " \n,")));

        foreach (config('secciones') as $clave => $s) {
            $this->assertContains($s['icono'], $iconos, "El ícono de la sección '{$clave}' no está en ICONOS (icono.tsx).");
        }
    }

    public function test_la_seccion_de_una_ruta_sale_de_su_modulo(): void
    {
        $this->assertSame('maestros', seccionDeRuta('clientes.index'));
        $this->assertSame('operativa', seccionDeRuta('pedidos.index'));
        $this->assertSame('operativa', seccionDeRuta('compras.create'));
        $this->assertSame('reportes', seccionDeRuta('reportes.produccion'));
        $this->assertSame('admin', seccionDeRuta('configuracion.index'));
        // Rutas comunes y no mapeadas: sin sección (identidad de marca).
        $this->assertNull(seccionDeRuta('dashboard'));
        $this->assertNull(seccionDeRuta('ruta.que.no.existe'));
    }

    public function test_cada_seccion_del_menu_declara_la_de_sus_enlaces(): void
    {
        $problemas = [];
        $enlaces = function (array $items) use (&$enlaces): array {
            return collect($items)->flatMap(fn ($i) => isset($i['items']) ? $enlaces($i['items']) : [$i])->all();
        };

        foreach (config('navegacion') as $item) {
            if (! isset($item['items'])) {
                // Enlace suelto de primer nivel (Inicio): ruta común, sin sección.
                if (seccionDeRuta($item['ruta']) !== null) {
                    $problemas[] = "«{$item['titulo']}» es un enlace suelto pero su ruta tiene sección";
                }
                continue;
            }
            $seccion = $item['seccion'] ?? null;
            if (! in_array($seccion, self::CLAVES, true)) {
                $problemas[] = "la sección del menú «{$item['titulo']}» no declara una 'seccion' válida";
                continue;
            }
            foreach ($enlaces($item['items']) as $enlace) {
                $real = seccionDeRuta($enlace['ruta']);
                if ($real !== $seccion) {
                    $problemas[] = "«{$enlace['titulo']}» está en «{$item['titulo']}» ({$seccion}) pero su módulo es de '{$real}'";
                }
            }
        }

        $this->assertSame([], $problemas, implode("\n", $problemas));
    }

    public function test_los_grupos_del_hub_de_reportes_usan_secciones_validas(): void
    {
        foreach (config('reportes.grupos') as $grupo) {
            $this->assertContains($grupo['seccion'] ?? null, self::CLAVES, "El grupo «{$grupo['titulo']}» de config/reportes.php no tiene una 'seccion' válida.");
        }
    }

    public function test_cada_pagina_comparte_su_seccion(): void
    {
        $admin = $this->admin();

        foreach ([
            'clientes.index' => ['maestros', 'Gestión General'],
            'pedidos.index' => ['operativa', 'Gestión Operativa'],
            'reportes.produccion' => ['reportes', 'Consultas y Reportes'],
            'configuracion.index' => ['admin', 'Administración'],
        ] as $ruta => [$clave, $titulo]) {
            $this->actingAs($admin)->get(route($ruta))->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->where('seccion.clave', $clave)
                    ->where('seccion.titulo', $titulo)
                    ->has('seccion.icono'));
        }

        $this->actingAs($admin)->get(route('dashboard'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('seccion', null));
    }

    public function test_el_menu_compartido_lleva_la_seccion_de_cada_grupo(): void
    {
        $menu = collect();
        $this->actingAs($this->admin())->get(route('dashboard'))->assertOk()
            ->assertInertia(function (Assert $page) use (&$menu) {
                $menu = collect($page->toArray()['props']['navegacion'])->keyBy('titulo');
            });

        // Por título, no por posición: agregar un enlace al menú no rompe el test.
        $this->assertSame('maestros', $menu['Gestión General']['seccion']);
        $this->assertSame('operativa', $menu['Gestión Operativa']['seccion']);
        $this->assertSame('reportes', $menu['Consultas y Reportes']['seccion']);
        // Los subgrupos (Productos dentro de Gestión General) heredan: no declaran la suya.
        $productos = collect($menu['Gestión General']['items'])->firstWhere('titulo', 'Productos');
        $this->assertNull($productos['seccion']);
    }
}
