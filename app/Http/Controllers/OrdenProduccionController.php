<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\Producto;
use App\Models\Insumo;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenProduccionController extends Controller
{
    public function index()
    {
        $productos = Producto::where('estado', true)->get();
        $insumos = Insumo::where('estado', true)->get();
        // Obtener pedidos que no estén cancelados o completados
        $pedidos = Pedido::whereNotIn('estado', ['Cancelado', 'Completado'])
            ->with(['productos.producto'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('admin.ordenes.index', compact('productos', 'insumos', 'pedidos'));
    }

    public function getOrdenes()
    {
        $ordenes = OrdenProduccion::with(['producto.tipoProducto', 'creadoPor:id,name', 'pedido.cliente.persona'])->select('orden_produccion.*');

        return DataTables::of($ordenes)
            ->addColumn('pedido_info', function ($orden) {
                if ($orden->pedido_id && $orden->pedido) {
                    return '<div class="fw-medium text-center">Pedido #' . $orden->pedido->id . '</div>';
                } else {
                    return '<div class="fw-medium text-center text-muted">Orden Manual</div>';
                }
            })
            ->addColumn('creado_por', function ($orden) {
                return $orden->creadoPor ? $orden->creadoPor->name : 'N/A';
            })
            ->addColumn('actions', function ($orden) {
                $actions = '<div class="d-flex gap-2">';
                $actions .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $orden->id . '" title="Ver detalles"><i class="ri-eye-fill"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-success edit-btn" data-id="' . $orden->id . '" title="Editar orden"><i class="ri-pencil-fill"></i></button>';

                if ($orden->estado === 'Pendiente') {
                    $actions .= '<button type="button" class="btn btn-sm btn-danger remove-btn" data-id="' . $orden->id . '" title="Eliminar orden"><i class="ri-delete-bin-fill"></i></button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['pedido_info', 'actions'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pedido_id' => 'nullable|exists:pedido,id',
            'producto_id' => 'required|exists:producto,id',
            'cantidad_solicitada' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after:fecha_inicio',
            'costo_estimado' => 'required|numeric|min:0',
            'logo' => 'nullable|string',
            'notas' => 'nullable|string',
            'insumos' => 'required|array',
            'insumos.*.id' => 'required|exists:insumo,id',
            'insumos.*.cantidad_estimada' => 'required|numeric|min:0.01'
        ]);

        $orden = OrdenProduccion::create([
            'pedido_id' => $request->pedido_id,
            'producto_id' => $request->producto_id,
            'cantidad_solicitada' => $request->cantidad_solicitada,
            'cantidad_producida' => 0,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin_estimada' => $request->fecha_fin_estimada,
            'estado' => 'Pendiente',
            'costo_estimado' => $request->costo_estimado,
            'logo' => $request->logo,
            'notas' => $request->notas,
            'created_by' => Auth::id()
        ]);

        foreach ($request->insumos as $insumo) {
            $orden->insumos()->attach($insumo['id'], [
                'cantidad_estimada' => $insumo['cantidad_estimada'],
                'cantidad_utilizada' => 0
            ]);
        }

        return response()->json(['success' => 'Orden de producción creada exitosamente.']);
    }

    public function show($id)
    {
        $orden = OrdenProduccion::with(['producto.tipoProducto', 'insumos', 'creadoPor:id,name'])
            ->findOrFail($id);

        return response()->json($orden);
    }

    public function edit($id)
    {
        $orden = OrdenProduccion::with(['insumos'])->findOrFail($id);
        return response()->json($orden);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'producto_id' => 'required|exists:producto,id',
            'cantidad_solicitada' => 'required|integer|min:1',
            'fecha_inicio' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:Pendiente,En Proceso,Finalizado,Cancelado',
            'costo_estimado' => 'required|numeric|min:0',
            'logo' => 'nullable|string',
            'notas' => 'nullable|string',
            'insumos' => 'required|array',
            'insumos.*.id' => 'required|exists:insumo,id',
            'insumos.*.cantidad_estimada' => 'required|numeric|min:0.01'
        ]);

        $orden = OrdenProduccion::findOrFail($id);

        $orden->update([
            'producto_id' => $request->producto_id,
            'cantidad_solicitada' => $request->cantidad_solicitada,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin_estimada' => $request->fecha_fin_estimada,
            'estado' => $request->estado,
            'costo_estimado' => $request->costo_estimado,
            'logo' => $request->logo,
            'notas' => $request->notas
        ]);

        // Actualizar insumos
        $orden->insumos()->sync([]);
        foreach ($request->insumos as $insumo) {
            $orden->insumos()->attach($insumo['id'], [
                'cantidad_estimada' => $insumo['cantidad_estimada'],
                'cantidad_utilizada' => 0
            ]);
        }

        return response()->json(['success' => 'Orden de producción actualizada exitosamente.']);
    }

    public function destroy($id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        if ($orden->estado !== 'Pendiente') {
            return response()->json([
                'error' => 'No se puede eliminar una orden que no está en estado Pendiente'
            ], 422);
        }

        $orden->delete();
        return response()->json(['success' => 'Orden de producción eliminada exitosamente.']);
    }

    /**
     * Obtener datos de un pedido para crear orden de producción
     */
    public function getPedidoData($pedidoId)
    {
        $pedido = Pedido::with([
            'productos.producto',
            'productos.insumos' => function ($query) {
                $query->withPivot('cantidad_estimada');
            }
        ])
            ->findOrFail($pedidoId);


        return response()->json($pedido);
    }

}
