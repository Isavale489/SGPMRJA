<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\DetallePedido;
use App\Models\DetallePedidoBordado;
use App\Models\PagoPedido;
use App\Models\Pedido;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pedidos. Regla de negocio: un pedido SOLO nace de una cotización Aprobada y
 * vigente; sus líneas (productos, tallas, precios, bordados) se copian en el
 * servidor desde la cotización, nunca desde lo que envía el navegador. Después
 * de creado se editan los pagos, la fecha de entrega y la prioridad; las líneas
 * quedan congeladas (para cambiarlas se edita la cotización antes de convertir).
 */
class PedidoService
{
    /**
     * Crear el pedido desde su cotización, con el abono mínimo ya registrado.
     *
     * @param  array{cotizacion_id:int, fecha_entrega_estimada:string, prioridad:string, pagos:array}  $data
     *
     * @throws \InvalidArgumentException si la cotización no se puede convertir o el abono no alcanza.
     */
    public function crearDesdeCotizacion(array $data): Pedido
    {
        // Si la cotización venció, la transacción CONFIRMA el estado «Vencida» y la
        // excepción se lanza después (dentro del closure haría rollback).
        $venceEl = null;

        $pedido = DB::transaction(function () use ($data, &$venceEl) {
            $cotizacion = Cotizacion::lockForUpdate()->findOrFail($data['cotizacion_id']);

            if ($cotizacion->estado !== 'Aprobada') {
                throw new \InvalidArgumentException('Solo se pueden convertir cotizaciones con estado Aprobada.');
            }
            if ($cotizacion->estaVencidaPorVigencia()) {
                $cotizacion->update(['estado' => 'Vencida']);
                $venceEl = $cotizacion->fechaLimiteVigencia();

                return null;
            }
            if ($cotizacion->yaFueConvertida()) {
                throw new \InvalidArgumentException('Esta cotización ya tiene un pedido asociado.');
            }
            // Sin cliente no hay proceso: uno inhabilitado no recibe pedidos (decisión de producto).
            $cliente = $cotizacion->cliente()->withTrashed()->first();
            if (! $cliente || $cliente->trashed() || ! $cliente->estatus) {
                throw new \InvalidArgumentException('El cliente de esta cotización está inhabilitado: rehabilítalo antes de crear el pedido.');
            }
            $cotizacion->load('productos.bordados');
            if ($cotizacion->productos->isEmpty()) {
                throw new \InvalidArgumentException('La cotización no tiene productos: no se puede crear un pedido vacío.');
            }

            $total = (float) $cotizacion->total;
            $this->validarPagos($total, $data['pagos'] ?? []);

            $pedido = Pedido::create([
                'cotizacion_id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'fecha_pedido' => now()->toDateString(),
                'fecha_entrega_estimada' => $data['fecha_entrega_estimada'],
                'estado' => 'Pendiente',
                'total' => $total,
                'abono' => 0,
                'prioridad' => $data['prioridad'] ?? $cotizacion->prioridad ?? 'Normal',
                'user_id' => Auth::id(),
            ]);

            $this->copiarLineas($cotizacion, $pedido);
            $this->syncPagos($pedido, $data['pagos'] ?? []);
            $cotizacion->update(['estado' => 'Convertida']);

            return $pedido;
        });

        if ($venceEl !== null) {
            throw new \InvalidArgumentException(
                'La cotización venció: su validez expiró el '.$venceEl->format('d/m/Y')
                .'. Reactívala para actualizar los precios antes de convertirla a pedido.'
            );
        }

        Log::info('Pedido creado desde cotización', [
            'pedido_id' => $pedido->id,
            'cotizacion_id' => $pedido->cotizacion_id,
            'total' => $pedido->total,
            'user_id' => Auth::id(),
        ]);

        return $pedido;
    }

    /**
     * Pagos, fecha de entrega y prioridad de un pedido existente. Las líneas no
     * se tocan. Un pedido Completado solo recibe pagos (p. ej. el saldo a la
     * entrega); uno Cancelado no se edita.
     *
     * @throws \InvalidArgumentException
     */
    public function actualizar(Pedido $pedido, array $data): void
    {
        DB::transaction(function () use ($pedido, $data) {
            Pedido::whereKey($pedido->id)->lockForUpdate()->first();
            $pedido->refresh();

            if ($pedido->estado === 'Cancelado') {
                throw new \InvalidArgumentException('No se puede editar un pedido cancelado.');
            }

            $this->validarPagos((float) $pedido->total, $data['pagos'] ?? [], $pedido);

            if ($pedido->estado !== 'Completado' && isset($data['fecha_entrega_estimada'], $data['prioridad'])) {
                $pedido->update([
                    'fecha_entrega_estimada' => $data['fecha_entrega_estimada'],
                    'prioridad' => $data['prioridad'],
                ]);
            }
            $this->syncPagos($pedido, $data['pagos'] ?? []);
        });

        $pedido->recalcularEstado();

        Log::info('Pedido actualizado', [
            'pedido_id' => $pedido->id,
            'abono' => $pedido->fresh()->abono,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Elimina (soft delete) un pedido y revierte el estado de su cotización de
     * origen. Si la cotización estaba 'Convertida' vuelve a 'Aprobada' para poder
     * re-convertirla; además se desliga el cotizacion_id del pedido borrado para
     * liberar el índice único pedido_cotizacion_id_unique (las filas soft-deleted
     * lo siguen ocupando; MySQL sí admite múltiples NULL).
     */
    public function eliminar(Pedido $pedido): void
    {
        DB::transaction(function () use ($pedido) {
            // Con el pedido bloqueado: una orden de producción creada mientras
            // tanto (store la crea con este mismo bloqueo) impide eliminarlo.
            Pedido::whereKey($pedido->id)->lockForUpdate()->first();
            if ($pedido->tieneProduccionActiva()) {
                throw new \DomainException('No se puede eliminar un pedido con producción iniciada. Cancela primero sus órdenes de producción.');
            }

            if ($pedido->cotizacion_id) {
                $cotizacion = Cotizacion::lockForUpdate()->find($pedido->cotizacion_id);
                if ($cotizacion && $cotizacion->estado === 'Convertida') {
                    $cotizacion->update(['estado' => 'Aprobada']);
                }
                // Liberar el slot del índice único antes del soft delete.
                $pedido->update(['cotizacion_id' => null]);
            }

            $pedido->delete();
        });

        Log::info('Pedido eliminado con reversión de cotización', [
            'pedido_id' => $pedido->id,
            'user_id' => Auth::id(),
        ]);
    }

    /** Copia fiel de las líneas de la cotización (snapshots, precio pactado y bordados). */
    private function copiarLineas(Cotizacion $cotizacion, Pedido $pedido): void
    {
        foreach ($cotizacion->productos as $detalle) {
            $linea = DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $detalle->producto_id,
                'tipo_producto_id' => $detalle->tipo_producto_id,
                'tela_snapshot' => $detalle->tela_snapshot,
                'atributos_snapshot' => $detalle->atributos_snapshot,
                'sku_snapshot' => $detalle->sku_snapshot,
                'cantidad' => $detalle->cantidad,
                'precio_unitario' => $detalle->precio_unitario,
                'descripcion' => $detalle->descripcion,
                'lleva_bordado' => $detalle->lleva_bordado ?? false,
                'color_id' => $detalle->color_id,
                'talla_id' => $detalle->talla_id,
                'genero_id' => $detalle->genero_id,
            ]);

            foreach ($detalle->bordados as $index => $bordado) {
                DetallePedidoBordado::create([
                    'detalle_pedido_id' => $linea->id,
                    'ubicacion_bordado_id' => $bordado->ubicacion_bordado_id,
                    'logo_id' => $bordado->logo_id,
                    'nombre_aplicado' => $bordado->nombre_aplicado,
                    'nombre_logo_aplicado' => $bordado->nombre_logo_aplicado,
                    'es_personalizada' => (bool) $bordado->es_personalizada,
                    'cantidad' => (int) ($bordado->cantidad ?: 1),
                    'precio_aplicado' => (float) $bordado->precio_aplicado,
                    'orden' => (int) $index,
                ]);
            }
        }
    }

    /**
     * Reemplaza los pagos del pedido y recalcula el abono; si con ellos alcanza el
     * mínimo, formaliza el pedido (hito permanente).
     */
    private function syncPagos(Pedido $pedido, array $pagos): void
    {
        $pedido->pagos()->delete();

        foreach ($pagos as $pago) {
            PagoPedido::create([
                'pedido_id' => $pedido->id,
                'metodo' => $pago['metodo'],
                'monto' => $pago['monto'],
                'banco_id' => $pago['metodo'] === 'efectivo' ? null : ($pago['banco_id'] ?? null),
                'referencia' => $pago['metodo'] === 'efectivo' ? null : ($pago['referencia'] ?? null),
            ]);
        }

        $pedido->recalcularAbono();
        $pedido->marcarFormalizacionSiCorresponde();
    }

    /**
     * El abono no pasa del total y cubre el mínimo (% configurable del total).
     * Al crear, el mínimo es estricto. Al editar se tolera el abono que el pedido
     * ya tenía (pedidos legacy con abono bajo), pero nunca menos de eso.
     *
     * @throws \InvalidArgumentException
     */
    private function validarPagos(float $total, array $pagos, ?Pedido $existente = null): void
    {
        $abono = round(array_sum(array_map(fn ($p) => (float) ($p['monto'] ?? 0), $pagos)), 2);

        if ($abono > round($total, 2) + 0.001) {
            throw new \InvalidArgumentException(sprintf('Los pagos ($%s) superan el total del pedido ($%s).', number_format($abono, 2), number_format($total, 2)));
        }

        $minimo = round($total * Pedido::porcentajeAbonoMinimo() / 100, 2);
        if ($existente) {
            $minimo = min($minimo, (float) $existente->abono);
        }
        if ($abono + 0.001 < $minimo) {
            throw new \InvalidArgumentException(sprintf(
                'El abono registrado ($%s) no alcanza el mínimo requerido de $%s (%s%% del total del pedido).',
                number_format($abono, 2),
                number_format($minimo, 2),
                rtrim(rtrim(number_format(Pedido::porcentajeAbonoMinimo(), 2), '0'), '.')
            ));
        }
    }
}
