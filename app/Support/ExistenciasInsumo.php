<?php

namespace App\Support;

use App\Models\Insumo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Existencias de los insumos inventariables activos: la pestaña «Existencias»
 * de Movimientos y la de Compras muestran lo mismo (el costo es el de la
 * última compra procesada). Filtros: tipo_insumo, alerta, buscar (ya
 * saneados con FiltrosUrl::de()).
 */
class ExistenciasInsumo
{
    /** bajo = en o bajo el mínimo · medio = hasta 1,5 × el mínimo · normal. */
    public static function estado(Insumo $i): string
    {
        if ($i->stock_actual <= $i->stock_minimo) {
            return 'bajo';
        }

        return $i->stock_actual <= $i->stock_minimo * 1.5 ? 'medio' : 'normal';
    }

    public static function paginar(array $f, int $porPagina = 20): LengthAwarePaginator
    {
        // Más recientes primero, como el resto de los listados del sistema
        // (y como la tabla de existencias de DataTables).
        $q = Insumo::where('estado', true)->where('is_inventoriable', true)->orderByDesc('id');
        if (! empty($f['tipo_insumo'])) {
            $q->where('tipo', $f['tipo_insumo']);
        }
        if (! empty($f['alerta'])) {
            $q->whereColumn('stock_actual', '<=', 'stock_minimo');
        }
        if (! empty($f['buscar'])) {
            $kw = '%'.FiltrosUrl::like($f['buscar']).'%';
            $q->where(fn ($w) => $w->where('nombre', 'like', $kw)->orWhere('codigo', 'like', $kw));
        }

        return $q->paginate($porPagina)->withQueryString()->through(fn (Insumo $i) => [
            'id' => $i->id,
            'nombre' => $i->nombre,
            'codigo' => $i->codigo,
            'tipo' => $i->tipo,
            'unidad' => $i->unidad_medida,
            'minimo' => (float) $i->stock_minimo,
            'actual' => (float) $i->stock_actual,
            'maximo' => (float) $i->stock_maximo,
            'costo' => (float) $i->costo_unitario,
            'estado' => self::estado($i),
        ]);
    }

    /** Tipos presentes entre los insumos inventariables activos (filtro de la pestaña). */
    public static function tipos(): array
    {
        return Insumo::where('estado', true)->where('is_inventoriable', true)->distinct()->orderBy('tipo')->pluck('tipo')->all();
    }
}
