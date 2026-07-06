<?php

use App\Models\Configuracion;
use App\Models\PermisoRol;
use App\Models\Rol;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

if (!function_exists('parametro')) {
    /**
     * Valor efectivo de un parámetro configurable del sistema (FEAT-004).
     *
     * Cadena de resolución: override en la tabla `configuracion` (cacheado)
     * → default del registry config/parametros.php (entrada `default` o, si
     * es null, el config file legacy apuntado por `config_key`).
     *
     * El resultado se castea según el `tipo` declarado en el registry.
     * Lanza InvalidArgumentException si la clave no está en el registry,
     * para que un typo truene en desarrollo en vez de devolver null mudo.
     *
     * Nunca toca la BD si la tabla no existe todavía (deploy a medias) ni
     * propaga errores de conexión: en esos casos resuelve con el default.
     */
    function parametro(string $clave): mixed
    {
        $registry = config('parametros', []);

        if (!array_key_exists($clave, $registry)) {
            throw new InvalidArgumentException("Parámetro [{$clave}] no está definido en config/parametros.php.");
        }

        $definicion = $registry[$clave];

        $overrides = Cache::rememberForever('parametros', function () {
            try {
                if (!Schema::hasTable('configuracion')) {
                    return [];
                }

                return Configuracion::pluck('valor', 'clave')->all();
            } catch (\Throwable $e) {
                // BD caída o aún sin migrar: el helper jamás debe tirar 500
                // por sí solo; se resuelve con los defaults del registry.
                return [];
            }
        });

        $valor = $overrides[$clave]
            ?? $definicion['default']
            ?? (isset($definicion['config_key']) ? config($definicion['config_key']) : null);

        return match ($definicion['tipo']) {
            'decimal'  => (float) $valor,
            'entero'   => (int) $valor,
            'booleano' => filter_var($valor, FILTER_VALIDATE_BOOLEAN),
            default    => (string) $valor,
        };
    }
}

if (!function_exists('tienePermiso')) {
    /**
     * ¿El usuario autenticado tiene el permiso 'modulo.accion'? (FEAT-005).
     *
     * Cadena de resolucion:
     *   - Sin sesion (invitado)            => false.
     *   - Administrador (Gate::before)     => true (bypass total, no consulta BD).
     *   - Cualquier otro rol               => true si 'modulo.accion' esta en la
     *                                         lista de permisos de SU rol.
     *
     * --------------------------------------------------------------------
     * CONVENCION DE CACHE (la usa TASK-039 / CRUD de roles)
     * --------------------------------------------------------------------
     *   Key:    "permisos.rol_{id}"  (UNA por rol, NO por usuario — spec 7).
     *   Valor:  array<string> de permisos otorgados ('compras.ver', ...).
     *   Miss:   se consulta la tabla permiso_rol y se cachea para siempre.
     *   Flush:  toda escritura de la matriz / edicion / borrado de un rol DEBE
     *           hacer  Cache::forget("permisos.rol_{$rolId}")  para que el
     *           siguiente request del rol refleje el cambio sin re-login.
     *
     * El Administrador no consulta permiso_rol: el bypass de admin se evalua
     * aqui (defensa en profundidad) y tambien en Gate::before del
     * AuthServiceProvider; ambos delegan en esrolAdministrador()/isAdmin().
     *
     * Robusto ante despliegue a medias: si la BD esta caida o las tablas de
     * roles aun no existen, el helper jamas tira 500 por si solo — deniega
     * (false) salvo para el Administrador.
     */
    function tienePermiso(string $permiso): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Bypass del Administrador (anti-lockout estructural, spec 7).
        if (esUsuarioAdministrador($user)) {
            return true;
        }

        $rolId = $user->role_id ?? null;
        if (!$rolId) {
            return false;
        }

        $permisos = Cache::rememberForever("permisos.rol_{$rolId}", function () use ($rolId) {
            try {
                if (!Schema::hasTable('permiso_rol')) {
                    return [];
                }

                return PermisoRol::where('rol_id', $rolId)->pluck('permiso')->all();
            } catch (\Throwable $e) {
                // BD caida o sin migrar: nunca tirar 500; denegar por defecto.
                return [];
            }
        });

        return in_array($permiso, $permisos, true);
    }
}

if (!function_exists('permisoDeRuta')) {
    /**
     * Resuelve el permiso 'modulo.accion' que exige una ruta nombrada, segun
     * el registry config/modulos.php (misma resolucion que el middleware
     * CheckPermiso). Devuelve null si la ruta no esta mapeada (que para el
     * middleware equivale a denegar por defecto).
     *
     * Uso tipico: decidir si se muestra un enlace/acceso a una ruta sin
     * duplicar el permiso a mano (ej. catalogo de Reportes Generales):
     *   $permiso = permisoDeRuta('compras.reporte.pdf');
     *   $visible = $permiso !== null && tienePermiso($permiso);
     */
    function permisoDeRuta(string $nombreRuta): ?string
    {
        foreach (config('modulos', []) as $modulo => $config) {
            if ($modulo === 'comunes' || !isset($config['rutas'])) {
                continue;
            }

            foreach ($config['rutas'] as $patron => $accion) {
                if (in_array($nombreRuta, explode('|', $patron), true)) {
                    return "{$modulo}.{$accion}";
                }
            }
        }

        return null;
    }
}

if (!function_exists('esUsuarioAdministrador')) {
    /**
     * Determina si el usuario es Administrador de forma robusta (FEAT-005).
     *
     * Prefiere el contrato de dominio User::isAdmin() (capa de compatibilidad
     * del Modulo 2). Si ese metodo aun no resuelve por nombre de rol (deploy
     * parcial), cae a comparar el nombre del rol relacionado por role_id contra
     * 'Administrador'. Asi el bypass de admin funciona aunque la capa de
     * compatibilidad de User no este completamente desplegada.
     */
    function esUsuarioAdministrador($user): bool
    {
        if (!$user) {
            return false;
        }

        try {
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return true;
            }
        } catch (\Throwable $e) {
            // ignorar y caer al chequeo por relacion
        }

        try {
            $rolId = $user->role_id ?? null;
            if ($rolId && Schema::hasTable('rol')) {
                $nombre = Cache::rememberForever("rol_nombre_{$rolId}", function () use ($rolId) {
                    return \App\Models\Rol::whereKey($rolId)->value('nombre');
                });

                return $nombre === 'Administrador';
            }
        } catch (\Throwable $e) {
            return false;
        }

        return false;
    }
}
