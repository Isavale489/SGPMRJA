<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\DetalleCotizacionBordado;
use App\Models\DetallePedido;
use App\Models\DetallePedidoBordado;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\TasaCambio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CotizacionService
{
    public function __construct(
        private BordadoPricingService $bordadoPricingService,
        private ProductoService $productoService
    ) {
    }

    /**
     * Crear una nueva cotización con sus detalles.
     */
    public function crear(array $data): Cotizacion
    {
        $cotizacion = DB::transaction(function () use ($data) {
            $total = $this->calcularTotal($data['productos']);

            $cotizacion = Cotizacion::create([
                'cliente_id'          => $data['cliente_id'],
                'fecha_cotizacion'    => $data['fecha_cotizacion'],
                'fecha_validez'       => $data['fecha_validez']
                    ?? \Carbon\Carbon::parse($data['fecha_cotizacion'])->addDays(15)->toDateString(),
                'estado'              => 'Pendiente',
                'total'               => $total,
                'tasa_cambio_valor'   => TasaCambio::obtenerValorUsd(),
                'notas'               => $data['notas'] ?? null,
                'condiciones_terminos' => $data['condiciones_terminos'] ?? null,
                'user_id'             => Auth::id(),
            ]);

            $this->crearDetalles($cotizacion, $data['productos']);

            return $cotizacion;
        });

        Log::info('Cotización creada', [
            'cotizacion_id' => $cotizacion->id,
            'cliente_id' => $cotizacion->cliente_id,
            'total' => $cotizacion->total,
            'user_id' => Auth::id(),
        ]);

        return $cotizacion;
    }

    /**
     * Actualizar una cotización existente con sus detalles.
     */
    public function actualizar(Cotizacion $cotizacion, array $data): void
    {
        DB::transaction(function () use ($cotizacion, $data) {
            // Eliminar detalles existentes
            $cotizacion->productos()->delete();

            $total = $this->calcularTotal($data['productos']);

            $cotizacion->update([
                'cliente_id'           => $data['cliente_id'],
                'fecha_cotizacion'     => $data['fecha_cotizacion'],
                'fecha_validez'        => $data['fecha_validez'] ?? null,
                'estado'               => $data['estado'],
                'total'                => $total,
                'tasa_cambio_valor'    => TasaCambio::obtenerValorUsd(),
                'notas'                => $data['notas'] ?? null,
                'condiciones_terminos' => $data['condiciones_terminos'] ?? null,
                // NO se sobrescribe user_id: queda fijo como el creador original.
            ]);

            $this->crearDetalles($cotizacion, $data['productos']);
        });

        Log::info('Cotización actualizada', [
            'cotizacion_id' => $cotizacion->id,
            'total' => $cotizacion->total,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Reactivar una cotización vencida, reseteando su validez a 15 días desde hoy.
     */
    public function reactivar(Cotizacion $cotizacion): void
    {
        if ($cotizacion->estado !== 'Vencida') {
            throw new \InvalidArgumentException('Solo se pueden reactivar cotizaciones en estado Vencida.');
        }

        $cotizacion->update([
            'estado'              => 'Pendiente',
            'fecha_validez'       => now()->addDays(15)->toDateString(),
            'tasa_cambio_valor'   => TasaCambio::obtenerValorUsd(),
        ]);

        Log::info('Cotización reactivada', [
            'cotizacion_id'    => $cotizacion->id,
            'nueva_fecha_validez' => $cotizacion->fecha_validez,
            'user_id'          => Auth::id(),
        ]);
    }

    /**
     * Convertir una cotización aprobada a pedido.
     *
     * @return Pedido El pedido creado
     * @throws \Exception Si la cotización no puede ser convertida
     */
    public function convertirAPedido(Cotizacion $cotizacion): Pedido
    {
        return DB::transaction(function () use ($cotizacion) {
            // 1. Re-obtener la cotización con bloqueo pesimista (SELECT ... FOR UPDATE)
            $cotizacion = Cotizacion::lockForUpdate()->findOrFail($cotizacion->id);

            // 2. Validar estado DENTRO del bloqueo (previene race conditions)
            if ($cotizacion->estado !== 'Aprobada') {
                throw new \InvalidArgumentException('Solo se pueden convertir cotizaciones con estado Aprobada.');
            }

            // 3. Verificar que no exista ya un pedido asociado (doble protección + índice único en BD)
            if ($cotizacion->yaFueConvertida()) {
                throw new \InvalidArgumentException('Esta cotización ya fue convertida a pedido anteriormente.');
            }

            // 4. Eager-load detalles y validar que no estén vacíos
            $cotizacion->load('productos.bordados');
            if ($cotizacion->productos->isEmpty()) {
                throw new \RuntimeException('La cotización no tiene productos. No se puede crear un pedido vacío.');
            }

            // 5. Crear el Pedido con datos de la cotización
            $pedido = Pedido::create([
                'cotizacion_id' => $cotizacion->id,
                'cliente_id' => $cotizacion->cliente_id,
                'fecha_pedido' => now(),
                'total' => $cotizacion->total,
                'abono' => 0,
                'prioridad' => $cotizacion->prioridad ?? 'Normal',
                'estado' => 'Pendiente',
                'user_id' => Auth::id(),
            ]);

            // 6. Transferir TODOS los detalles (incluyendo color, talla, precio cotizado)
            foreach ($cotizacion->productos as $detalle) {
                $detallePedido = DetallePedido::create([
                    'pedido_id' => $pedido->id,
                    'producto_id' => $detalle->producto_id,
                    'tela_snapshot' => $detalle->tela_snapshot,
                    'atributos_snapshot' => $detalle->atributos_snapshot,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descripcion' => $detalle->descripcion,
                    'lleva_bordado' => $detalle->lleva_bordado ?? false,
                    'color_id' => $detalle->color_id,
                    'talla_id' => $detalle->talla_id,
                ]);

                foreach ($detalle->bordados as $index => $bordado) {
                    DetallePedidoBordado::create([
                        'detalle_pedido_id' => $detallePedido->id,
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

            // 7. Marcar como convertida (dentro de la misma transacción)
            $cotizacion->update(['estado' => 'Convertida']);

            Log::info('Cotización convertida a pedido', [
                'cotizacion_id' => $cotizacion->id,
                'pedido_id' => $pedido->id,
                'total' => $pedido->total,
                'productos' => $cotizacion->productos->count(),
                'user_id' => Auth::id(),
            ]);

            return $pedido;
        });
    }

    /**
     * Calcular total de la cotización sumando (precio_base + recargos de bordado) × cantidad.
     */
    private function calcularTotal(array $productos): float
    {
        $total = 0;
        foreach ($productos as $item) {
            $producto = Producto::find($item['producto_id']);
            $precioBase = isset($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : (float) ($producto->precio_base ?? 0);

            $bordados = $this->bordadoPricingService->normalizeBordados($item);
            $precioUnitarioFinal = $this->bordadoPricingService->calcularPrecioUnitarioFinal($precioBase, $bordados);

            $total += $precioUnitarioFinal * (int) $item['cantidad'];
        }
        return $total;
    }

    /**
     * Crear los detalles de cotización (líneas de producto).
     */
    private function crearDetalles(Cotizacion $cotizacion, array $productos): void
    {
        foreach ($productos as $item) {
            $producto = Producto::with('tela')->find($item['producto_id']);
            $precioBase = isset($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : (float) ($producto->precio_base ?? 0);
            $bordados = $this->bordadoPricingService->normalizeBordados($item);
            $precioUnitarioFinal = $this->bordadoPricingService->calcularPrecioUnitarioFinal($precioBase, $bordados);
            $snapshots = $this->productoService->buildSnapshotsParaDetalle($producto);

            $detalle = DetalleCotizacion::create([
                'cotizacion_id' => $cotizacion->id,
                'producto_id' => $item['producto_id'],
                'tela_snapshot' => $snapshots['tela_snapshot'],
                'atributos_snapshot' => $snapshots['atributos_snapshot'],
                'cantidad' => $item['cantidad'],
                'descripcion' => $item['descripcion'] ?? null,
                'lleva_bordado' => $item['lleva_bordado'] ?? false,
                'color_id' => $item['color_id'] ?? null,
                'talla_id' => $item['talla_id'] ?? null,
                'precio_unitario' => $precioUnitarioFinal,
            ]);

            foreach ($bordados as $bordado) {
                $logoId = $bordado['logo_id'] ?? null;
                DetalleCotizacionBordado::create([
                    'detalle_cotizacion_id' => $detalle->id,
                    'ubicacion_bordado_id' => $bordado['ubicacion_bordado_id'] ?? null,
                    'logo_id' => $logoId,
                    'nombre_aplicado' => trim((string) ($bordado['nombre_aplicado'] ?? '')),
                    'nombre_logo_aplicado' => $this->bordadoPricingService->resolverNombreLogoSnapshot($logoId),
                    'es_personalizada' => (bool) ($bordado['es_personalizada'] ?? false),
                    'cantidad' => max(1, (int) ($bordado['cantidad'] ?? 1)),
                    'precio_aplicado' => (float) ($bordado['precio_aplicado'] ?? 0),
                    'orden' => (int) ($bordado['orden'] ?? 0),
                ]);
            }
        }
    }
}
