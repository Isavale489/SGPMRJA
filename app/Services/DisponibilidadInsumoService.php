<?php

namespace App\Services;

use App\Models\Insumo;
use App\Models\TipoProducto;

/**
 * Proyección de disponibilidad de insumos para producir un conjunto de líneas
 * (de cotización o de pedido). Es INFORMATIVA: no descuenta stock ni bloquea.
 *
 * Reusa el MISMO "bill of materials" que la Orden de Producción para que el
 * aviso nunca contradiga al bloqueo real del 422 en
 * ProduccionInventarioService::validarYDescontar():
 *   - insumos del tipo de producto (pivot tipo_producto_insumo.cantidad_estimada)
 *   - tela (tipo_producto.consumo_tela_por_unidad) si el tipo la requiere
 *
 * Respeta los dos filtros que definen qué consume stock:
 *   - requiere_produccion = false  → reventa, no se fabrica → se ignora
 *   - is_inventoriable     = false → servicios/mano de obra → no mueve stock
 *
 * Comparación contra `insumo.stock_actual` CRUDO (no descuenta demanda de otros
 * pedidos). El resultado es un snapshot en vivo: hay que recalcularlo cada vez,
 * nunca persistirlo (el stock se mueve con compras y otras órdenes).
 */
class DisponibilidadInsumoService
{
    /**
     * Proyecta los requerimientos de un conjunto de líneas normalizadas.
     *
     * @param  array<int,array{tipo_producto_id?:int|null,tela_id?:int|null,cantidad?:int|float}>  $lineas
     * @return array{items:array<int,array<string,mixed>>,hay_faltantes:bool,hay_alertas:bool}
     */
    public function proyectar(array $lineas): array
    {
        $tipoIds = collect($lineas)
            ->pluck('tipo_producto_id')
            ->filter()
            ->unique()
            ->values();

        if ($tipoIds->isEmpty()) {
            return $this->vacio();
        }

        $tipos = TipoProducto::with('insumosDefault')
            ->whereIn('id', $tipoIds)
            ->get()
            ->keyBy('id');

        // Acumula la cantidad requerida por insumo a través de TODAS las líneas
        // (dos líneas con la misma tela suman).
        $requeridos = []; // insumo_id => cantidad total requerida

        foreach ($lineas as $ln) {
            $tipo = $tipos->get($ln['tipo_producto_id'] ?? null);
            // Sin tipo o reventa: no se fabrica, no consume insumos.
            if (!$tipo || !$tipo->requiere_produccion) {
                continue;
            }

            $cantidad = (int) ($ln['cantidad'] ?? 0);
            if ($cantidad <= 0) {
                continue;
            }

            foreach ($tipo->insumosDefault as $insumo) {
                $req = (float) $insumo->pivot->cantidad_estimada * $cantidad;
                if ($req > 0) {
                    $requeridos[$insumo->id] = ($requeridos[$insumo->id] ?? 0) + $req;
                }
            }

            $telaId = $ln['tela_id'] ?? null;
            if ($telaId && $tipo->requiere_tela && $tipo->consumo_tela_por_unidad > 0) {
                $req = (float) $tipo->consumo_tela_por_unidad * $cantidad;
                $requeridos[$telaId] = ($requeridos[$telaId] ?? 0) + $req;
            }
        }

        return $this->compararContraStock($requeridos);
    }

    /**
     * Proyecta a partir de un mapa insumo_id => cantidad ya materializada (p. ej.
     * los insumos reales de las órdenes de producción, que son editables y no
     * dependen del BOM). Devuelve el MISMO shape que proyectar().
     *
     * @param  array<int,float>  $requeridos
     * @return array{items:array<int,array<string,mixed>>,hay_faltantes:bool,hay_alertas:bool}
     */
    public function proyectarInsumos(array $requeridos): array
    {
        return $this->compararContraStock($requeridos);
    }

    /**
     * Compara un mapa insumo_id => cantidad requerida contra el stock actual.
     * Clasifica cada insumo inventariable en: falta / ajustado / ok.
     *
     * @param  array<int,float>  $requeridos
     */
    private function compararContraStock(array $requeridos): array
    {
        if (empty($requeridos)) {
            return $this->vacio();
        }

        $insumos = Insumo::whereIn('id', array_keys($requeridos))->get()->keyBy('id');

        $items = [];
        $hayFaltantes = false;
        $hayAlertas = false;

        foreach ($requeridos as $id => $requerido) {
            $insumo = $insumos->get($id);

            // No inventariable (o inexistente): no mueve stock → no se avisa.
            if (!$insumo || !$insumo->is_inventoriable) {
                continue;
            }

            $stock     = (float) $insumo->stock_actual;
            $stockMin  = (float) $insumo->stock_minimo;
            $requerido = round((float) $requerido, 2);
            $faltante  = round(max(0, $requerido - $stock), 2);
            $restante  = round($stock - $requerido, 2);

            if ($faltante > 0.0001) {
                $estado = 'falta';            // no alcanza: hay que comprar
                $hayFaltantes = true;
            } elseif ($stockMin > 0 && $restante < $stockMin) {
                $estado = 'ajustado';         // alcanza pero deja bajo el mínimo
                $hayAlertas = true;
            } else {
                $estado = 'ok';
            }

            $items[] = [
                'insumo_id'    => (int) $id,
                'nombre'       => $insumo->nombre,
                'codigo'       => $insumo->codigo,
                'unidad'       => $insumo->unidad_medida,
                'requerido'    => $requerido,
                'stock'        => $stock,
                'stock_minimo' => $stockMin,
                'faltante'     => $faltante,
                'restante'     => $restante,
                'estado'       => $estado,
            ];
        }

        // Orden de prioridad para la UI: faltantes, luego ajustados, luego ok.
        $rank = ['falta' => 0, 'ajustado' => 1, 'ok' => 2];
        usort($items, function ($a, $b) use ($rank) {
            return [$rank[$a['estado']], $a['nombre']] <=> [$rank[$b['estado']], $b['nombre']];
        });

        return [
            'items'         => $items,
            'hay_faltantes' => $hayFaltantes,
            'hay_alertas'   => $hayAlertas,
        ];
    }

    private function vacio(): array
    {
        return ['items' => [], 'hay_faltantes' => false, 'hay_alertas' => false];
    }
}
