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
            [$subtotal, $iva, $total] = $this->calcularTotales($data['items'], $data['iva_porcentaje'] ?? 0);

            $compra = Compra::create([
                'proveedor_id'      => $data['proveedor_id'],
                'user_id'           => $userId,
                'numero_factura'    => $data['numero_factura'] ?? null,
                'fecha_compra'      => $data['fecha_compra'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'tipo_pago'         => $data['tipo_pago'],
                'subtotal'          => $subtotal,
                'iva'               => $iva,
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
            [$subtotal, $iva, $total] = $this->calcularTotales($data['items'], $data['iva_porcentaje'] ?? 0);

            $compra->update([
                'proveedor_id'      => $data['proveedor_id'],
                'numero_factura'    => $data['numero_factura'] ?? null,
                'fecha_compra'      => $data['fecha_compra'],
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'tipo_pago'         => $data['tipo_pago'],
                'subtotal'          => $subtotal,
                'iva'               => $iva,
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
            $compra->load('detalles');

            foreach ($compra->detalles as $detalle) {
                $insumo        = Insumo::lockForUpdate()->findOrFail($detalle->insumo_id);
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
            $compra->load('detalles');

            foreach ($compra->detalles as $detalle) {
                $insumo        = Insumo::lockForUpdate()->findOrFail($detalle->insumo_id);
                $stockAnterior = (float) $insumo->stock_actual;
                $stockNuevo    = max(0, $stockAnterior - (float) $detalle->cantidad);

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
                'fecha_vencimiento' => null,
                'tipo_pago'         => $compra->tipo_pago,
                'subtotal'          => $compra->subtotal,
                'iva'               => $compra->iva,
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

    private function calcularTotales(array $items, float $ivaPorcentaje): array
    {
        $subtotal = collect($items)->sum(fn($i) => $i['cantidad'] * $i['costo_unitario']);
        $iva      = round($subtotal * ($ivaPorcentaje / 100), 2);
        return [$subtotal, $iva, $subtotal + $iva];
    }

    private function sincronizarDetalles(Compra $compra, array $items): void
    {
        foreach ($items as $item) {
            CompraDetalle::create([
                'compra_id'      => $compra->id,
                'insumo_id'      => $item['insumo_id'],
                'cantidad'       => $item['cantidad'],
                'costo_unitario' => $item['costo_unitario'],
                'subtotal'       => $item['cantidad'] * $item['costo_unitario'],
            ]);
        }
    }
}
