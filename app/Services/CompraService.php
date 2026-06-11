<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use Illuminate\Support\Facades\DB;

class CompraService
{
    /**
     * Crea la compra en estado 'borrador'.
     * No genera movimientos de insumo — eso ocurre en procesar().
     */
    public function registrar(array $data, int $userId): Compra
    {
        return DB::transaction(function () use ($data, $userId) {
            $tasa = $this->tasaIva();
            [$subtotal, $iva, $total] = $this->calcularTotales($data['items'], $tasa);

            $compra = Compra::create([
                'proveedor_id'      => $data['proveedor_id'],
                'user_id'           => $userId,
                'numero_factura'    => $data['numero_factura'] ?? null,
                'fecha_compra'      => $data['fecha_compra'],
                'subtotal'          => $subtotal,
                'iva'               => $iva,
                'iva_porcentaje'    => $tasa,
                'total'             => $total,
                'observaciones'     => $data['observaciones'] ?? null,
                'estado'            => 'borrador',
            ]);

            $this->sincronizarDetalles($compra, $data['items']);

            return $compra;
        });
    }

    /**
     * Actualiza cabecera y detalles de una compra en estado 'borrador'.
     * No genera movimientos de insumo.
     */
    public function actualizar(Compra $compra, array $data): void
    {
        if ($compra->estado !== 'borrador') {
            throw new \RuntimeException('Solo se pueden editar compras en estado borrador.');
        }

        DB::transaction(function () use ($compra, $data) {
            $tasa = $this->tasaIva();
            [$subtotal, $iva, $total] = $this->calcularTotales($data['items'], $tasa);

            $compra->update([
                'proveedor_id'      => $data['proveedor_id'],
                'numero_factura'    => $data['numero_factura'] ?? null,
                'fecha_compra'      => $data['fecha_compra'],
                'subtotal'          => $subtotal,
                'iva'               => $iva,
                'iva_porcentaje'    => $tasa,
                'total'             => $total,
                'observaciones'     => $data['observaciones'] ?? null,
            ]);

            $compra->detalles()->delete();
            $this->sincronizarDetalles($compra, $data['items']);
        });
    }

    /**
     * Procesa un borrador: cambia estado a 'recibida' y genera movimientos de insumo.
     */
    public function procesar(Compra $compra, int $userId): void
    {
        if ($compra->estado !== 'borrador') {
            throw new \RuntimeException('Solo se pueden procesar compras en estado borrador.');
        }

        DB::transaction(function () use ($compra, $userId) {
            // Orden de lock estable (por insumo_id) para evitar deadlocks entre
            // compras que comparten insumos y los recorren en distinto orden.
            $compra->load(['detalles' => fn($q) => $q->orderBy('insumo_id')]);

            foreach ($compra->detalles as $detalle) {
                $insumo        = $this->insumoBloqueado($detalle->insumo_id);
                $stockAnterior = (float) $insumo->stock_actual;
                $stockNuevo    = $stockAnterior + (float) $detalle->cantidad;

                $insumo->update([
                    'stock_actual'   => $stockNuevo,
                    'costo_unitario' => $detalle->costo_unitario,
                ]);

                MovimientoInsumo::create([
                    'insumo_id'       => $detalle->insumo_id,
                    'tipo_movimiento' => 'Entrada',
                    'cantidad'        => $detalle->cantidad,
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stockNuevo,
                    'motivo'          => 'Compra #' . $compra->id . ' — Fact: ' . ($compra->numero_factura ?? 'S/N'),
                    'created_by'      => $userId,
                ]);
            }

            $compra->update(['estado' => 'recibida']);
        });
    }

    /**
     * Anula una compra recibida: revierte stock y registra movimiento de salida.
     */
    public function anular(Compra $compra, int $userId): void
    {
        if ($compra->estado !== 'recibida') {
            throw new \RuntimeException('Solo se pueden anular compras en estado recibida.');
        }

        DB::transaction(function () use ($compra, $userId) {
            // Mismo orden de lock estable (por insumo_id) que procesar(), para
            // evitar deadlocks entre operaciones concurrentes sobre el inventario.
            $compra->load([
                'detalles' => fn($q) => $q->orderBy('insumo_id'),
                'detalles.insumo',
            ]);

            foreach ($compra->detalles as $detalle) {
                $insumo        = $this->insumoBloqueado($detalle->insumo_id);
                $stockAnterior = (float) $insumo->stock_actual;
                $cantidad      = (float) $detalle->cantidad;

                // No se puede revertir mercancía que ya salió de inventario: si el
                // stock actual no alcanza, bloqueamos en vez de clampar a 0 en silencio.
                if ($stockAnterior < $cantidad) {
                    throw new \RuntimeException(
                        "No se puede anular: el insumo «{$insumo->nombre}» tiene "
                        . rtrim(rtrim(number_format($stockAnterior, 2), '0'), '.') . ' en existencia, '
                        . 'menos que las ' . rtrim(rtrim(number_format($cantidad, 2), '0'), '.')
                        . ' unidades de esta compra. Parte del stock ya fue consumido; '
                        . 'realizá un ajuste de inventario manual.'
                    );
                }

                $stockNuevo = $stockAnterior - $cantidad;

                $insumo->update(['stock_actual' => $stockNuevo]);

                MovimientoInsumo::create([
                    'insumo_id'       => $detalle->insumo_id,
                    'tipo_movimiento' => 'Salida',
                    'cantidad'        => $detalle->cantidad,
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stockNuevo,
                    'motivo'          => 'Anulación Compra #' . $compra->id,
                    'created_by'      => $userId,
                ]);
            }

            $compra->update([
                'estado'          => 'anulada',
                'anulado_por_id'  => $userId,
                'fecha_anulacion' => now(),
            ]);
        });
    }

    /**
     * Clona una compra anulada como nuevo borrador (sin número de factura, fecha hoy).
     */
    public function clonar(Compra $compra, int $userId): Compra
    {
        if ($compra->estado !== 'anulada') {
            throw new \RuntimeException('Solo se pueden clonar compras anuladas.');
        }

        if ($compra->clonada) {
            throw new \RuntimeException('Esta compra ya fue clonada anteriormente y no puede volver a clonarse.');
        }

        return DB::transaction(function () use ($compra, $userId) {
            $compra->load('detalles');

            $nueva = Compra::create([
                'proveedor_id'      => $compra->proveedor_id,
                'user_id'           => $userId,
                'numero_factura'    => null,
                'fecha_compra'      => now()->toDateString(),
                'subtotal'          => $compra->subtotal,
                'iva'               => $compra->iva,
                'iva_porcentaje'    => $compra->iva_porcentaje,
                'total'             => $compra->total,
                'observaciones'     => 'Clonada de Compra #' . $compra->id . ($compra->observaciones ? '. ' . $compra->observaciones : ''),
                'estado'            => 'borrador',
            ]);

            foreach ($compra->detalles as $detalle) {
                CompraDetalle::create([
                    'compra_id'      => $nueva->id,
                    'insumo_id'      => $detalle->insumo_id,
                    'cantidad'       => $detalle->cantidad,
                    'costo_unitario' => $detalle->costo_unitario,
                    'aplica_iva'     => $detalle->aplica_iva,
                    'subtotal'       => $detalle->subtotal,
                ]);
            }

            $compra->update(['clonada' => true]);

            return $nueva;
        });
    }

    /**
     * Elimina físicamente un borrador (nunca generó stock; arrastra sus
     * detalles por cascade). Solo aplica a borradores.
     */
    public function eliminar(Compra $compra): void
    {
        if ($compra->estado !== 'borrador') {
            throw new \RuntimeException('Solo se pueden eliminar compras en estado borrador.');
        }

        DB::transaction(function () use ($compra) {
            $compra->detalles()->delete();
            $compra->forceDelete();
        });
    }

    // ── Helpers privados ─────────────────────────────────────────────────────

    /**
     * Recupera y bloquea (lockForUpdate) un insumo para mover su stock.
     * Incluye soft-deleted para poder nombrar el insumo en el mensaje de error
     * en vez de un 500 opaco si fue inhabilitado tras crear el borrador.
     */
    private function insumoBloqueado(int $insumoId): Insumo
    {
        $insumo = Insumo::withTrashed()->lockForUpdate()->find($insumoId);

        if (!$insumo) {
            throw new \RuntimeException('Uno de los insumos de la compra ya no existe en el sistema.');
        }
        if ($insumo->trashed()) {
            throw new \RuntimeException("El insumo «{$insumo->nombre}» está inhabilitado. Habilítalo antes de procesar o anular esta compra.");
        }

        return $insumo;
    }

    /**
     * Tasa de IVA general vigente (%). Centralizada en config/impuestos.php.
     */
    private function tasaIva(): float
    {
        return (float) config('impuestos.iva', 16);
    }

    /**
     * Calcula totales con IVA por línea: solo las líneas gravables
     * (aplica_iva = true) suman a la base sobre la que se aplica la tasa.
     */
    private function calcularTotales(array $items, float $tasaIva): array
    {
        $subtotal    = 0.0;
        $baseGravada = 0.0;

        foreach ($items as $item) {
            $lineaSubtotal = (float) $item['cantidad'] * (float) $item['costo_unitario'];
            $subtotal     += $lineaSubtotal;
            if ($this->lineaGravada($item)) {
                $baseGravada += $lineaSubtotal;
            }
        }

        $iva = round($baseGravada * ($tasaIva / 100), 2);

        return [round($subtotal, 2), $iva, round($subtotal + $iva, 2)];
    }

    private function sincronizarDetalles(Compra $compra, array $items): void
    {
        foreach ($items as $item) {
            CompraDetalle::create([
                'compra_id'      => $compra->id,
                'insumo_id'      => $item['insumo_id'],
                'cantidad'       => $item['cantidad'],
                'costo_unitario' => $item['costo_unitario'],
                'aplica_iva'     => $this->lineaGravada($item),
                'subtotal'       => $item['cantidad'] * $item['costo_unitario'],
            ]);
        }
    }

    /**
     * Una línea es gravable salvo que venga explícitamente marcada como exenta.
     * Default true para mantener compatibilidad si el flag no se envía.
     */
    private function lineaGravada(array $item): bool
    {
        return filter_var($item['aplica_iva'] ?? true, FILTER_VALIDATE_BOOLEAN);
    }
}
