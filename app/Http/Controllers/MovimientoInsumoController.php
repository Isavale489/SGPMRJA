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
        $insumos = Insumo::where('estado', true)->get();
        return view('admin.inventario.movimientos.index', compact('insumos'));
    }

    public function getMovimientos()
    {
        $movimientos = MovimientoInsumo::with(['insumo', 'creadoPor'])
            ->select('movimiento_insumo.insumo_id', 'movimiento_insumo.tipo_movimiento', 'movimiento_insumo.cantidad', 'movimiento_insumo.stock_anterior', 'movimiento_insumo.stock_nuevo', 'movimiento_insumo.motivo', 'movimiento_insumo.created_by', 'movimiento_insumo.created_at');

        return DataTables::of($movimientos)
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
                $actions = '<div class="text-center">';
                $actions .= '<button type="button" class="btn btn-primary btn-sm view-btn me-2" data-id="' . $movimiento->id . '">';
                $actions .= '<i class="ri-eye-line"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
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
                'success' => 'Movimiento de existencia registrado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Error al registrar el movimiento de existencia: ' . $e->getMessage()
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
        $insumos = Insumo::with('proveedor')->where('estado', true)->get();
        return view('admin.inventario.reporte.index', compact('insumos'));
    }

    public function historialInsumo($id)
    {
        $insumo = Insumo::findOrFail($id);
        $movimientos = MovimientoInsumo::where('insumo_id', $id)
            ->with('creadoPor')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.inventario.movimientos.historial', compact('insumo', 'movimientos'));
    }

    public function alertasStock()
    {
        $insumosConBajoStock = Insumo::where('estado', true)
            ->whereRaw('stock_actual <= stock_minimo')
            ->with('proveedor')
            ->get();

        return view('admin.inventario.alertas.index', compact('insumosConBajoStock'));
    }
}
