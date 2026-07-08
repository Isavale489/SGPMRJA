<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PagoPedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use App\Models\DetallePedidoBordado;
use App\Models\Cotizacion;
use App\Models\Insumo;
use App\Models\TipoProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoService
{
    public function __construct(
        private BordadoPricingService $bordadoPricingService,
        private ProductoService $productoService
    ) {
    }

    /**
     * Crear un pedido con sus detalles.
     */
    public function crear(array $data): Pedido
    {
        $pedido = DB::transaction(function () use ($data) {
            $cotizacion = $this->validarCotizacionParaCrearPedido($data['cotizacion_id'] ?? null);
            $total_pedido = $this->calcularTotal($data['productos']);
            $this->validarAbonoMinimo($total_pedido, $data['pagos'] ?? []);

            $pedido = Pedido::create([
                'cotizacion_id' => $data['cotizacion_id'] ?? null,
                'cliente_id' => $data['cliente_id'],
                'fecha_pedido' => $data['fecha_pedido'],
                'fecha_entrega_estimada' => $data['fecha_entrega_estimada'] ?? null,
                'estado' => 'Pendiente',
                'total' => $total_pedido,
                'user_id' => Auth::id(),
                'abono' => 0,
                'prioridad' => $data['prioridad'],
            ]);

            $this->syncPagos($pedido, $data['pagos'] ?? []);
            $this->crearDetalles($pedido, $data['productos']);

            if ($cotizacion) {
                $cotizacion->update(['estado' => 'Convertida']);
            }

            return $pedido;
        });

        Log::info('Pedido creado', [
            'pedido_id' => $pedido->id,
            'cliente_id' => $pedido->cliente_id,
            'total' => $pedido->total,
            'cotizacion_id' => $pedido->cotizacion_id,
            'user_id' => Auth::id(),
        ]);

        return $pedido;
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

    /**
     * Actualizar un pedido existente y sincronizar sus detalles.
     */
    public function actualizar(Pedido $pedido, array $data): void
    {
        DB::transaction(function () use ($data, $pedido) {
            $total_pedido = $this->calcularTotal($data['productos']);
            $this->validarAbonoMinimo($total_pedido, $data['pagos'] ?? [], $pedido);

            // Eliminar detalles anteriores
            $pedido->productos()->delete();

            $pedido->update([
                'cliente_id' => $data['cliente_id'],
                'fecha_pedido' => $data['fecha_pedido'],
                'fecha_entrega_estimada' => $data['fecha_entrega_estimada'] ?? null,
                // 'estado' NO se toca aquí: lo gobierna producción (recalcularEstado)
                // y Cancelar/Reactivar (manual). Ver PedidoController.
                'total' => $total_pedido,
                'prioridad' => $data['prioridad'],
            ]);

            $this->syncPagos($pedido, $data['pagos'] ?? []);
            $this->crearDetalles($pedido, $data['productos']);
        });

        // El estado refleja la realidad de producción (no el formulario)
        $pedido->recalcularEstado();

        Log::info('Pedido actualizado', [
            'pedido_id' => $pedido->id,
            'estado' => $data['estado'],
            'total' => $pedido->fresh()->total,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Calcular el total del pedido.
     * Usa precio_unitario del request si está disponible (ej: viene de una cotización),
     * de lo contrario usa precio_base del catálogo.
     */
    private function calcularTotal(array $productos): float
    {
        $total = 0;
        foreach ($productos as $item) {
            $base = $this->resolverVarianteLinea($item);
            $precioBase = isset($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : $base['precio_base'];

            $bordados = $this->bordadoPricingService->normalizeBordados($item);
            $precioUnitarioFinal = $this->bordadoPricingService->calcularPrecioUnitarioFinal($precioBase, $bordados);

            $total += $precioUnitarioFinal * (int) $item['cantidad'];
        }

        return $total;
    }

    /**
     * Resuelve la base de una línea de pedido, soportando legacy (`producto_id`)
     * o dinámico (`tipo_producto_id` + tela + atributos), igual que en cotización
     * (FEAT-003). Devuelve precio base, ids y snapshots (incl. SKU).
     *
     * @return array{producto_id: ?int, tipo_producto_id: ?int, precio_base: float,
     *               tela_snapshot: ?array, atributos_snapshot: ?array, sku_snapshot: ?string}
     */
    private function resolverVarianteLinea(array $item): array
    {
        if (!empty($item['producto_id'])) {
            $producto = Producto::with('tela')->find($item['producto_id']);
            $snapshots = $this->productoService->buildSnapshotsParaDetalle($producto);

            return [
                'producto_id'        => (int) $item['producto_id'],
                'tipo_producto_id'   => $producto?->tipo_producto_id,
                'precio_base'        => (float) ($producto->precio_base ?? 0),
                'tela_snapshot'      => $snapshots['tela_snapshot'],
                'atributos_snapshot' => $snapshots['atributos_snapshot'],
                'sku_snapshot'       => $snapshots['sku'],
            ];
        }

        $tipo = TipoProducto::find($item['tipo_producto_id']);
        $tela = !empty($item['insumo_tela_id']) ? Insumo::find($item['insumo_tela_id']) : null;
        $snap = $this->productoService->buildSnapshotsDesdeTipo($tipo, $tela, $item['atributo_valor_ids'] ?? []);

        return [
            'producto_id'        => null,
            'tipo_producto_id'   => $tipo->id,
            'precio_base'        => (float) $snap['precio_sugerido'],
            'tela_snapshot'      => $snap['tela_snapshot'],
            'atributos_snapshot' => $snap['atributos_snapshot'],
            'sku_snapshot'       => $snap['sku'],
        ];
    }

    /**
     * Crear los detalles (líneas) de un pedido.
     * Usa precio_unitario del request si disponible, sinó precio_base del catálogo.
     */
    private function crearDetalles(Pedido $pedido, array $productos): void
    {
        foreach ($productos as $item) {
            $base = $this->resolverVarianteLinea($item);
            $precioBase = isset($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : $base['precio_base'];

            $bordados = $this->bordadoPricingService->normalizeBordados($item);
            $precioUnitarioFinal = $this->bordadoPricingService->calcularPrecioUnitarioFinal($precioBase, $bordados);

            $detalle = DetallePedido::create([
                'pedido_id' => $pedido->id,
                'producto_id' => $base['producto_id'],
                'tipo_producto_id' => $base['tipo_producto_id'],
                'tela_snapshot' => $base['tela_snapshot'],
                'atributos_snapshot' => $base['atributos_snapshot'],
                'sku_snapshot' => $base['sku_snapshot'],
                'cantidad' => $item['cantidad'],
                'descripcion' => $item['descripcion'] ?? null,
                'lleva_bordado' => $item['lleva_bordado'] ?? false,
                'color_id' => $item['color_id'] ?? null,
                'talla_id' => $item['talla_id'] ?? null,
                'genero_id' => $item['genero_id'] ?? null,
                'precio_unitario' => $precioUnitarioFinal,
            ]);

            foreach ($bordados as $bordado) {
                $logoId = $bordado['logo_id'] ?? null;
                DetallePedidoBordado::create([
                    'detalle_pedido_id' => $detalle->id,
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

    /**
     * Sincronizar pagos del pedido: elimina los anteriores y crea los nuevos.
     * Recalcula el abono como suma de montos.
     */
    private function syncPagos(Pedido $pedido, array $pagos): void
    {
        $pedido->pagos()->delete();

        foreach ($pagos as $pago) {
            PagoPedido::create([
                'pedido_id'  => $pedido->id,
                'metodo'     => $pago['metodo'],
                'monto'      => $pago['monto'],
                'banco_id'   => $pago['banco_id'] ?? null,
                'referencia' => $pago['referencia'] ?? null,
            ]);
        }

        $pedido->recalcularAbono();

        // Si con estos pagos el abono alcanzó el mínimo (50%), formaliza el pedido
        // y calcula la fecha de entrega (formalización + 30 días hábiles).
        $pedido->marcarFormalizacionSiCorresponde();
    }

    /**
     * Valida que los pagos cubran el abono mínimo (% configurable del total).
     * Al crear, el mínimo es estricto. Al actualizar se tolera el abono que el
     * pedido ya tenía: un pedido legacy con abono bajo puede seguir recibiendo
     * pagos parciales, pero el abono nunca puede quedar por debajo del mínimo
     * ni de lo que ya estaba abonado (lo que sea menor).
     *
     * @throws \InvalidArgumentException
     */
    private function validarAbonoMinimo(float $total, array $pagos, ?Pedido $pedidoExistente = null): void
    {
        $minimo = round($total * Pedido::porcentajeAbonoMinimo() / 100, 2);
        if ($pedidoExistente) {
            $minimo = min($minimo, (float) $pedidoExistente->abono);
        }

        $abono = 0.0;
        foreach ($pagos as $pago) {
            $abono += (float) ($pago['monto'] ?? 0);
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

    /**
     * Actualiza únicamente los pagos de un pedido con líneas congeladas
     * (formalizado / en producción / completado). No toca productos, cliente ni
     * total: solo permite registrar pagos, p. ej. el saldo restante a la entrega.
     */
    public function actualizarSoloPagos(Pedido $pedido, array $data): void
    {
        DB::transaction(function () use ($pedido, $data) {
            $this->validarAbonoMinimo((float) $pedido->total, $data['pagos'] ?? [], $pedido);
            $this->syncPagos($pedido, $data['pagos'] ?? []);
        });

        Log::info('Pagos de pedido actualizados (líneas congeladas)', [
            'pedido_id' => $pedido->id,
            'abono'     => $pedido->fresh()->abono,
            'user_id'   => Auth::id(),
        ]);
    }

    private function validarCotizacionParaCrearPedido(?int $cotizacionId): ?Cotizacion
    {
        if (!$cotizacionId) {
            return null;
        }

        $cotizacion = Cotizacion::lockForUpdate()->findOrFail($cotizacionId);

        if ($cotizacion->estado !== 'Aprobada') {
            throw new \InvalidArgumentException('La cotización seleccionada no está aprobada para crear pedido.');
        }

        // Vigencia de precios: no permitir crear pedido desde una cotización cuya
        // fecha de validez pactada ya pasó. Se marca como 'Vencida'.
        if ($cotizacion->estaVencidaPorVigencia()) {
            $cotizacion->update(['estado' => 'Vencida']);
            throw new \InvalidArgumentException(
                'La cotización venció: su validez expiró el ' .
                $cotizacion->fechaLimiteVigencia()->format('d/m/Y') .
                '. Reactívala para actualizar los precios antes de convertirla a pedido.'
            );
        }

        $pedidoExistente = Pedido::where('cotizacion_id', $cotizacionId)->lockForUpdate()->exists();
        if ($pedidoExistente) {
            throw new \InvalidArgumentException('Esta cotización ya tiene un pedido asociado.');
        }

        return $cotizacion;
    }
}
