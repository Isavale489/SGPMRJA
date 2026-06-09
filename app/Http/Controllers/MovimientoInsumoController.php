<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class MovimientoInsumoController extends Controller
{
    public function index()
    {
        // Todos los activos: alimenta el filtro del listado (un insumo legacy
        // no inventariable podría tener movimientos históricos que filtrar).
        $insumos = Insumo::where('estado', true)->get();
        // Solo inventariables: alimenta el select de "registrar movimiento";
        // los no inventariables no gestionan stock, así que no se mueven.
        $insumosInventariables = Insumo::where('estado', true)
            ->where('is_inventoriable', true)
            ->get();
        return view('admin.movimiento-insumo.movimientos.index', compact('insumos', 'insumosInventariables'));
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
        if ($request->filled('fecha_desde')) {
            $query->where('created_at', '>=', $request->fecha_desde . ' 00:00:00');
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('created_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $movimientos = $query->get();
        $pdf = \PDF::loadView('admin.movimiento-insumo.movimientos.reporte_pdf', compact('movimientos'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('movimientos_insumo_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    public function getMovimientos(Request $request)
    {
        $movimientos = MovimientoInsumo::with(['insumo', 'creadoPor'])
            ->select('movimiento_insumo.id', 'movimiento_insumo.insumo_id', 'movimiento_insumo.tipo_movimiento', 'movimiento_insumo.cantidad', 'movimiento_insumo.stock_anterior', 'movimiento_insumo.stock_nuevo', 'movimiento_insumo.motivo', 'movimiento_insumo.created_by', 'movimiento_insumo.created_at')
            ->orderBy('movimiento_insumo.created_at', 'desc');

        if ($request->filled('filter_tipo_movimiento')) {
            $movimientos->where('movimiento_insumo.tipo_movimiento', $request->input('filter_tipo_movimiento'));
        }

        if ($request->filled('filter_insumo_id')) {
            $movimientos->where('movimiento_insumo.insumo_id', $request->input('filter_insumo_id'));
        }

        $fechaDesde = $request->input('filter_fecha_desde');
        $fechaHasta = $request->input('filter_fecha_hasta');

        if ($fechaDesde && $fechaHasta) {
            $movimientos->whereBetween('movimiento_insumo.created_at', [
                $fechaDesde . ' 00:00:00',
                $fechaHasta . ' 23:59:59',
            ]);
        } elseif ($fechaDesde) {
            $movimientos->where('movimiento_insumo.created_at', '>=', $fechaDesde . ' 00:00:00');
        } elseif ($fechaHasta) {
            $movimientos->where('movimiento_insumo.created_at', '<=', $fechaHasta . ' 23:59:59');
        }

        return DataTables::of($movimientos)
            // Búsqueda estricta: por el insumo (nombre o código) y el motivo del
            // movimiento. Sobrescribe el buscador global para no romper sobre la
            // columna derivada del insumo.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->where('movimiento_insumo.motivo', 'like', "{$keyword}%")
                      ->orWhereHas('insumo', function ($i) use ($keyword) {
                          $i->where('nombre', 'like', "{$keyword}%")
                            ->orWhere('codigo', 'like', "{$keyword}%");
                      });
                });
            }, true)
            ->addColumn('insumo_nombre', function ($movimiento) {
                return $movimiento->insumo ? $movimiento->insumo->nombre : 'N/A';
            })
            ->addColumn('insumo_tipo', function ($movimiento) {
                return $movimiento->insumo ? $movimiento->insumo->tipo : 'N/A';
            })
            ->addColumn('usuario', function ($movimiento) {
                return $movimiento->creadoPor ? $movimiento->creadoPor->name : 'Sistema';
            })
            ->addColumn('fecha', function ($movimiento) {
                return $movimiento->created_at ? $movimiento->created_at->format('d/m/Y H:i') : 'N/A';
            })
            ->addColumn('actions', function ($movimiento) {
                $actions = '<div class="d-flex gap-2 justify-content-center">';
                $actions .= '<button type="button" class="btn btn-sm btn-soft-info view-btn" data-id="' . $movimiento->id . '" title="Ver">';
                $actions .= '<i class="ri-eye-fill"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
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
            ->select('id', 'nombre', 'codigo', 'tipo', 'unidad_medida', 'stock_minimo', 'stock_actual', 'stock_maximo')
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

    public function store(Request $request)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumo,id',
            'tipo_movimiento' => 'required|in:Entrada,Salida',
            'cantidad' => 'required|numeric|min:0.01',
            'motivo' => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            $insumo = Insumo::findOrFail($request->insumo_id);

            // Un insumo no inventariable no gestiona stock: no admite movimientos.
            if (!$insumo->is_inventoriable) {
                DB::rollBack();
                return response()->json([
                    'error' => 'Este insumo no es inventariable, por lo que no gestiona stock ni admite movimientos de insumo.'
                ], 422);
            }

            $stockAnterior = $insumo->stock_actual;

            // Calcular nuevo stock
            if ($request->tipo_movimiento == 'Entrada') {
                $stockNuevo = $stockAnterior + $request->cantidad;
            } else {
                // Validar que haya suficiente stock para la salida
                if ($stockAnterior < $request->cantidad) {
                    return response()->json([
                        'error' => 'No hay suficiente stock disponible para realizar esta salida'
                    ], 422);
                }
                $stockNuevo = $stockAnterior - $request->cantidad;
            }

            // Crear el movimiento
            MovimientoInsumo::create([
                'insumo_id' => $request->insumo_id,
                'tipo_movimiento' => $request->tipo_movimiento,
                'cantidad' => $request->cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'motivo' => $request->motivo,
                'created_by' => Auth::id(),
            ]);

            // Actualizar el stock del insumo
            $insumo->stock_actual = $stockNuevo;
            $insumo->save();

            DB::commit();

            return response()->json([
                'success' => 'Movimiento de insumo registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al registrar el movimiento de insumo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $movimiento = MovimientoInsumo::with(['insumo', 'creadoPor'])->findOrFail($id);
        return response()->json($movimiento);
    }

    public function reporteExistencia()
    {
        // Insumos ya no tienen relación con proveedor (Santiago, e607f64).
        $insumos = Insumo::where('estado', true)->get();
        return view('admin.movimiento-insumo.reporte.index', compact('insumos'));
    }

    public function historialInsumo($id)
    {
        $insumo = Insumo::findOrFail($id);
        $movimientos = MovimientoInsumo::where('insumo_id', $id)
            ->with('creadoPor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.movimiento-insumo.movimientos.historial', compact('insumo', 'movimientos'));
    }

    public function alertasStock()
    {
        // El módulo de insumos eliminó la relación con proveedor (Santiago, e607f64).
        // Aquí solo listamos los insumos en alerta sin info de proveedor.
        $insumosConBajoStock = Insumo::where('estado', true)
            ->where('is_inventoriable', true)
            ->whereRaw('stock_actual <= stock_minimo')
            ->get();

        return view('admin.movimiento-insumo.alertas.index', compact('insumosConBajoStock'));
    }
}
