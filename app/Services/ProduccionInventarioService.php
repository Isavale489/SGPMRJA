<?php

namespace App\Services;

use App\Exceptions\StockInsuficienteException;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\OrdenProduccion;
use Illuminate\Support\Facades\DB;

/**
 * Movimientos de inventario asociados a las Órdenes de Producción.
 *
 * Regla de negocio (confección textil):
 *  - El stock se descuenta al CREAR la OP (la materia prima se compromete a la
 *    orden). Solo aplica a insumos inventariables (is_inventoriable = true).
 *  - Reposición SOLO en cancelación temprana ('Pendiente'): la tela aún no se
 *    cortó. En cancelación tardía ('En Proceso' o superior) la tela ya se cortó
 *    y se considera merma → no se repone (lo decide quien llama, no este método).
 *
 * Reutiliza el mismo patrón de CompraService::procesar()/anular()
 * (lockForUpdate + MovimientoInsumo + transacción).
 */
class ProduccionInventarioService
{
    /**
     * Valida que haya stock para todos los insumos inventariables de la orden y,
     * si lo hay, descuenta el stock generando un MovimientoInsumo de 'Salida' por
     * cada uno. La cantidad comprometida es la `cantidad_estimada` del pivot.
     *
     * @throws StockInsuficienteException si algún insumo inventariable no alcanza.
     */
    public function validarYDescontar(OrdenProduccion $orden, int $userId): void
    {
        DB::transaction(function () use ($orden, $userId) {
            $orden->load('insumos');

            $faltantes = [];

            foreach ($orden->insumos as $insumo) {
                if (!$insumo->is_inventoriable) {
                    continue; // servicios / no inventariables no mueven stock
                }

                $cantidad = (float) $insumo->pivot->cantidad_estimada;
                if ($cantidad <= 0) {
                    continue;
                }

                // Bloqueo pesimista: evita carreras al descontar el mismo insumo.
                $bloqueado     = Insumo::lockForUpdate()->findOrFail($insumo->id);
                $stockAnterior = (float) $bloqueado->stock_actual;

                if ($stockAnterior + 0.0001 < $cantidad) {
                    $faltantes[] = sprintf(
                        '%s (necesita %s %s, disponible %s)',
                        $bloqueado->nombre,
                        rtrim(rtrim(number_format($cantidad, 2, '.', ''), '0'), '.'),
                        $bloqueado->unidad_medida,
                        rtrim(rtrim(number_format($stockAnterior, 2, '.', ''), '0'), '.')
                    );
                    continue;
                }

                $stockNuevo = $stockAnterior - $cantidad;
                $bloqueado->update(['stock_actual' => $stockNuevo]);

                MovimientoInsumo::create([
                    'insumo_id'       => $bloqueado->id,
                    'tipo_movimiento' => 'Salida',
                    'cantidad'        => $cantidad,
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stockNuevo,
                    'motivo'          => 'Consumo de producción — OP #' . $orden->id,
                    'created_by'      => $userId,
                ]);

                // El insumo queda comprometido a la orden (utilizado = estimado).
                $orden->insumos()->updateExistingPivot($insumo->id, [
                    'cantidad_utilizada' => $cantidad,
                ]);
            }

            if (!empty($faltantes)) {
                throw new StockInsuficienteException(
                    'Stock insuficiente para generar la orden de producción: '
                    . implode('; ', $faltantes)
                    . '. Registra una compra para reponer el inventario.'
                );
            }
        });
    }

    /**
     * Repone el stock comprometido por una OP (cancelación temprana). Genera un
     * MovimientoInsumo de 'Entrada' por cada insumo inventariable, devolviendo la
     * cantidad que se había descontado al crear la orden.
     */
    public function reponer(OrdenProduccion $orden, int $userId): void
    {
        DB::transaction(function () use ($orden, $userId) {
            $orden->load('insumos');

            foreach ($orden->insumos as $insumo) {
                if (!$insumo->is_inventoriable) {
                    continue;
                }

                $cantidad = (float) $insumo->pivot->cantidad_estimada;
                if ($cantidad <= 0) {
                    continue;
                }

                $bloqueado     = Insumo::lockForUpdate()->findOrFail($insumo->id);
                $stockAnterior = (float) $bloqueado->stock_actual;
                $stockNuevo    = $stockAnterior + $cantidad;

                $bloqueado->update(['stock_actual' => $stockNuevo]);

                MovimientoInsumo::create([
                    'insumo_id'       => $bloqueado->id,
                    'tipo_movimiento' => 'Entrada',
                    'cantidad'        => $cantidad,
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stockNuevo,
                    'motivo'          => 'Reposición por cancelación OP #' . $orden->id,
                    'created_by'      => $userId,
                ]);
            }
        });
    }
}
