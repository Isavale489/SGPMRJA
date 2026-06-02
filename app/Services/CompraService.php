<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use Illuminate\Support\Facades\DB;

class CompraService
{
    public function registrar(array $data, int $userId): Compra
    {
        return DB::transaction(function () use ($data, $userId) {
            $items = $data['items'];

            $subtotal = collect($items)->sum(
                fn($item) => $item['cantidad'] * $item['costo_unitario']
            );
            $iva    = round($subtotal * (($data['iva_porcentaje'] ?? 0) / 100), 2);
            $total  = $subtotal + $iva;

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
                'estado'            => 'recibida',
            ]);

            foreach ($items as $item) {
                CompraDetalle::create([
                    'compra_id'      => $compra->id,
                    'insumo_id'      => $item['insumo_id'],
                    'cantidad'       => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'subtotal'       => $item['cantidad'] * $item['costo_unitario'],
                ]);

                // lockForUpdate evita race conditions si dos compras procesan el mismo insumo simultáneamente
                $insumo        = Insumo::lockForUpdate()->findOrFail($item['insumo_id']);
                $stockAnterior = (float) $insumo->stock_actual;
                $stockNuevo    = $stockAnterior + (float) $item['cantidad'];

                $insumo->update([
                    'stock_actual'   => $stockNuevo,
                    'costo_unitario' => $item['costo_unitario'],
                ]);

                MovimientoInsumo::create([
                    'insumo_id'       => $item['insumo_id'],
                    'tipo_movimiento' => 'Entrada',
                    'cantidad'        => $item['cantidad'],
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stockNuevo,
                    'motivo'          => 'Compra #' . $compra->id . ' — Fact: ' . ($data['numero_factura'] ?? 'S/N'),
                    'created_by'      => $userId,
                ]);
            }

            return $compra;
        });
    }

    public function anular(Compra $compra, int $userId): void
    {
        if ($compra->estado === 'anulada') {
            throw new \RuntimeException('La compra ya se encuentra anulada.');
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

            $compra->update(['estado' => 'anulada']);
        });
    }
}
