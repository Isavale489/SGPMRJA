<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenProduccionController extends Controller
{
    public function index()
    {
        $insumos = Insumo::where('estado', true)->get();

        // Empleados del departamento de Producción (asignables a una orden)
        $empleados = Empleado::with('persona')
            ->whereHas('departamento', fn($q) => $q->whereRaw("LOWER(nombre) LIKE 'producc%'"))
            ->where('estado', 1)
            ->get()
            ->map(fn($e) => (object)[
                'id'   => $e->id,
                'name' => $e->persona->nombre_completo ?? 'Sin nombre',
            ]);

        return view('admin.ordenes.index', compact('insumos', 'empleados'));
    }

    public function getOrdenes(Request $request)
    {
        $ordenes = OrdenProduccion::with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'empleado.persona', 'creadoPor:id,name', 'pedido.cliente.persona'])
            ->select('orden_produccion.*');

        if ($request->filled('filter_estado')) {
            $ordenes->where('orden_produccion.estado', $request->input('filter_estado'));
        }

        if ($request->filled('filter_fecha_desde')) {
            $ordenes->whereDate('orden_produccion.fecha_fin_estimada', '>=', $request->input('filter_fecha_desde'));
        }

        if ($request->filled('filter_fecha_hasta')) {
            $ordenes->whereDate('orden_produccion.fecha_fin_estimada', '<=', $request->input('filter_fecha_hasta'));
        }

        $orden = $request->input('filter_orden', 'recientes');

        switch ($orden) {
            case 'progreso_desc':
                $ordenes->orderByRaw('(orden_produccion.cantidad_producida / NULLIF(orden_produccion.cantidad_solicitada, 0)) desc');
                break;
            case 'progreso_asc':
                $ordenes->orderByRaw('(orden_produccion.cantidad_producida / NULLIF(orden_produccion.cantidad_solicitada, 0)) asc');
                break;
            case 'recientes':
            default:
                $ordenes->orderBy('orden_produccion.created_at', 'desc');
                break;
        }

        return DataTables::of($ordenes)
            ->addColumn('pedido_info', function ($orden) {
                if ($orden->pedido_id && $orden->pedido) {
                    return '<div class="fw-medium text-center">Pedido #' . $orden->pedido->id . '</div>';
                }
                return '<div class="fw-medium text-center text-muted">Orden Manual</div>';
            })
            ->addColumn('producto_info', function ($orden) {
                $producto = $orden->nombre_producto;
                $empleado = $orden->empleado && $orden->empleado->persona
                    ? $orden->empleado->persona->nombre_completo
                    : null;
                $html = '<div class="fw-medium">' . e($producto) . '</div>';
                if ($empleado) {
                    $html .= '<small class="text-muted"><i class="ri-user-line"></i> ' . e($empleado) . '</small>';
                }
                return $html;
            })
            ->addColumn('creado_por', function ($orden) {
                return $orden->creadoPor ? $orden->creadoPor->name : 'N/A';
            })
            ->addColumn('actions', function ($orden) {
                $actions = '<div class="d-flex gap-2 justify-content-center">';
                $actions .= '<button type="button" class="btn btn-sm btn-soft-info view-btn" data-id="' . $orden->id . '" title="Ver detalles"><i class="ri-eye-fill"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-soft-success edit-btn" data-id="' . $orden->id . '" title="Editar orden"><i class="ri-pencil-fill"></i></button>';

                if ($orden->estado === 'Pendiente') {
                    $actions .= '<button type="button" class="btn btn-sm btn-soft-danger remove-btn" data-id="' . $orden->id . '" title="Eliminar orden"><i class="ri-delete-bin-fill"></i></button>';
                }

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['pedido_info', 'producto_info', 'actions'])
            ->make(true);
    }

    /**
     * Pedidos activos con sus líneas de producto, marcando cuáles ya tienen
     * orden de producción. Alimenta el modal de selección (1 orden por línea).
     */
    public function pedidosDisponibles()
    {
        $pedidos = Pedido::with([
                'cliente.persona',
                'productos.producto.tipoProducto.insumosDefault',
                'productos.producto.tela',
                'productos.tipoProducto.insumosDefault', // líneas dinámicas (sin producto)
                'productos.color',
                'productos.talla',
                'productos.bordados',
            ])
            ->whereNotIn('estado', ['Cancelado', 'Completado'])
            ->orderBy('created_at', 'desc')
            ->get();

        // [detalle_pedido_id => orden_id] de las líneas con orden activa (no cancelada)
        $detallesConOrden = OrdenProduccion::whereIn('pedido_id', $pedidos->pluck('id'))
            ->whereNotNull('detalle_pedido_id')
            ->where('estado', '!=', 'Cancelado')
            ->pluck('id', 'detalle_pedido_id');

        $data = $pedidos->map(function ($pedido) use ($detallesConOrden) {
            $lineas = $pedido->productos->map(function ($d) use ($detallesConOrden) {
                // Tipo: legacy desde el producto; dinámico desde la relación directa.
                $tipo = $d->producto ? $d->producto->tipoProducto : $d->tipoProducto;

                // Insumos por defecto del tipo de producto (template para la orden).
                // Cantidad pivote = consumo por unidad → multiplicar por las unidades de la línea.
                $insumosDefault = $tipo
                    ? $tipo->insumosDefault->map(fn($i) => [
                        'id'        => $i->id,
                        'nombre'    => $i->nombre,
                        'unidad'    => $i->unidad_medida,
                        'cantidad'  => round((float) $i->pivot->cantidad_estimada * $d->cantidad, 2),
                    ])->values()
                    : collect();

                // Tela de la variante: legacy desde producto->tela; dinámico desde tela_snapshot.
                $telaId = $telaNombre = $telaUnidad = null;
                if ($d->producto && $d->producto->tela) {
                    $telaId = $d->producto->tela->id;
                    $telaNombre = $d->producto->tela->nombre;
                    $telaUnidad = $d->producto->tela->unidad_medida;
                } elseif (is_array($d->tela_snapshot) && !empty($d->tela_snapshot['id'])) {
                    $telaId = $d->tela_snapshot['id'];
                    $telaNombre = $d->tela_snapshot['nombre'] ?? 'Tela';
                    $telaUnidad = $d->tela_snapshot['unidad_medida'] ?? '';
                }

                // Auto-prefill de la tela: si el tipo requiere tela y define consumo por
                // unidad, se agrega con cantidad = consumo × unidades de la línea.
                if ($tipo && $tipo->requiere_tela && $tipo->consumo_tela_por_unidad > 0 && $telaId) {
                    $insumosDefault->prepend([
                        'id'        => $telaId,
                        'nombre'    => $telaNombre,
                        'unidad'    => $telaUnidad,
                        'cantidad'  => round((float) $tipo->consumo_tela_por_unidad * $d->cantidad, 2),
                    ]);
                }

                // Nombre legible de la línea (legacy o dinámico desde snapshot).
                $productoNombre = $d->producto
                    ? $d->producto->nombre
                    : (trim(($tipo->nombre ?? '') . ' ' . ($telaNombre ?? ''))
                        ?: ($d->sku_snapshot ?? ('Producto #' . $d->id)));

                return [
                    'detalle_id'      => $d->id,
                    'producto_id'     => $d->producto_id,
                    'producto_nombre' => $productoNombre,
                    'cantidad'        => $d->cantidad,
                    'color'           => $d->color->nombre ?? null,
                    'talla'           => $d->talla ? ($d->talla->etiqueta ?: $d->talla->nombre) : null,
                    'precio_unitario' => (float) $d->precio_unitario,
                    'subtotal'        => round($d->cantidad * $d->precio_unitario, 2),
                    'lleva_bordado'   => (bool) $d->lleva_bordado,
                    'bordados_count'  => $d->bordados->count(),
                    'orden_id'        => $detallesConOrden[$d->id] ?? null,
                    'insumos_default' => $insumosDefault,
                ];
            })->values();

            return [
                'id'                => $pedido->id,
                'cliente_nombre'    => $pedido->cliente_nombre_completo,
                'cliente_documento' => $pedido->cliente_documento ?? 'N/A',
                'fecha_pedido'      => optional($pedido->fecha_pedido)->format('d/m/Y'),
                'fecha_entrega'     => optional($pedido->fecha_entrega_estimada)->format('Y-m-d'),
                'estado'            => $pedido->estado,
                'total_lineas'      => $lineas->count(),
                'lineas_pendientes' => $lineas->whereNull('orden_id')->count(),
                'progreso'          => $pedido->progreso_produccion,
                'lineas'            => $lineas,
            ];
        })->values();

        return response()->json($data);
    }

    /**
     * Órdenes asignadas a un empleado — alimenta el modal "Mis Órdenes".
     * Solo consulta; las activas se muestran primero para registrar avance.
     */
    public function ordenesPorEmpleado($empleadoId)
    {
        $empleado = Empleado::with('persona')->findOrFail($empleadoId);

        $ordenes = OrdenProduccion::with(['producto', 'detallePedido.tipoProducto', 'pedido'])
            ->where('empleado_id', $empleadoId)
            ->orderByRaw("FIELD(estado,'En Proceso','Pendiente','Finalizado','Cancelado')")
            ->orderBy('fecha_fin_estimada')
            ->get()
            ->map(fn($o) => [
                'id'                  => $o->id,
                'pedido_id'           => $o->pedido_id,
                'producto'            => $o->nombre_producto,
                'cantidad_solicitada' => $o->cantidad_solicitada,
                'cantidad_producida'  => $o->cantidad_producida,
                'cantidad_defectuosa' => $o->cantidad_defectuosa,
                'progreso'            => round($o->progreso * 100, 1),
                'estado'              => $o->estado,
                'fecha_inicio'        => optional($o->fecha_inicio)->format('d/m/Y'),
                'fecha_fin_estimada'  => optional($o->fecha_fin_estimada)->format('d/m/Y'),
            ]);

        return response()->json([
            'empleado' => optional($empleado->persona)->nombre_completo ?? ('Empleado #' . $empleado->id),
            'resumen'  => [
                'total'       => $ordenes->count(),
                'pendientes'  => $ordenes->where('estado', 'Pendiente')->count(),
                'en_proceso'  => $ordenes->where('estado', 'En Proceso')->count(),
                'finalizadas' => $ordenes->where('estado', 'Finalizado')->count(),
            ],
            'ordenes'  => $ordenes->values(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'detalle_pedido_id' => 'required|exists:detalle_pedido,id',
            'empleado_id' => 'required|exists:empleado,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after:fecha_inicio',
            'notas' => 'nullable|string',
            'insumos' => 'required|array|min:1',
            'insumos.*.id' => 'required|exists:insumo,id',
            'insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        $detalle = DetallePedido::findOrFail($validated['detalle_pedido_id']);

        // Una línea de pedido solo puede tener una orden activa a la vez
        $yaTieneOrden = OrdenProduccion::where('detalle_pedido_id', $detalle->id)
            ->where('estado', '!=', 'Cancelado')
            ->exists();
        if ($yaTieneOrden) {
            return response()->json([
                'message' => 'Este producto del pedido ya tiene una orden de producción activa.'
            ], 422);
        }

        // producto y cantidad se derivan de la línea (autoritativo, no del cliente)
        $orden = OrdenProduccion::create([
            'pedido_id' => $detalle->pedido_id,
            'detalle_pedido_id' => $detalle->id,
            'producto_id' => $detalle->producto_id,
            'empleado_id' => $validated['empleado_id'],
            'cantidad_solicitada' => $detalle->cantidad,
            'cantidad_producida' => 0,
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
            'estado' => 'Pendiente',
            'notas' => $validated['notas'] ?? null,
            'created_by' => Auth::id(),
        ]);

        foreach ($request->insumos as $insumo) {
            $orden->insumos()->attach($insumo['id'], [
                'cantidad_estimada' => $insumo['cantidad_estimada'],
                'cantidad_utilizada' => 0,
            ]);
        }

        Pedido::find($detalle->pedido_id)?->recalcularEstado();

        return response()->json(['message' => 'Orden de producción creada exitosamente.']);
    }

    /**
     * Crear varias órdenes del mismo pedido en una sola transacción.
     * Cada orden trae sus propios empleado/fechas/costo/insumos. Si alguna
     * línea ya tiene orden activa o falla la validación, no se crea ninguna.
     */
    public function storeBatch(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedido,id',
            'ordenes' => 'required|array|min:1',
            'ordenes.*.detalle_pedido_id' => 'required|exists:detalle_pedido,id',
            'ordenes.*.empleado_id' => 'required|exists:empleado,id',
            'ordenes.*.fecha_inicio' => 'required|date',
            'ordenes.*.fecha_fin_estimada' => 'required|date|after:ordenes.*.fecha_inicio',
            'ordenes.*.notas' => 'nullable|string',
            'ordenes.*.insumos' => 'required|array|min:1',
            'ordenes.*.insumos.*.id' => 'required|exists:insumo,id',
            'ordenes.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        // Detectar duplicados dentro del mismo batch (mismo detalle_pedido_id dos veces)
        $detalleIds = collect($validated['ordenes'])->pluck('detalle_pedido_id');
        if ($detalleIds->count() !== $detalleIds->unique()->count()) {
            return response()->json([
                'message' => 'El batch contiene líneas duplicadas. Cada línea del pedido debe aparecer una sola vez.'
            ], 422);
        }

        // Todas las líneas deben pertenecer al mismo pedido_id (anti-tampering)
        $detalles = DetallePedido::whereIn('id', $detalleIds)
            ->where('pedido_id', $validated['pedido_id'])
            ->get()->keyBy('id');
        if ($detalles->count() !== $detalleIds->count()) {
            return response()->json([
                'message' => 'Una o más líneas no pertenecen al pedido indicado.'
            ], 422);
        }

        // Ninguna línea debe tener ya orden activa (no Cancelada)
        $yaConOrden = OrdenProduccion::whereIn('detalle_pedido_id', $detalleIds)
            ->where('estado', '!=', 'Cancelado')
            ->pluck('detalle_pedido_id');
        if ($yaConOrden->isNotEmpty()) {
            return response()->json([
                'message' => 'Algunas líneas seleccionadas ya tienen orden activa. Recarga e intenta de nuevo.',
            ], 422);
        }

        $creadas = [];
        DB::transaction(function () use ($validated, $detalles, &$creadas) {
            foreach ($validated['ordenes'] as $o) {
                $detalle = $detalles[$o['detalle_pedido_id']];
                $orden = OrdenProduccion::create([
                    'pedido_id'           => $detalle->pedido_id,
                    'detalle_pedido_id'   => $detalle->id,
                    'producto_id'         => $detalle->producto_id,
                    'empleado_id'         => $o['empleado_id'],
                    'cantidad_solicitada' => $detalle->cantidad,
                    'cantidad_producida'  => 0,
                    'cantidad_defectuosa' => 0,
                    'fecha_inicio'        => $o['fecha_inicio'],
                    'fecha_fin_estimada'  => $o['fecha_fin_estimada'],
                    'estado'              => 'Pendiente',
                    'notas'               => $o['notas'] ?? null,
                    'created_by'          => Auth::id(),
                ]);
                foreach ($o['insumos'] as $ins) {
                    $orden->insumos()->attach($ins['id'], [
                        'cantidad_estimada' => $ins['cantidad_estimada'],
                        'cantidad_utilizada' => 0,
                    ]);
                }
                $creadas[] = $orden->id;
            }
        });

        Pedido::find($validated['pedido_id'])?->recalcularEstado();

        return response()->json([
            'message' => count($creadas) . ' ' . (count($creadas) === 1 ? 'orden creada' : 'órdenes creadas') . ' correctamente.',
            'ordenes' => $creadas,
        ]);
    }

    public function show($id)
    {
        $orden = OrdenProduccion::with([
                'producto.tipoProducto',
                'empleado.persona',
                'detallePedido.tipoProducto',
                'detallePedido.bordados.logo',
                'detallePedido.color',
                'detallePedido.talla',
                'insumos',
                'creadoPor:id,name',
            ])->findOrFail($id);

        // Expone el nombre legible (legacy o dinámico desde snapshot) al front.
        $orden->append('nombre_producto');

        return response()->json($orden);
    }

    /**
     * Registrar un avance de producción directamente sobre la orden.
     * Acumula cantidad_producida / cantidad_defectuosa y actualiza el estado.
     * El empleado responsable es el asignado a la orden (empleado_id).
     */
    public function registrarAvance(Request $request, $id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        if (in_array($orden->estado, ['Finalizado', 'Cancelado'])) {
            return response()->json([
                'message' => "La orden ya está en estado \"{$orden->estado}\" y no puede recibir más avances."
            ], 422);
        }

        $restante = $orden->cantidad_solicitada - $orden->cantidad_producida;

        $validated = $request->validate([
            'cantidad_producida'  => 'required|integer|min:1|max:' . max(1, $restante),
            'cantidad_defectuosa' => 'nullable|integer|min:0|lte:cantidad_producida',
        ]);

        $orden->cantidad_producida += $validated['cantidad_producida'];
        $orden->cantidad_defectuosa += ($validated['cantidad_defectuosa'] ?? 0);

        if ($orden->cantidad_producida >= $orden->cantidad_solicitada) {
            $orden->estado = 'Finalizado';
            $orden->fecha_fin_real = now()->toDateString();
        } elseif ($orden->estado === 'Pendiente') {
            $orden->estado = 'En Proceso';
        }
        $orden->save();

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return response()->json(['message' => 'Avance registrado correctamente.']);
    }

    public function edit($id)
    {
        $orden = OrdenProduccion::with([
                'insumos',
                'producto',
                'empleado.persona',
                'pedido.cliente',
                'detallePedido.color',
                'detallePedido.talla',
                'detallePedido.bordados',
                'creadoPor:id,name,avatar',
            ])->findOrFail($id);

        $data = $orden->toArray();
        // Cliente del pedido y creador real (para el chip "Registrada por").
        $data['cliente_nombre'] = $orden->pedido?->cliente_nombre_completo;
        $data['creador'] = $orden->creadoPor ? [
            'name'       => $orden->creadoPor->name,
            'avatar_url' => $orden->creadoPor->avatar_url,
        ] : null;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleado,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:Pendiente,En Proceso,Finalizado,Cancelado',
            'notas' => 'nullable|string',
            'insumos' => 'required|array|min:1',
            'insumos.*.id' => 'required|exists:insumo,id',
            'insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        $fechaFinReal = $orden->fecha_fin_real;
        if ($validated['estado'] === 'Finalizado' && is_null($fechaFinReal)) {
            $fechaFinReal = now()->toDateString();
        } elseif ($validated['estado'] !== 'Finalizado') {
            $fechaFinReal = null;
        }

        // producto y cantidad quedan ligados a la línea del pedido (no se editan aquí)
        $orden->update([
            'empleado_id' => $validated['empleado_id'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
            'estado' => $validated['estado'],
            'fecha_fin_real' => $fechaFinReal,
            'notas' => $validated['notas'] ?? null,
        ]);

        $orden->insumos()->sync([]);
        foreach ($request->insumos as $insumo) {
            $orden->insumos()->attach($insumo['id'], [
                'cantidad_estimada' => $insumo['cantidad_estimada'],
                'cantidad_utilizada' => 0,
            ]);
        }

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return response()->json(['message' => 'Orden de producción actualizada exitosamente.']);
    }

    public function destroy($id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        if ($orden->estado !== 'Pendiente') {
            return response()->json([
                'message' => 'No se puede eliminar una orden que no está en estado Pendiente'
            ], 422);
        }

        $pedidoId = $orden->pedido_id;
        $orden->delete();
        Pedido::find($pedidoId)?->recalcularEstado();
        return response()->json(['message' => 'Orden de producción eliminada exitosamente.']);
    }
}
