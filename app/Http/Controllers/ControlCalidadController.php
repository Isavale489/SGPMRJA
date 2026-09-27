<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreControlCalidadRequest;
use App\Models\ControlCalidad;
use App\Models\OrdenProduccion;
use App\Services\ControlCalidadService;
use App\Support\ReporteFiltros;
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
    /**
     * Órdenes en cola de inspección de un pedido (o las manuales) para el
     * DataTable del modal "Ver órdenes". `pedido_id` = id o 'manual'.
     */
    public function getOrdenesCalidad(Request $request)
    {
        $ordenes = OrdenProduccion::query()
            ->with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'pedido.cliente.persona'])
            ->where('estado', 'Finalizado')
            ->whereDoesntHave('controlesCalidad', function ($q) {
                $q->whereIn('resultado', ['aprobado', 'observado']);
            })
            ->select('orden_produccion.*');

        if ($request->filled('pedido_id')) {
            $request->input('pedido_id') === 'manual'
                ? $ordenes->whereNull('orden_produccion.pedido_id')
                : $ordenes->where('orden_produccion.pedido_id', $request->input('pedido_id'));
        }

        $ordenes->orderByDesc('orden_produccion.fecha_fin_real');

        return DataTables::of($ordenes)
            ->addColumn('producto_info', fn ($orden) => $orden->nombre_producto)
            ->addColumn('cantidad_producida', fn ($orden) => $orden->cantidad_producida)
            ->addColumn('cantidad_solicitada', fn ($orden) => $orden->cantidad_solicitada)
            ->addColumn('reinspeccion', fn ($orden) => $orden->controlesCalidad()->exists())
            ->addColumn('fecha_fin', fn ($orden) => $orden->fecha_fin_real ? $orden->fecha_fin_real->format('d/m/Y') : '—')
            ->rawColumns([])
            ->make(true);
    }

    /**
     * Tabla principal de /calidad: una fila por pedido (más una para las
     * órdenes manuales) con agregados de su cola de inspección. El detalle
     * por orden vive en el modal "Ver órdenes" (getOrdenesCalidad + pedido_id).
     *
     * El filtro de estado de calidad se aplica ANTES de agrupar: el pedido
     * aparece solo si tiene órdenes que cumplan y los conteos reflejan esas.
     */
    public function getPedidosCalidad(Request $request)
    {
        $pedidos = OrdenProduccion::query()
            ->leftJoin('pedido', 'pedido.id', '=', 'orden_produccion.pedido_id')
            ->leftJoin('cliente', 'cliente.id', '=', 'pedido.cliente_id')
            ->leftJoin('persona', 'persona.id', '=', 'cliente.persona_id')
            ->where('orden_produccion.estado', 'Finalizado')
            ->whereDoesntHave('controlesCalidad', function ($q) {
                $q->whereIn('resultado', ['aprobado', 'observado']);
            })
            ->groupBy('orden_produccion.pedido_id')
            // MAX() sobre persona.nombre: valor único por grupo (1 pedido = 1 cliente),
            // envuelto en agregado para cumplir ONLY_FULL_GROUP_BY de MySQL 8.
            ->selectRaw("
                orden_produccion.pedido_id,
                MAX(persona.nombre) as cliente_nombre,
                COUNT(*) as total_ordenes,
                SUM(EXISTS(
                    select 1 from control_calidad cc
                    where cc.orden_produccion_id = orden_produccion.id
                      and cc.deleted_at is null
                )) as reinspecciones,
                DATE_FORMAT(MAX(orden_produccion.fecha_fin_real), '%d/%m/%Y') as ultima_fin
            ");

        // pendiente = nunca inspeccionada · reinspeccion = rechazo previo que volvió
        $estadoCalidad = $request->input('filter_estado_calidad');
        if ($estadoCalidad === 'pendiente') {
            $pedidos->whereDoesntHave('controlesCalidad');
        } elseif ($estadoCalidad === 'reinspeccion') {
            $pedidos->whereHas('controlesCalidad');
        }

        $request->input('filter_orden') === 'antiguos'
            ? $pedidos->orderByRaw('MIN(orden_produccion.fecha_fin_real) asc')
            : $pedidos->orderByRaw('MAX(orden_produccion.fecha_fin_real) desc');

        return DataTables::of($pedidos)
            ->filter(function ($query) use ($request) {
                $kw = trim((string) $request->input('search.value', ''));
                if ($kw === '') {
                    return;
                }
                $num = preg_replace('/\D/', '', $kw); // dígitos (ej. "Pedido #8" → "8")
                $query->where(function ($q) use ($kw, $num) {
                    // El nombre del producto se deriva del tipo de producto (línea
                    // dinámica o legacy), no de una columna 'nombre' en `producto`.
                    $q->where('persona.nombre', 'like', "%{$kw}%")
                      ->orWhereHas('detallePedido.tipoProducto', fn ($t) => $t->where('nombre', 'like', "%{$kw}%"))
                      ->orWhereHas('producto.tipoProducto', fn ($t) => $t->where('nombre', 'like', "%{$kw}%"));
                    if ($num !== '') {
                        $q->orWhereRaw('CAST(orden_produccion.pedido_id AS CHAR) LIKE ?', ["%{$num}%"]);
                    }
                });
            })
            ->make(true);
    }

    /**
     * JSON con los datos de la orden + su historial de inspecciones (para el modal).
     */
    public function detalle(OrdenProduccion $orden)
    {
        $orden->load(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'pedido', 'empleadosAsignados.persona', 'controlesCalidad.inspector:id,name']);

        return response()->json([
            'id'                 => $orden->id,
            'producto'           => $orden->nombre_producto,
            'pedido'             => $orden->pedido_id ? ('Pedido #' . $orden->pedido_id) : 'Orden manual',
            'estado'             => $orden->estado,
            'cantidad_solicitada' => $orden->cantidad_solicitada,
            'cantidad_producida' => $orden->cantidad_producida,
            'cantidad_defectuosa' => $orden->cantidad_defectuosa,
            // Equipo con lo producido por cada uno: para atribuir el rechazo cuando
            // la orden tiene 2+ empleados (el reproceso descuenta a quien corresponde).
            'equipo'             => $orden->empleadosAsignados->map(function ($e) {
                return [
                    'id'        => $e->id,
                    'nombre'    => $e->persona->nombre ?? ('Empleado #' . $e->id),
                    'producida' => (int) $e->pivot->cantidad_producida,
                ];
            })->values(),
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
     * Reporte PDF — historial de inspecciones de calidad (registro de auditoría).
     * Filtros: resultado del veredicto + rango de fecha de inspección.
     */
    public function reportePdf(Request $request)
    {
        $query = ControlCalidad::query()
            ->with([
                'inspector:id,name',
                'ordenProduccion.producto.tipoProducto',
                'ordenProduccion.detallePedido.tipoProducto',
                'ordenProduccion.detallePedido.genero',
                'ordenProduccion.pedido',
            ])
            ->orderByDesc('fecha_inspeccion');

        if ($request->filled('resultado')) {
            $query->where('resultado', $request->resultado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_inspeccion', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_inspeccion', '<=', $request->fecha_hasta);
        }

        $inspecciones = $query->get();

        $filtros = [];
        if ($request->filled('resultado')) {
            $filtros['Resultado'] = ControlCalidad::RESULTADOS[$request->resultado] ?? ucfirst($request->resultado);
        }
        if ($rango = ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de inspección'] = $rango;
        }

        $pdf = \PDF::loadView('admin.calidad.reporte_pdf', compact('inspecciones', 'filtros'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('inspecciones_calidad_' . now()->format('Y-m-d_H-i-s') . '.pdf');
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
