<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Yajra\DataTables\Facades\DataTables;

class MovimientoInsumoController extends Controller
{
    use RespondeSegunCliente;

    /** Estado de la existencia frente a su mínimo (mismo criterio que el panel de Compras). */
    private static function estadoStock(Insumo $i): string
    {
        if ($i->stock_actual <= $i->stock_minimo) {
            return 'bajo';
        }

        return $i->stock_actual <= $i->stock_minimo * 1.5 ? 'medio' : 'normal';
    }

    /**
     * Página Inertia con dos vistas (?vista=movimientos|existencias). Solo se
     * consulta la vista activa: cada una es un closure que Inertia evalúa si
     * se pide. El antiguo «reporte de existencias» es la vista existencias.
     */
    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(['vista', 'buscar', 'tipo', 'insumo', 'stock', 'desde', 'hasta', 'tipo_insumo', 'alerta']), fn ($v) => $v !== null && $v !== '');
        $vista = ($filtros['vista'] ?? null) === 'existencias' ? 'existencias' : 'movimientos';

        return Inertia::render('Movimientos/Index', [
            'vista' => $vista,
            'filtros' => (object) $filtros,
            'movimientos' => fn () => $vista === 'movimientos' ? $this->movimientos($filtros) : null,
            'existencias' => fn () => $vista === 'existencias' ? $this->existencias($filtros) : null,
            // Todos los activos para el filtro (un insumo legacy no inventariable
            // puede tener movimientos históricos); solo inventariables para la salida.
            'insumos' => Insumo::where('estado', true)->orderBy('nombre')->get(['id', 'nombre', 'codigo', 'unidad_medida', 'is_inventoriable', 'stock_actual'])
                ->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'codigo' => $i->codigo, 'unidad' => $i->unidad_medida, 'inventariable' => (bool) $i->is_inventoriable, 'stock' => (float) $i->stock_actual])->all(),
            'tiposInsumo' => Insumo::where('estado', true)->where('is_inventoriable', true)->distinct()->orderBy('tipo')->pluck('tipo')->all(),
            'urls' => [
                'index' => route('movimiento-insumo.index', absolute: false),
                'store' => route('movimiento-insumo.store', absolute: false),
                'reportePdf' => route('movimiento-insumo.reporte.pdf', absolute: false),
                'alertas' => route('movimiento-insumo.alertas', absolute: false),
                'rotacion' => route('movimiento-insumo.rotacion', absolute: false),
                'historial' => url('/movimiento-insumo/historial'),
            ],
        ]);
    }

    private function movimientos(array $f)
    {
        $q = MovimientoInsumo::with(['insumo:id,nombre,codigo,tipo,unidad_medida', 'creadoPor:id,name'])
            ->orderByDesc('movimiento_insumo.created_at')->orderByDesc('movimiento_insumo.id');

        if (! empty($f['tipo'])) {
            $q->where('tipo_movimiento', $f['tipo']);
        }
        if (! empty($f['insumo'])) {
            $q->where('insumo_id', $f['insumo']);
        }
        $q->when($f['stock'] ?? null, fn ($q, $estado) => $q->filtroStock($estado));
        if (! empty($f['desde'])) {
            $q->where('movimiento_insumo.created_at', '>=', $f['desde'].' 00:00:00');
        }
        if (! empty($f['hasta'])) {
            $q->where('movimiento_insumo.created_at', '<=', $f['hasta'].' 23:59:59');
        }
        if (! empty($f['buscar'])) {
            $kw = trim($f['buscar']);
            $q->where(fn ($w) => $w->where('motivo', 'like', "%{$kw}%")
                ->orWhereHas('insumo', fn ($i) => $i->where('nombre', 'like', "%{$kw}%")->orWhere('codigo', 'like', "%{$kw}%")));
        }

        return $q->paginate(20)->withQueryString()->through(fn (MovimientoInsumo $m) => [
            'id' => $m->id,
            'tipo' => $m->tipo_movimiento,
            'insumo_id' => $m->insumo_id,
            'insumo' => $m->insumo?->nombre,
            'codigo' => $m->insumo?->codigo,
            'unidad' => $m->insumo?->unidad_medida,
            'cantidad' => (float) $m->cantidad,
            'stock_anterior' => (float) $m->stock_anterior,
            'stock_nuevo' => (float) $m->stock_nuevo,
            'motivo' => $m->motivo,
            'usuario' => $m->creadoPor?->name,
            'fecha' => $m->created_at?->format('Y-m-d H:i'),
        ]);
    }

    private function existencias(array $f)
    {
        $q = Insumo::where('estado', true)->where('is_inventoriable', true)->orderBy('nombre');
        if (! empty($f['tipo_insumo'])) {
            $q->where('tipo', $f['tipo_insumo']);
        }
        if (! empty($f['alerta'])) {
            $q->whereColumn('stock_actual', '<=', 'stock_minimo');
        }
        if (! empty($f['buscar'])) {
            $kw = trim($f['buscar']);
            $q->where(fn ($w) => $w->where('nombre', 'like', "%{$kw}%")->orWhere('codigo', 'like', "{$kw}%"));
        }

        return $q->paginate(20)->withQueryString()->through(fn (Insumo $i) => [
            'id' => $i->id,
            'nombre' => $i->nombre,
            'codigo' => $i->codigo,
            'tipo' => $i->tipo,
            'unidad' => $i->unidad_medida,
            'minimo' => (float) $i->stock_minimo,
            'actual' => (float) $i->stock_actual,
            'maximo' => (float) $i->stock_maximo,
            'costo' => (float) $i->costo_unitario,
            'estado' => self::estadoStock($i),
        ]);
    }

    /**
     * Exportar el historial de movimientos a PDF, con filtros por tipo, insumo
     * y rango de fecha del movimiento (created_at).
     */
    public function reportePdf(Request $request)
    {
        $query = MovimientoInsumo::with(['insumo', 'creadoPor'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('tipo_movimiento')) {
            $query->where('tipo_movimiento', $request->tipo_movimiento);
        }
        if ($request->filled('insumo_id')) {
            $query->where('insumo_id', $request->insumo_id);
        }
        $query->when($request->filled('estado_stock'), function ($q) use ($request) {
            $q->filtroStock($request->input('estado_stock'));
        });
        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde . ' 00:00:00');
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $movimientos = $query->get();

        $filtros = [];
        if ($request->filled('tipo_movimiento')) {
            $filtros['Tipo de movimiento'] = ucfirst($request->tipo_movimiento);
        }
        if ($request->filled('insumo_id')) {
            $filtros['Insumo'] = optional(\App\Models\Insumo::find($request->insumo_id))->nombre
                ?? ('#' . $request->insumo_id);
        }
        if ($request->filled('estado_stock') && isset(MovimientoInsumo::ETIQUETAS_STOCK[$request->estado_stock])) {
            $filtros['Estado de stock'] = MovimientoInsumo::ETIQUETAS_STOCK[$request->estado_stock];
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha'] = $rango;
        }

        $pdf = \PDF::loadView('admin.movimiento-insumo.movimientos.reporte_pdf', compact('movimientos', 'filtros'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('movimientos_insumo_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Panel de existencias dentro de /movimiento-insumo:
     * stock mínimo, actual y máximo de cada insumo inventariable,
     * para consultarlo sin salir a /insumos.
     */
    public function getExistencias(Request $request)
    {
        $query = Insumo::where('estado', true)
            ->where('is_inventoriable', true)
            ->select('id', 'nombre', 'codigo', 'tipo', 'unidad_medida', 'stock_minimo', 'stock_actual', 'stock_maximo', 'costo_unitario')
            ->orderBy('id', 'desc'); // más reciente primero (estándar del sistema)

        if ($request->filled('filter_tipo')) {
            $query->where('tipo', $request->input('filter_tipo'));
        }

        if ($request->input('filter_estado') === 'alerta') {
            $query->whereRaw('stock_actual <= stock_minimo');
        }

        return DataTables::of($query)
            ->addColumn('stock_status', function ($insumo) {
                if ($insumo->stock_actual <= $insumo->stock_minimo) {
                    return 'bajo';
                } elseif ($insumo->stock_actual <= ($insumo->stock_minimo * 1.5)) {
                    return 'medio';
                }
                return 'normal';
            })
            ->make(true);
    }

    /**
     * Registra una salida manual. Las entradas llegan solo por Compras (con
     * proveedor, costo y factura) y por Producción.
     *
     * Con lockForUpdate(), como CompraService y ProduccionInventarioService:
     * dos salidas simultáneas del mismo insumo no pueden leer el mismo stock.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'insumo_id' => ['required', 'exists:insumo,id'],
            'tipo_movimiento' => ['required', 'in:Salida'],
            'cantidad' => ['required', 'numeric', 'min:0.01'],
            'motivo' => ['required', 'string', 'max:500'],
        ], [
            'tipo_movimiento.in' => 'Solo se registran salidas manuales: las entradas llegan por Compras o Producción.',
        ]);

        DB::transaction(function () use ($datos) {
            $insumo = Insumo::lockForUpdate()->findOrFail($datos['insumo_id']);

            if (! $insumo->is_inventoriable) {
                throw ValidationException::withMessages(['insumo_id' => 'Este insumo no es inventariable, por lo que no gestiona stock ni admite movimientos.']);
            }
            $anterior = (float) $insumo->stock_actual;
            if ($anterior < (float) $datos['cantidad']) {
                throw ValidationException::withMessages(['cantidad' => 'No hay suficiente existencia: quedan '.rtrim(rtrim(number_format($anterior, 2, ',', '.'), '0'), ',').' '.$insumo->unidad_medida.'.']);
            }
            $nuevo = $anterior - (float) $datos['cantidad'];

            MovimientoInsumo::create([
                'insumo_id' => $insumo->id,
                'tipo_movimiento' => 'Salida',
                'cantidad' => $datos['cantidad'],
                'stock_anterior' => $anterior,
                'stock_nuevo' => $nuevo,
                'motivo' => $datos['motivo'],
                'created_by' => Auth::id(),
            ]);
            $insumo->update(['stock_actual' => $nuevo]);
        });

        return $this->responder($request, 'Salida registrada correctamente.');
    }

    /** El antiguo «reporte de existencias» es ahora la vista existencias de la página principal. */
    public function reporteExistencia()
    {
        return redirect()->route('movimiento-insumo.index', ['vista' => 'existencias']);
    }

    public function historialInsumo(int $id): Response
    {
        $insumo = Insumo::findOrFail($id);

        return Inertia::render('Movimientos/Historial', [
            'insumo' => ['id' => $insumo->id, 'nombre' => $insumo->nombre, 'codigo' => $insumo->codigo, 'unidad' => $insumo->unidad_medida,
                'actual' => (float) $insumo->stock_actual, 'minimo' => (float) $insumo->stock_minimo, 'maximo' => (float) $insumo->stock_maximo],
            'movimientos' => MovimientoInsumo::where('insumo_id', $id)->with('creadoPor:id,name')
                ->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(25)->through(fn (MovimientoInsumo $m) => [
                    'id' => $m->id, 'tipo' => $m->tipo_movimiento, 'cantidad' => (float) $m->cantidad,
                    'stock_anterior' => (float) $m->stock_anterior, 'stock_nuevo' => (float) $m->stock_nuevo,
                    'motivo' => $m->motivo, 'usuario' => $m->creadoPor?->name, 'fecha' => $m->created_at?->format('Y-m-d H:i'),
                ]),
            'urls' => ['index' => route('movimiento-insumo.index', absolute: false)],
        ]);
    }

    /**
     * Análisis de Rotación: insumos ordenados por sus salidas acumuladas
     * (histórico), para priorizar reposición. Los sin salidas quedan al final.
     */
    public function analisisRotacion(): Response
    {
        $insumos = Insumo::where('estado', true)->where('is_inventoriable', true)
            ->withSum(['movimientos as total_salidas' => fn ($q) => $q->where('tipo_movimiento', 'Salida')], 'cantidad')
            ->orderByDesc('total_salidas')->orderBy('nombre')
            ->get();

        return Inertia::render('Movimientos/Rotacion', [
            'insumos' => $insumos->map(fn (Insumo $i) => [
                'id' => $i->id, 'nombre' => $i->nombre, 'codigo' => $i->codigo, 'unidad' => $i->unidad_medida,
                'salidas' => (float) ($i->total_salidas ?? 0), 'actual' => (float) $i->stock_actual, 'minimo' => (float) $i->stock_minimo,
            ])->all(),
            'urls' => ['index' => route('movimiento-insumo.index', absolute: false), 'historial' => url('/movimiento-insumo/historial')],
        ]);
    }

    /** Insumos en o bajo su existencia mínima (enlazado desde el encabezado y el dashboard). */
    public function alertasStock(): Response
    {
        $insumos = Insumo::where('estado', true)->where('is_inventoriable', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderByRaw('stock_actual - stock_minimo')
            ->get();

        return Inertia::render('Movimientos/Alertas', [
            'insumos' => $insumos->map(fn (Insumo $i) => [
                'id' => $i->id, 'nombre' => $i->nombre, 'codigo' => $i->codigo, 'tipo' => $i->tipo, 'unidad' => $i->unidad_medida,
                'actual' => (float) $i->stock_actual, 'minimo' => (float) $i->stock_minimo, 'maximo' => (float) $i->stock_maximo,
            ])->all(),
            'urls' => ['index' => route('movimiento-insumo.index', absolute: false), 'historial' => url('/movimiento-insumo/historial'), 'compras' => route('compras.index', absolute: false)],
        ]);
    }
}
