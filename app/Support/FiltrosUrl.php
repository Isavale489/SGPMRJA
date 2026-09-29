<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Filtros que llegan en la URL de un listado. La URL la puede escribir
 * cualquiera: un `?buscar[]=x` llega como arreglo y una fecha como `abc`
 * rompe la consulta (MySQL 1525). Aquí se descarta lo que no sirve antes de
 * armar el SQL, y se escapan los comodines de LIKE.
 */
class FiltrosUrl
{
    /**
     * Solo las claves pedidas con texto no vacío. Las de $fechas, además,
     * tienen que ser una fecha AAAA-MM-DD que exista; si no, se ignoran.
     */
    public static function de(Request $request, array $claves, array $fechas = []): array
    {
        $filtros = [];
        foreach ($claves as $clave) {
            $valor = $request->query($clave);
            if (is_int($valor) || is_float($valor)) {
                $valor = (string) $valor;
            }
            if (! is_string($valor) || trim($valor) === '') {
                continue;
            }
            if (in_array($clave, $fechas, true) && self::fecha($valor) === null) {
                continue;
            }
            $filtros[$clave] = $valor;
        }

        return $filtros;
    }

    /** La fecha si es AAAA-MM-DD válida (sin 31 de febrero); si no, null. */
    public static function fecha(mixed $valor): ?string
    {
        if (! is_string($valor) || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $valor, $m)) {
            return null;
        }

        return checkdate((int) $m[2], (int) $m[3], (int) $m[1]) ? $valor : null;
    }

    /** Texto para `LIKE '%…%'`: `%`, `_` y `\` se buscan literalmente. */
    public static function like(string $texto): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($texto));
    }
}
