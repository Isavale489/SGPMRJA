<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Helper para construir la lista de "filtros aplicados" que se muestra en el
 * encabezado de los reportes PDF de tipo listado. Centraliza el formato del
 * rango de fechas para no repetirlo en cada controlador.
 */
class ReporteFiltros
{
    /**
     * Devuelve un texto legible para un rango de fechas, o null si no hay rango.
     * Ej: "01/06/2026 a 30/06/2026", "Desde 01/06/2026", "Hasta 30/06/2026".
     */
    public static function rango(?string $desde, ?string $hasta): ?string
    {
        $d = $desde ? Carbon::parse($desde)->format('d/m/Y') : null;
        $h = $hasta ? Carbon::parse($hasta)->format('d/m/Y') : null;

        if ($d && $h) {
            return $d . ' a ' . $h;
        }
        if ($d) {
            return 'Desde ' . $d;
        }
        if ($h) {
            return 'Hasta ' . $h;
        }
        return null;
    }

    /**
     * Etiqueta de estatus activo/inactivo a partir del valor crudo (1/0).
     */
    public static function estatus($valor): string
    {
        return (string) $valor === '0' ? 'Inhabilitados' : 'Activos';
    }
}
