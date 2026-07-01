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
    public function getOrdenesCalidad(Request $request)
    {
        $ordenes = OrdenProduccion::query()
            ->with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'pedido.cliente.persona'])
            ->where('estado', 'Finalizado')
            ->whereDoesntHave('controlesCalidad', function ($q) {
                $q->whereIn('resultado', ['aprobado', 'observado']);
            });

        // Filtro: estado de calidad dentro de la cola pendiente.
        //   pendiente   = nunca inspeccionada
        //   reinspeccion = ya tuvo una inspección (rechazo previo) y volvió finalizada
        $estadoCalidad = $request->input('filter_estado_calidad');
        if ($estadoCalidad === 'pendiente') {
            $ordenes->whereDoesntHave('controlesCalidad');
        } elseif ($estadoCalidad === 'reinspeccion') {
            $ordenes->whereHas('controlesCalidad');
        }

        // Clustering por pedido: las órdenes de un mismo pedido quedan contiguas
        // para la fila-cabecera de grupo (dt-group-rows.js). Los grupos se ordenan
        // por su finalización más reciente/antigua y, dentro del grupo, por fecha.
        // Las órdenes manuales (pedido_id NULL, comparadas con <=>) forman un grupo.
        $grupo = "op2.pedido_id <=> orden_produccion.pedido_id and op2.deleted_at is null and op2.estado = 'Finalizado'";
        $dir   = $request->input('filter_orden') === 'antiguos' ? 'asc' : 'desc';
        $fn    = $dir === 'asc' ? 'min' : 'max';

        $ordenes->orderByRaw("(select {$fn}(op2.fecha_fin_real) from orden_produccion op2 where {$grupo}) {$dir}")
            ->orderBy('orden_produccion.pedido_id', $dir)
            ->orderBy('orden_produccion.fecha_fin_real', $dir)
            ->select('orden_produccion.*')
            // Órdenes del pedido aún en cola de inspección (chip de la cabecera de grupo)
            ->selectRaw("(select count(*) from orden_produccion op2
                where {$grupo}
                  and orden_produccion.pedido_id is not null
                  and not exists (
                      select 1 from control_calidad cc
                      where cc.orden_produccion_id = op2.id
                        and cc.deleted_at is null
                        and cc.resultado in ('aprobado', 'observado')
                  )) as grupo_total");

        return DataTables::of($ordenes)
            // Búsqueda global por producto (tipo/nombre) o pedido — las columnas son
            // derivadas, así que se sobrescribe el buscador de DataTables.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $num = preg_replace('/\D/', '', $keyword); // dígitos (ej. "Pedido #8" → "8")
                $query->where(function ($q) use ($keyword, $num) {
                    // El nombre del producto se deriva del tipo de producto (línea dinámica
                    // o legacy), no de una columna 'nombre' en `producto`.
                    $q->whereHas('detallePedido.tipoProducto', fn ($t) => $t->where('nombre', 'like', "%{$keyword}%"))
                      ->orWhereHas('producto.tipoProducto', fn ($t) => $t->where('nombre', 'like', "%{$keyword}%"));
                    if ($num !== '') {
                        $q->orWhere('pedido_id', $num);
                    }
                });
            }, true)
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
