<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Autorizacion en runtime dirigida por el registry config/modulos.php (FEAT-005).
 *
 * Resuelve el permiso requerido DESDE el nombre de la ruta actual:
 *   1. Sin sesion        => login.
 *   2. Ruta sin nombre   => 403 + log (no se puede mapear).
 *   3. Ruta en 'comunes' => pasa (transversal a todo autenticado).
 *   4. Ruta mapeada      => exige tienePermiso('modulo.accion'); si no, 403.
 *   5. Ruta SIN mapeo    => DENEGAR POR DEFECTO: 403 + Log::warning (hueco
 *      del registry a cerrar en QA — spec 7: denegar > permitir).
 *
 * El Administrador entra por Gate::before / tienePermiso() (bypass total).
 *
 * Alias: 'permiso' (app/Http/Kernel.php). Agregar un modulo nuevo al registry
 * NO requiere tocar este middleware.
 */
class CheckPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        $nombreRuta = $request->route()?->getName();

        // Ruta sin nombre: no se puede resolver contra el registry => denegar.
        if (!$nombreRuta) {
            Log::warning('CheckPermiso: ruta autenticada sin nombre, denegada por defecto.', [
                'uri'    => $request->path(),
                'method' => $request->method(),
            ]);
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        $registry = config('modulos', []);

        // 1) Rutas comunes: accesibles para todo usuario autenticado.
        $comunes = $registry['comunes'] ?? [];
        if (in_array($nombreRuta, $comunes, true)) {
            return $next($request);
        }

        // 2) Resolver el permiso requerido desde los patrones 'rutas' del registry.
        $permisoRequerido = $this->resolverPermiso($nombreRuta, $registry);

        // 3) Denegar por defecto: ruta autenticada sin mapeo en el registry.
        if ($permisoRequerido === null) {
            Log::warning('CheckPermiso: ruta sin mapeo en config/modulos.php, denegada por defecto.', [
                'ruta'   => $nombreRuta,
                'uri'    => $request->path(),
                'method' => $request->method(),
            ]);
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        // 4) Evaluar el permiso (admin pasa por bypass dentro de tienePermiso()).
        if (tienePermiso($permisoRequerido)) {
            return $next($request);
        }

        abort(403, 'No tiene permiso para acceder a esta sección.');
    }

    /**
     * Devuelve el permiso 'modulo.accion' que exige una ruta, o null si ningun
     * modulo del registry la mapea.
     */
    private function resolverPermiso(string $nombreRuta, array $registry): ?string
    {
        foreach ($registry as $modulo => $config) {
            if ($modulo === 'comunes' || !isset($config['rutas'])) {
                continue;
            }

            foreach ($config['rutas'] as $patron => $accion) {
                // Patron 'ruta.a|ruta.b|ruta.c' => coincidencia exacta por nombre.
                $nombres = explode('|', $patron);
                if (in_array($nombreRuta, $nombres, true)) {
                    return "{$modulo}.{$accion}";
                }
            }
        }

        return null;
    }
}
