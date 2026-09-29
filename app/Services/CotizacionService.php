<?php

namespace App\Services;

use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use App\Models\DetalleCotizacionBordado;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\TasaCambio;
use App\Models\TipoProducto;
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
                    ?? \Carbon\Carbon::parse($data['fecha_cotizacion'])->addDays(Cotizacion::diasVigencia())->toDateString(),
                'estado'              => 'Pendiente',
                'prioridad'           => $data['prioridad'] ?? 'Normal',
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
                'fecha_validez'        => $data['fecha_validez']
                    ?? \Carbon\Carbon::parse($data['fecha_cotizacion'])->addDays(Cotizacion::diasVigencia())->toDateString(),
                // El estado no viene del formulario: cambia solo por sus acciones.
                'prioridad'            => $data['prioridad'] ?? $cotizacion->prioridad ?? 'Normal',
                'total'                => $total,
                'tasa_cambio_valor'    => TasaCambio::obtenerValorUsd(),
                'notas'                => $data['notas'] ?? null,
                // Si el formulario no las envía, se conservan (antes se borraban al editar).
                'condiciones_terminos' => array_key_exists('condiciones_terminos', $data) ? $data['condiciones_terminos'] : $cotizacion->condiciones_terminos,
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
     * Cambio de estado manual (aprobar, cancelar, volver a pendiente) según
     * Cotizacion::TRANSICIONES.
     *
     * @throws \InvalidArgumentException si la transición no está permitida
     */
    public function cambiarEstado(Cotizacion $cotizacion, string $nuevo): void
    {
        DB::transaction(function () use ($cotizacion, $nuevo) {
            // Con la fila bloqueada: una conversión a pedido simultánea no deja
            // una cotización Cancelada con pedido.
            Cotizacion::whereKey($cotizacion->id)->lockForUpdate()->first();
            $cotizacion->refresh();
            $this->aplicarCambioEstado($cotizacion, $nuevo);
        });
    }

    private function aplicarCambioEstado(Cotizacion $cotizacion, string $nuevo): void
    {
        $actual = $cotizacion->estado;
        if ($actual === 'Convertida') {
            throw new \InvalidArgumentException('No se puede cambiar el estado de una cotización ya convertida a pedido.');
        }
        if ($actual === 'Vencida') {
            throw new \InvalidArgumentException('La cotización está vencida: usa «Reactivar» para renovar su validez.');
        }
        if (! in_array($nuevo, Cotizacion::TRANSICIONES[$actual] ?? [], true)) {
            throw new \InvalidArgumentException("No se puede pasar una cotización de {$actual} a {$nuevo}.");
        }

        $cotizacion->update(['estado' => $nuevo]);

        Log::info('Cotización: cambio de estado', [
            'cotizacion_id' => $cotizacion->id, 'de' => $actual, 'a' => $nuevo, 'user_id' => Auth::id(),
        ]);
    }

    /**
     * Reactivar una cotización vencida, otorgando una nueva fecha_validez de
     * diasVigencia() días desde hoy (con la tasa de cambio actualizada).
     */
    public function reactivar(Cotizacion $cotizacion): void
    {
        if ($cotizacion->estado !== 'Vencida') {
            throw new \InvalidArgumentException('Solo se pueden reactivar cotizaciones en estado Vencida.');
        }

        $cotizacion->update([
            'estado'              => 'Pendiente',
            'fecha_validez'       => now()->addDays(Cotizacion::diasVigencia())->toDateString(),
            'tasa_cambio_valor'   => TasaCambio::obtenerValorUsd(),
        ]);

        Log::info('Cotización reactivada', [
            'cotizacion_id'    => $cotizacion->id,
            'nueva_fecha_validez' => $cotizacion->fecha_validez,
            'user_id'          => Auth::id(),
        ]);
    }

    /**
     * Calcular total de la cotización sumando (precio_base + recargos de bordado) × cantidad.
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
     * Resuelve la base de una línea de producto, soportando dos casos (FEAT-003):
     *   - Legacy: la línea trae `producto_id` → se usa el Producto y sus snapshots.
     *   - Dinámico: la línea trae `tipo_producto_id` (+ tela + atributos) → se
     *     calculan snapshots/precio al vuelo sin requerir una fila `producto`.
     *
     * @return array{producto_id: ?int, tipo_producto_id: ?int, precio_base: float,
     *               tela_snapshot: ?array, atributos_snapshot: ?array, sku_snapshot: ?string}
     */
    private function resolverVarianteLinea(array $item): array
    {
        if (!empty($item['producto_id'])) {
            // withTrashed: una cotización vieja puede tener un producto que luego se inhabilitó.
            $producto = Producto::withTrashed()->with('tela')->findOrFail($item['producto_id']);
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

        $tipo = TipoProducto::withTrashed()->findOrFail($item['tipo_producto_id']);
        $tela = !empty($item['insumo_tela_id']) ? Insumo::withTrashed()->find($item['insumo_tela_id']) : null;
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
     * Crear los detalles de cotización (líneas de producto).
     */
    private function crearDetalles(Cotizacion $cotizacion, array $productos): void
    {
        foreach ($productos as $item) {
            $base = $this->resolverVarianteLinea($item);
            $precioBase = isset($item['precio_unitario'])
                ? (float) $item['precio_unitario']
                : $base['precio_base'];
            $bordados = $this->bordadoPricingService->normalizeBordados($item);
            $precioUnitarioFinal = $this->bordadoPricingService->calcularPrecioUnitarioFinal($precioBase, $bordados);

            $detalle = DetalleCotizacion::create([
                'cotizacion_id' => $cotizacion->id,
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
                DetalleCotizacionBordado::create([
                    'detalle_cotizacion_id' => $detalle->id,
                    'ubicacion_bordado_id' => $bordado['ubicacion_bordado_id'] ?? null,
                    'logo_id' => $logoId,
                    'nombre_aplicado' => trim((string) ($bordado['nombre_aplicado'] ?? '')),
                    // Sin logo del catálogo se conserva el nombre que ya traía (logos legados en texto).
                    'nombre_logo_aplicado' => $this->bordadoPricingService->resolverNombreLogoSnapshot($logoId) ?: trim((string) ($bordado['nombre_logo_aplicado'] ?? '')),
                    'es_personalizada' => (bool) ($bordado['es_personalizada'] ?? false),
                    'cantidad' => max(1, (int) ($bordado['cantidad'] ?? 1)),
                    'precio_aplicado' => (float) ($bordado['precio_aplicado'] ?? 0),
                    'orden' => (int) ($bordado['orden'] ?? 0),
                ]);
            }
        }
    }
}
