<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\JsonResponse;

class NotificacionController extends Controller
{
    public function sistema(): JsonResponse
    {
        $items = [];

        $insumos = Insumo::where('estado', true)
            ->where('is_inventoriable', true)
            ->whereRaw('stock_actual <= stock_minimo')
            ->orderByRaw('(stock_actual / NULLIF(stock_minimo, 0)) ASC')
            ->get(['id', 'nombre', 'codigo', 'stock_actual', 'stock_minimo', 'unidad_medida']);

        foreach ($insumos as $insumo) {
            $agotado = (float) $insumo->stock_actual <= 0;
            $items[] = [
                'id'        => 'insumo-' . $insumo->id,
                'tipo'      => 'stock_bajo',
                'severidad' => $agotado ? 'danger' : 'warning',
                'icono'     => $agotado ? 'ri-error-warning-line' : 'ri-alert-line',
                'titulo'    => $agotado ? 'Insumo agotado' : 'Stock bajo',
                'mensaje'   => trim(($insumo->codigo ? "[{$insumo->codigo}] " : '') . $insumo->nombre)
                                . ' · ' . rtrim(rtrim(number_format((float) $insumo->stock_actual, 2, '.', ''), '0'), '.')
                                . ' / mín ' . rtrim(rtrim(number_format((float) $insumo->stock_minimo, 2, '.', ''), '0'), '.')
                                . ($insumo->unidad_medida ? ' ' . $insumo->unidad_medida : ''),
                'url'       => route('movimiento-insumo.alertas'),
            ];
        }

        return response()->json([
            'count' => count($items),
            'items' => $items,
        ]);
    }
}
