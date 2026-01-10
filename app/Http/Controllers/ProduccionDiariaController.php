<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\ProduccionDiaria;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProduccionDiariaController extends Controller
{
    public function index()
    {
        // Obtener empleados del departamento de Producción para asignar como operarios
        $operarios = \App\Models\Empleado::with('persona')
            ->where('departamento', 'Produccion')
            ->where('estado', 1)
            ->get()
            ->map(function ($empleado) {
                return (object) [
                    'id' => $empleado->id,
                    'name' => $empleado->persona->nombre_completo ?? 'Sin nombre'
                ];
            });
        $ordenes = OrdenProduccion::where('estado', 'Pendiente')
            ->where('cantidad_producida', '<', \DB::raw('cantidad_solicitada'))
            ->get();

        return view('admin.produccion.diaria.index', compact('operarios', 'ordenes'));
    }

    public function getRegistros()
    {
        $registros = ProduccionDiaria::with(['orden.producto', 'operario.persona'])
            ->select('produccion_diaria.*');

        return DataTables::of($registros)
            ->addColumn('orden_producto', function ($registro) {
                return $registro->orden && $registro->orden->producto ? $registro->orden->producto->nombre : 'N/A';
            })
            ->addColumn('operario_nombre', function ($registro) {
                return $registro->operario && $registro->operario->persona
                    ? $registro->operario->persona->nombre_completo
                    : 'N/A';
            })
            ->addColumn('fecha', function ($registro) {
                return $registro->created_at->format('d/m/Y');
            })
            ->addColumn('actions', function ($registro) {
                $actions = '<div class="text-center">';
                if (Auth::user()->isAdmin() || Auth::user()->isSupervisor()) {
                    $actions .= '<button type="button" class="btn btn-primary btn-sm view-btn me-2" data-id="' . $registro->id . '">';
                    $actions .= '<i class="ri-eye-line"></i></button>';

                    $actions .= '<button type="button" class="btn btn-warning btn-sm edit-btn me-2" data-id="' . $registro->id . '">';
                    $actions .= '<i class="ri-pencil-line"></i></button>';

                    $actions .= '<button type="button" class="btn btn-danger btn-sm remove-btn" data-id="' . $registro->id . '">';
                    $actions .= '<i class="ri-delete-bin-line"></i></button>';
                }
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:orden_produccion,id',
            'operario_id' => 'required|exists:empleado,id',
            'cantidad_producida' => 'required|numeric|min:1',
            'cantidad_defectuosa' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            $registro = ProduccionDiaria::create([
                'orden_id' => $request->orden_id,
                'operario_id' => $request->operario_id,
                'cantidad_producida' => $request->cantidad_producida,
                'cantidad_defectuosa' => $request->cantidad_defectuosa,
                'observaciones' => $request->observaciones
            ]);

            // Actualizar cantidad producida en la orden
            $orden = OrdenProduccion::find($request->orden_id);
            $orden->cantidad_producida += $request->cantidad_producida;
            if ($orden->cantidad_producida >= $orden->cantidad_solicitada) {
                $orden->estado = 'Finalizado';
            }
            $orden->save();

            return response()->json([
                'success' => 'Registro de producción creado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el registro de producción'
            ], 500);
        }
    }

    public function show($id)
    {
        $registro = ProduccionDiaria::with(['orden.producto', 'operario.persona'])->findOrFail($id);
        return response()->json($registro);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cantidad_producida' => 'required|numeric|min:1',
            'cantidad_defectuosa' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:500'
        ]);

        try {
            $registro = ProduccionDiaria::findOrFail($id);

            // Restar la cantidad anterior de la orden
            $orden = $registro->orden;
            $orden->cantidad_producida -= $registro->cantidad_producida;

            // Actualizar el registro
            $registro->update([
                'cantidad_producida' => $request->cantidad_producida,
                'cantidad_defectuosa' => $request->cantidad_defectuosa,
                'observaciones' => $request->observaciones
            ]);

            // Actualizar la nueva cantidad en la orden
            $orden->cantidad_producida += $request->cantidad_producida;
            if ($orden->cantidad_producida >= $orden->cantidad_solicitada) {
                $orden->estado = 'Finalizado';
            } else {
                $orden->estado = 'En Proceso';
            }
            $orden->save();

            return response()->json([
                'success' => 'Registro de producción actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el registro de producción'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $registro = ProduccionDiaria::findOrFail($id);

            // Actualizar cantidad producida en la orden
            $orden = $registro->orden;
            $orden->cantidad_producida -= $registro->cantidad_producida;
            if ($orden->cantidad_producida < $orden->cantidad_solicitada) {
                $orden->estado = 'En Proceso';
            }
            $orden->save();

            $registro->delete();

            return response()->json([
                'success' => 'Registro de producción eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar el registro de producción'
            ], 500);
        }
    }
}
