<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreControlCalidadRequest;
use App\Models\OrdenProduccion;
use App\Services\ControlCalidadService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

/**
 * FEAT-006 — Control de Calidad: inspección de órdenes de producción finalizadas.
 */
class ControlCalidadController extends Controller
{
    public function __construct(
        private ControlCalidadService $controlCalidadService
    ) {
    }

    public function index()
    {
        return view('admin.calidad.index');
    }

    /**
     * DataTable server-side: órdenes en "Finalizado" PENDIENTES de calidad.
     * Pendiente = finalizada y sin inspección que la apruebe (aprobado/observado).
     * Una orden rechazada vuelve a "En Proceso" (sale de la lista) hasta que se
     * re-finalice; ahí reaparece como pendiente de re-inspección.
     */
    public function getOrdenesCalidad(Request $request)
    {
        $ordenes = OrdenProduccion::query()
            ->with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'pedido.cliente.persona'])
            ->where('estado', 'Finalizado')
            ->whereDoesntHave('controlesCalidad', function ($q) {
                $q->whereIn('resultado', ['aprobado', 'observado']);
            })
            ->select('orden_produccion.*')
            ->orderBy('fecha_fin_real', 'desc');

        return DataTables::of($ordenes)
            ->addColumn('pedido_info', function ($orden) {
                return $orden->pedido_id && $orden->pedido
                    ? 'Pedido #' . $orden->pedido->id
                    : 'Orden manual';
            })
            ->addColumn('producto_info', fn ($orden) => $orden->nombre_producto)
            ->addColumn('cantidad_producida', fn ($orden) => $orden->cantidad_producida)
            ->addColumn('cantidad_solicitada', fn ($orden) => $orden->cantidad_solicitada)
            ->addColumn('reinspeccion', fn ($orden) => $orden->controlesCalidad()->exists())
            ->addColumn('fecha_fin', fn ($orden) => $orden->fecha_fin_real ? $orden->fecha_fin_real->format('d/m/Y') : '—')
            ->rawColumns([])
            ->make(true);
    }

    /**
     * JSON con los datos de la orden + su historial de inspecciones (para el modal).
     */
    public function detalle(OrdenProduccion $orden)
    {
        $orden->load(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'pedido', 'controlesCalidad.inspector:id,name']);

        return response()->json([
            'id'                 => $orden->id,
            'producto'           => $orden->nombre_producto,
            'pedido'             => $orden->pedido_id ? ('Pedido #' . $orden->pedido_id) : 'Orden manual',
            'estado'             => $orden->estado,
            'cantidad_solicitada' => $orden->cantidad_solicitada,
            'cantidad_producida' => $orden->cantidad_producida,
            'cantidad_defectuosa' => $orden->cantidad_defectuosa,
            'historial'          => $orden->controlesCalidad->sortByDesc('fecha_inspeccion')->values()->map(function ($c) {
                return [
                    'fecha'        => optional($c->fecha_inspeccion)->format('d/m/Y H:i'),
                    'inspector'    => $c->inspector?->name ?? '—',
                    'inspeccionada' => $c->cantidad_inspeccionada,
                    'aprobada'     => $c->cantidad_aprobada,
                    'rechazada'    => $c->cantidad_rechazada,
                    'resultado'    => $c->resultado,
                    'observaciones' => $c->observaciones,
                ];
            }),
        ]);
    }

    /**
     * Registra una inspección. Delega la lógica (incluido el reproceso) al service.
     */
    public function inspeccionar(StoreControlCalidadRequest $request, OrdenProduccion $orden)
    {
        $this->controlCalidadService->inspeccionar($orden, $request->validated(), (int) auth()->id());

        return response()->json(['message' => 'Inspección registrada correctamente.']);
    }
}
