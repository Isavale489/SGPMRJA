<?php

namespace App\Support;

use App\Models\Estado;
use Illuminate\Support\Facades\Cache;

/**
 * Catálogo geográfico de Venezuela (tablas estado/municipio), cacheado un día
 * porque es estático. Lo usan las páginas Inertia
 * (formularios de dirección).
 */
class CatalogoGeografico
{
    /**
     * @return array<string, list<string>> nombre del estado => nombres de sus municipios
     */
    public static function mapa(): array
    {
        // '.v2': la forma cambió respecto de la clave anterior ([estados, mapa]);
        // una clave nueva evita leer en producción un valor viejo cacheado.
        try {
            return Cache::remember('catalogo_geografico_ve.v2', now()->addDay(), function () {
                $mapa = [];
                foreach (Estado::with('municipios:id,estado_id,nombre')->orderBy('nombre')->get() as $e) {
                    $mapa[$e->nombre] = $e->municipios->pluck('nombre')->values()->all();
                }

                return $mapa;
            });
        } catch (\Exception $e) {
            return [];
        }
    }
}
