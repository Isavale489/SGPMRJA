<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Services\DisponibilidadInsumoService;
use Illuminate\Http\Request;

/**
 * Proyección de disponibilidad de insumos (aviso NO bloqueante de stock).
 *
 * Punto de entrada único: proyectarLineas() — recibe las líneas en vivo desde el
 * wizard (Cotización o Pedido), aún sin guardar, resuelve tipo/tela y delega el
 * cálculo en DisponibilidadInsumoService. Sirve tanto a la creación como a la
 * revisión de un registro existente (el front reenvía sus líneas actuales).
 */
class DisponibilidadInsumoController extends Controller
{
    public function __construct(private DisponibilidadInsumoService $service)
    {
    }

    /**
     * POST — proyección en vivo desde un wizard (cotización).
     * Body: { lineas: [{ producto_id?, tipo_producto_id?, tela_id?, cantidad }] }
     */
    public function proyectarLineas(Request $request)
    {
        $validated = $request->validate([
            'lineas'                     => 'present|array',
            'lineas.*.producto_id'       => 'nullable|integer',
            'lineas.*.tipo_producto_id'  => 'nullable|integer',
            'lineas.*.tela_id'           => 'nullable|integer',
            'lineas.*.cantidad'          => 'required|numeric|min:0',
        ]);

        $lineas = $this->normalizarLineas($validated['lineas']);

        return response()->json($this->service->proyectar($lineas));
    }

    /**
     * Normaliza líneas del wizard: si una línea trae producto_id (legacy) pero no
     * tipo/tela, los resuelve desde el catálogo en un solo query.
     *
     * @param  array<int,array<string,mixed>>  $lineas
     * @return array<int,array{tipo_producto_id:?int,tela_id:?int,cantidad:int}>
     */
    private function normalizarLineas(array $lineas): array
    {
        // Productos a resolver (líneas legacy sin tipo explícito).
        $productoIds = collect($lineas)
            ->filter(fn($l) => empty($l['tipo_producto_id']) && !empty($l['producto_id']))
            ->pluck('producto_id')
            ->unique()
            ->values();

        $productos = $productoIds->isEmpty()
            ? collect()
            : Producto::whereIn('id', $productoIds)->get(['id', 'tipo_producto_id', 'insumo_tela_id'])->keyBy('id');

        return collect($lineas)->map(function ($l) use ($productos) {
            $tipoId = $l['tipo_producto_id'] ?? null;
            $telaId = $l['tela_id'] ?? null;

            if (!$tipoId && !empty($l['producto_id']) && ($p = $productos->get($l['producto_id']))) {
                $tipoId = $p->tipo_producto_id;
                $telaId = $telaId ?: $p->insumo_tela_id;
            }

            return [
                'tipo_producto_id' => $tipoId ? (int) $tipoId : null,
                'tela_id'          => $telaId ? (int) $telaId : null,
                'cantidad'         => (int) ($l['cantidad'] ?? 0),
            ];
        })->all();
    }
}
