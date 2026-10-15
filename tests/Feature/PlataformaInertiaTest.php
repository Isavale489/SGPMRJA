<?php

namespace Tests\Feature;

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\PermisoRol;
use App\Models\Rol;
use App\Models\TasaCambio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CreaDatosBase;
use Tests\TestCase;

/**
 * Plataforma Inertia (paso 2): datos compartidos, menú filtrado por permisos
 * y coherencia de config/navegacion.php con rutas, permisos e íconos.
 */
class PlataformaInertiaTest extends TestCase
{
    use RefreshDatabase, CreaDatosBase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake(); // la tasa BCV nunca llama a la API real
    }

    /** Evalúa los datos compartidos como los vería una página, para el usuario dado. */
    private function compartidos(User $user): array
    {
        $this->actingAs($user);
        $request = Request::create('/plataforma/componentes');
        $request->setLaravelSession(app('session.store'));
        $request->setUserResolver(fn () => $user);

        $valor = fn ($v) => $v instanceof \Closure ? $v() : $v;

        return collect(app(HandleInertiaRequests::class)->share($request))
            ->map(fn ($v) => is_array($v) ? array_map($valor, $v) : $valor($v))
            ->all();
    }

    private function titulos(array $items): array
    {
        return collect($items)->flatMap(fn ($i) => isset($i['items']) ? [$i['titulo'], ...$this->titulos($i['items'])] : [$i['titulo']])->all();
    }

    public function test_el_catalogo_renderiza_con_los_datos_compartidos(): void
    {
        TasaCambio::create(['moneda' => 'USD', 'valor' => 40.5, 'fecha_bcv' => today(), 'fuente' => 'test']);

        $this->actingAs($this->admin())
            ->get(route('plataforma.componentes'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Plataforma/Componentes')
                ->where('auth.esAdmin', true)
                ->has('auth.user.name')
                ->where('tasaBcv.valor', 40.5)
                ->where('tasaBcv.fecha', today()->toDateString())
                ->has('navegacion', 4));
    }

    public function test_la_plantilla_no_carga_bootstrap_ni_jquery(): void
    {
        $html = $this->actingAs($this->admin())->get(route('plataforma.componentes'))->getContent();

        $this->assertStringNotContainsString('bootstrap', $html);
        $this->assertStringNotContainsString('jquery', strtolower($html));
    }

    public function test_sin_permiso_no_se_accede_al_catalogo(): void
    {
        $this->actingAs($this->usuarioSinPermisos())->get(route('plataforma.componentes'))->assertForbidden();
    }

    public function test_el_menu_solo_muestra_lo_que_el_rol_puede_ver(): void
    {
        $rol = Rol::create(['nombre' => 'Vendedor', 'es_sistema' => false]);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'cotizaciones.ver']);
        PermisoRol::create(['rol_id' => $rol->id, 'permiso' => 'colores.ver']);
        $vendedor = User::factory()->create(['role_id' => $rol->id]);

        $datos = $this->compartidos($vendedor);
        $titulos = $this->titulos($datos['navegacion']);

        $this->assertFalse($datos['auth']['esAdmin']);
        $this->assertEqualsCanonicalizing(['cotizaciones.ver', 'colores.ver'], $datos['auth']['permisos']);
        // Solo sus dos enlaces, con sus secciones/grupos; nada más.
        $this->assertEqualsCanonicalizing(
            ['Inicio', 'Gestión General', 'Productos', 'Colores', 'Gestión Operativa', 'Cotizaciones'],
            $titulos,
        );
    }

    public function test_el_administrador_ve_el_menu_completo(): void
    {
        $titulos = $this->titulos($this->compartidos($this->admin())['navegacion']);

        foreach (['Clientes', 'Proveedores', 'Compras', 'Control de Calidad', 'Reportes Generales'] as $t) {
            $this->assertContains($t, $titulos);
        }
    }

    public function test_la_navegacion_es_coherente_con_rutas_permisos_e_iconos(): void
    {
        $iconosTsx = file_get_contents(resource_path('js/components/app/icono.tsx'));
        preg_match('/export const ICONOS[^{]*\{([^}]*)\}/s', $iconosTsx, $m);
        $iconos = array_map('trim', explode(',', trim($m[1] ?? '', " \n,")));

        $permisosValidos = collect(config('modulos'))->except('comunes')
            ->flatMap(fn ($mod, $clave) => collect(array_keys($mod['acciones'] ?? []))->map(fn ($a) => "{$clave}.{$a}"))
            ->all();

        $problemas = [];
        $revisar = function (array $items) use (&$revisar, &$problemas, $iconos, $permisosValidos) {
            foreach ($items as $i) {
                if (! in_array($i['icono'], $iconos, true)) {
                    $problemas[] = "ícono '{$i['icono']}' de «{$i['titulo']}» no está en ICONOS (icono.tsx)";
                }
                if (isset($i['items'])) {
                    $revisar($i['items']);
                    continue;
                }
                if (! Route::has($i['ruta'])) {
                    $problemas[] = "ruta inexistente '{$i['ruta']}' en «{$i['titulo']}»";
                }
                if (isset($i['permiso']) && ! in_array($i['permiso'], $permisosValidos, true)) {
                    $problemas[] = "permiso '{$i['permiso']}' de «{$i['titulo']}» no existe en config/modulos.php";
                }
            }
        };
        $revisar(config('navegacion'));

        $this->assertSame([], $problemas, implode("\n", $problemas));
    }
}
