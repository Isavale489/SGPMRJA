<?php

namespace App\Http\Middleware;

use App\Support\TasaBcvVigente;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'inertia';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'app' => ['nombre' => config('app.name')],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    // Sin foto: null y el menú muestra las iniciales (sin pedir a ui-avatars.com).
                    'avatar_url' => $user->avatarSubido(),
                    'rol' => $user->role,
                ] : null,
                // El Administrador no tiene filas en permiso_rol: entra por Gate::before.
                'esAdmin' => $user ? esUsuarioAdministrador($user) : false,
                'permisos' => fn () => $user && ! esUsuarioAdministrador($user) && $user->role_id
                    ? permisosDeRol((int) $user->role_id)
                    : [],
            ],
            'navegacion' => fn () => $user ? $this->navegacion(config('navegacion', [])) : [],
            // Sección de la página actual (config/secciones.php): el layout la pone en
            // <html data-seccion> y de ahí salen los colores del grupo.
            'seccion' => fn () => $this->seccion($request),
            // Sin usuario (pantallas de acceso) no se muestra: tampoco se consulta al BCV.
            'tasaBcv' => function () use ($user) {
                if (! $user) {
                    return null;
                }
                $tasa = TasaBcvVigente::obtener();

                return $tasa ? [
                    'valor' => (float) $tasa->valor,
                    'fecha' => Carbon::parse($tasa->fecha_bcv)->toDateString(),
                ] : null;
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                // Pantallas de acceso: éxito (p. ej. «contraseña actualizada») y sesión vencida (Handler).
                'status' => fn () => $request->session()->get('status'),
                'aviso' => fn () => $request->session()->get('aviso'),
            ],
        ];
    }

    /**
     * Sección de la ruta actual con su título e ícono, o null (rutas comunes
     * como el dashboard o el perfil: identidad de marca, sin color de grupo).
     *
     * @return array{clave: string, titulo: string, icono: string}|null
     */
    private function seccion(Request $request): ?array
    {
        $ruta = $request->route()?->getName();
        $clave = $ruta ? seccionDeRuta($ruta) : null;
        if ($clave === null) {
            return null;
        }
        $def = config("secciones.{$clave}");

        return ['clave' => $clave, 'titulo' => $def['titulo'], 'icono' => $def['icono']];
    }

    /**
     * Filtra config/navegacion.php por permisos y resuelve rutas a URL.
     * Descarta secciones y grupos que se quedan sin enlaces visibles.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function navegacion(array $items): array
    {
        $visibles = [];

        foreach ($items as $item) {
            if (isset($item['items'])) {
                $hijos = $this->navegacion($item['items']);
                if ($hijos !== []) {
                    // 'seccion' solo en las secciones de primer nivel: el menú pinta cada una con su color.
                    $visibles[] = ['titulo' => $item['titulo'], 'icono' => $item['icono'], 'seccion' => $item['seccion'] ?? null, 'items' => $hijos];
                }
                continue;
            }

            if (isset($item['permiso']) && ! tienePermiso($item['permiso'])) {
                continue;
            }

            $visibles[] = [
                'titulo' => $item['titulo'],
                'icono' => $item['icono'],
                'url' => route($item['ruta'], absolute: false),
                'ruta' => $item['ruta'],
                // true solo si la página destino ya es Inertia: el cliente usa
                // <Link> (sin recarga); si es Blade, un <a> con recarga completa.
                'inertia' => (bool) ($item['inertia'] ?? false),
            ];
        }

        return $visibles;
    }
}
