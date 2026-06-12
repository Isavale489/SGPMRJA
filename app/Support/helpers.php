<?php

use App\Models\Configuracion;
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
