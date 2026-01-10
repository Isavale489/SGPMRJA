<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\Insumo;
use App\Models\DetalleOrdenInsumo;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;

class DetalleOrdenInsumoController extends Controller
{
    public function index($ordenId)
    {
        $orden = OrdenProduccion::with(['producto', 'insumos'])->findOrFail($ordenId);
        $insumos = Insumo::where('estado', true)->get();
        
        return view('admin.ordenes.insumos.index', compact('orden', 'insumos'));
    }

    public function getInsumos($ordenId)
    {
        $detalles = DetalleOrdenInsumo::with(['insumo', 'ordenProduccion'])
            ->where('orden_produccion_id', $ordenId)
            ->get();

        return DataTables::of($detalles)
            ->addColumn('progreso', function ($detalle) {
                $porcentaje = ($detalle->cantidad_utilizada / $detalle->cantidad_estimada * 100);
                return round($porcentaje, 2);
            })
            ->addColumn('actions', function ($detalle) {
                return '<div class="d-flex gap-2">' .
                    '<button class="btn btn-sm btn-info update-btn" data-id="'.$detalle->id.'" title="Actualizar Uso">' .
                    '<i class="ri-edit-line"></i></button>' .
                    '<button class="btn btn-sm btn-danger remove-btn" data-id="'.$detalle->id.'" title="Eliminar">' .
                    '<i class="ri-delete-bin-line"></i></button>' .
                    '</div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request, $ordenId)
    {
        $request->validate([
            'insumo_id' => 'required|exists:insumo,id',
            'cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        $orden = OrdenProduccion::findOrFail($ordenId);
        
        // Verificar si el insumo ya existe en la orden
        $existente = DetalleOrdenInsumo::where('orden_produccion_id', $ordenId)
            ->where('insumo_id', $request->insumo_id)
            ->first();
            
        if ($existente) {
            return response()->json([
                'error' => 'Este insumo ya está asignado a la orden'
            ], 422);
        }

        $detalle = DetalleOrdenInsumo::create([
            'orden_produccion_id' => $ordenId,
            'insumo_id' => $request->insumo_id,
            'cantidad_estimada' => $request->cantidad_estimada,
            'cantidad_utilizada' => 0
        ]);

        return response()->json([
            'success' => 'Insumo agregado correctamente',
            'detalle' => $detalle
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad_utilizada' => 'required|numeric|min:0',
        ]);

        $detalle = DetalleOrdenInsumo::findOrFail($id);
        
        // Verificar que la cantidad utilizada no exceda la estimada
        if ($request->cantidad_utilizada > $detalle->cantidad_estimada) {
            return response()->json([
                'error' => 'La cantidad utilizada no puede ser mayor a la cantidad estimada'
            ], 422);
        }

        $detalle->update([
            'cantidad_utilizada' => $request->cantidad_utilizada
        ]);

        // Actualizar el stock del insumo
        DB::transaction(function () use ($detalle, $request) {
            $insumo = $detalle->insumo;
            $diferencia = $request->cantidad_utilizada - $detalle->getOriginal('cantidad_utilizada');
            
            if ($diferencia != 0) {
                $insumo->stock_actual -= $diferencia;
                $insumo->save();

                // Registrar el movimiento
                $insumo->movimientos()->create([
                    'tipo_movimiento' => 'Salida',
                    'cantidad' => abs($diferencia),
                    'stock_anterior' => $insumo->stock_actual + $diferencia,
                    'stock_nuevo' => $insumo->stock_actual,
                    'motivo' => "Uso en Orden de Producción #{$detalle->orden_produccion_id}",
                    'created_by' => auth()->id()
                ]);
            }
        });

        return response()->json([
            'success' => 'Cantidad actualizada correctamente',
            'detalle' => $detalle
        ]);
    }

    public function destroy($id)
    {
        $detalle = DetalleOrdenInsumo::findOrFail($id);
        
        // Solo permitir eliminar si no se ha utilizado nada del insumo
        if ($detalle->cantidad_utilizada > 0) {
            return response()->json([
                'error' => 'No se puede eliminar un insumo que ya ha sido utilizado'
            ], 422);
        }

        $detalle->delete();

        return response()->json([
            'success' => 'Insumo eliminado correctamente'
        ]);
    }
}
