<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\StoreControlCalidadRequest;
use App\Models\ControlCalidad;
use App\Models\OrdenProduccion;
use App\Services\ControlCalidadService;
use App\Support\ReporteFiltros;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * FEAT-006 — Control de Calidad: inspección de órdenes de producción finalizadas.
 */
class ControlCalidadController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(
        private ControlCalidadService $controlCalidadService
    ) {
    }

    /**
     * Órdenes en cola de inspección: finalizadas y sin una inspección que las
     * apruebe (aprobado/observado). Una orden rechazada vuelve a "En Proceso"
     * (sale de la cola) hasta que se re-finaliza; ahí reaparece como re-inspección.
     */
    private function enCola(): Builder
    {
        return OrdenProduccion::query()
            ->where('orden_produccion.estado', 'Finalizado')
            ->whereDoesntHave('controlesCalidad', fn ($q) => $q->whereIn('resultado', ['aprobado', 'observado']));
    }

    /**
     * Página Inertia: una fila por pedido (más una para las órdenes manuales)
     * con los agregados de su cola. Al abrir un pedido (`?pedido=ID|manual`)
     * se recarga solo `cola`, con equipo e historial de cada orden: la
     * inspección no necesita otra petición.
     */
    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(['buscar', 'estado', 'orden', 'pedido']), fn ($v) => $v !== null && $v !== '');

        $pedidos = $this->enCola()
            ->leftJoin('pedido', 'pedido.id', '=', 'orden_produccion.pedido_id')
            ->leftJoin('cliente', 'cliente.id', '=', 'pedido.cliente_id')
            ->leftJoin('persona', 'persona.id', '=', 'cliente.persona_id')
            ->groupBy('orden_produccion.pedido_id')
            // MAX() sobre persona.nombre: valor único por grupo (1 pedido = 1 cliente),
            // envuelto en agregado para cumplir ONLY_FULL_GROUP_BY de MySQL 8.
            ->selectRaw('
                orden_produccion.pedido_id,
                MAX(persona.nombre) as cliente_nombre,
                COUNT(*) as total_ordenes,
                SUM(EXISTS(
                    select 1 from control_calidad cc
                    where cc.orden_produccion_id = orden_produccion.id
                      and cc.deleted_at is null
                )) as reinspecciones,
                MAX(orden_produccion.fecha_fin_real) as ultima_fin
            ');

        // pendiente = nunca inspeccionada · reinspeccion = rechazo previo que volvió
        if (($filtros['estado'] ?? null) === 'pendiente') {
            $pedidos->whereDoesntHave('controlesCalidad');
        } elseif (($filtros['estado'] ?? null) === 'reinspeccion') {
            $pedidos->whereHas('controlesCalidad');
        }
        if (! empty($filtros['buscar'])) {
            $kw = trim($filtros['buscar']);
            $num = preg_replace('/\D/', '', $kw); // dígitos (ej. "Pedido #8" → "8")
            $pedidos->where(function ($q) use ($kw, $num) {
                // El nombre del producto se deriva del tipo (línea dinámica o legacy).
                $q->where('persona.nombre', 'like', "%{$kw}%")
                    ->orWhereHas('detallePedido.tipoProducto', fn ($t) => $t->where('nombre', 'like', "%{$kw}%"))
                    ->orWhereHas('producto.tipoProducto', fn ($t) => $t->where('nombre', 'like', "%{$kw}%"));
                if ($num !== '') {
                    $q->orWhereRaw('CAST(orden_produccion.pedido_id AS CHAR) LIKE ?', ["%{$num}%"]);
                }
            });
        }
        ($filtros['orden'] ?? null) === 'antiguos'
            ? $pedidos->orderByRaw('MIN(orden_produccion.fecha_fin_real) asc')
            : $pedidos->orderByRaw('MAX(orden_produccion.fecha_fin_real) desc');

        return Inertia::render('Calidad/Index', [
            'registros' => $pedidos->paginate(15)->withQueryString()->through(fn ($p) => [
                'pedido_id' => $p->pedido_id,
                'cliente' => $p->cliente_nombre,
                'ordenes' => (int) $p->total_ordenes,
                'reinspecciones' => (int) $p->reinspecciones,
                'ultima_fin' => $p->ultima_fin ? substr((string) $p->ultima_fin, 0, 10) : null,
            ]),
            'filtros' => (object) $filtros,
            'cola' => fn () => isset($filtros['pedido']) ? $this->cola($filtros['pedido']) : null,
            'urls' => [
                'index' => route('calidad.index', absolute: false),
                'reportePdf' => route('calidad.reporte.pdf', absolute: false),
            ],
        ]);
    }

    /** Órdenes en cola de un pedido (o las manuales), con todo lo que usa la inspección. */
    private function cola(string $pedido): array
    {
        $ordenes = $this->enCola()
            ->with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'empleadosAsignados.persona', 'controlesCalidad.inspector:id,name'])
            ->when($pedido === 'manual', fn ($q) => $q->whereNull('pedido_id'), fn ($q) => $q->where('pedido_id', (int) $pedido))
            ->orderByDesc('fecha_fin_real')
            ->get();

        return $ordenes->map(fn (OrdenProduccion $o) => [
            'id' => $o->id,
            'producto' => $o->nombre_producto,
            'cantidad_solicitada' => (int) $o->cantidad_solicitada,
            'cantidad_producida' => (int) $o->cantidad_producida,
            'cantidad_defectuosa' => (int) $o->cantidad_defectuosa,
            'fecha_fin' => $o->fecha_fin_real?->toDateString(),
            // Equipo con lo producido por cada uno: con 2+ empleados el rechazo se
            // atribuye (el reproceso descuenta a quien corresponde).
            'equipo' => $o->empleadosAsignados->map(fn ($e) => [
                'id' => $e->id,
                'nombre' => $e->persona->nombre ?? ('Empleado #'.$e->id),
                'producida' => (int) $e->pivot->cantidad_producida,
            ])->values()->all(),
            'historial' => $o->controlesCalidad->sortByDesc('fecha_inspeccion')->values()->map(fn ($c) => [
                'fecha' => $c->fecha_inspeccion?->format('Y-m-d H:i'),
                'inspector' => $c->inspector?->name,
                'inspeccionada' => (int) $c->cantidad_inspeccionada,
                'aprobada' => (int) $c->cantidad_aprobada,
                'rechazada' => (int) $c->cantidad_rechazada,
                'resultado' => $c->resultado,
                'observaciones' => $c->observaciones,
            ])->all(),
        ])->all();
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
     * Registra una inspección. La lógica (incluido el reproceso y la
     * atribución por empleado) vive en ControlCalidadService.
     */
    public function inspeccionar(StoreControlCalidadRequest $request, OrdenProduccion $orden)
    {
        $this->controlCalidadService->inspeccionar($orden, $request->validated(), (int) auth()->id());

        return $this->responder($request, 'Inspección registrada correctamente.', ['message' => 'Inspección registrada correctamente.']);
    }
}
