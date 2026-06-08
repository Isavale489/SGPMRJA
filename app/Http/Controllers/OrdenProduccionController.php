<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\SubOrdenProduccion;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Empleado;
use App\Services\ProduccionInventarioService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenProduccionController extends Controller
{
    public function __construct(
        private ProduccionInventarioService $inventario
    ) {
    }

    /**
     * Devuelve el payload de error 422 si el pedido no alcanza el abono mínimo
     * requerido para producir, o null si lo cumple. Centraliza la regla para
     * store() y storeBatch().
     */
    private function bloqueoPorAbonoMinimo(Pedido $pedido): ?array
    {
        if ($pedido->cumpleAbonoMinimo()) {
            return null;
        }

        $pct = rtrim(rtrim(number_format(Pedido::porcentajeAbonoMinimo(), 2, '.', ''), '0'), '.');

        return [
            'message' => "El pedido #{$pedido->id} no alcanza el abono mínimo del {$pct}% requerido para "
                . 'iniciar producción. Abono validado: ' . number_format($pedido->porcentajeAbonado(), 1) . '% ('
                . number_format((float) $pedido->abono, 2) . ' de ' . number_format((float) $pedido->total, 2) . '). '
                . 'Registra el abono en el pedido antes de generar sus órdenes.',
        ];
    }

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
                $actions .= '<button type="button" class="btn btn-sm btn-soft-primary subordenes-btn" data-id="' . $orden->id . '" title="Sub-órdenes y empleados"><i class="ri-node-tree"></i></button>';
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
            $lineas = $pedido->productos
                // Solo líneas fabricables: los productos de reventa
                // (tipo->requiere_produccion = false) se venden pero no se producen.
                ->filter(fn($d) => $d->requiereProduccion())
                ->map(function ($d) use ($detallesConOrden) {
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
                // Abono mínimo (regla de negocio): el front bloquea/avisa si no se cumple.
                'total'              => (float) $pedido->total,
                'abono'              => (float) $pedido->abono,
                'porcentaje_abonado' => $pedido->porcentajeAbonado(),
                'abono_minimo_pct'   => Pedido::porcentajeAbonoMinimo(),
                'cumple_abono'       => $pedido->cumpleAbonoMinimo(),
                'lineas'            => $lineas,
            ];
        })
        // Ocultar pedidos sin nada que producir (solo productos de reventa).
        ->filter(fn($p) => $p['total_lineas'] > 0)
        ->values();

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

        // Regla de negocio: el pedido debe alcanzar el abono mínimo para producir.
        $pedido = Pedido::findOrFail($detalle->pedido_id);
        if ($error = $this->bloqueoPorAbonoMinimo($pedido)) {
            return response()->json($error, 422);
        }

        // Una línea de pedido solo puede tener una orden activa a la vez
        $yaTieneOrden = OrdenProduccion::where('detalle_pedido_id', $detalle->id)
            ->where('estado', '!=', 'Cancelado')
            ->exists();
        if ($yaTieneOrden) {
            return response()->json([
                'message' => 'Este producto del pedido ya tiene una orden de producción activa.'
            ], 422);
        }

        try {
            // Crear la orden, asociar insumos y descontar stock en una sola
            // transacción: si falta stock, no se crea nada (rollback total).
            DB::transaction(function () use ($validated, $detalle, $request) {
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

                // Descuenta stock de los insumos inventariables (valida antes).
                $this->inventario->validarYDescontar($orden, Auth::id());
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
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

        // Regla de negocio: el pedido debe alcanzar el abono mínimo para producir.
        $pedido = Pedido::findOrFail($validated['pedido_id']);
        if ($error = $this->bloqueoPorAbonoMinimo($pedido)) {
            return response()->json($error, 422);
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
        try {
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

                    // Descuenta stock de los insumos inventariables (valida antes).
                    // Si alguna orden no tiene stock, el batch completo hace rollback.
                    $this->inventario->validarYDescontar($orden, Auth::id());

                    $creadas[] = $orden->id;
                }
            });
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

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
        $orden = OrdenProduccion::with('pedido')->findOrFail($id);

        // Bloqueo cruzado: si el pedido padre está cancelado, no se admite
        // ningún avance ni movimiento sobre sus órdenes.
        if ($orden->pedido && $orden->pedido->estado === 'Cancelado') {
            return response()->json([
                'message' => 'El pedido asociado está cancelado: no se pueden registrar avances en sus órdenes de producción.'
            ], 422);
        }

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
                'detallePedido.tipoProducto',
                'detallePedido.color',
                'detallePedido.talla',
                'detallePedido.bordados',
                'creadoPor:id,name,avatar',
            ])->findOrFail($id);

        // Nombre legible (legacy o dinámico desde snapshot) para líneas sin producto.
        $orden->append('nombre_producto');

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

        // 'Cancelado' no se setea aquí: la cancelación tiene su propio endpoint
        // (cancelar) porque define la reposición de stock y exige motivo de merma.
        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleado,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin_estimada' => 'required|date|after:fecha_inicio',
            'estado' => 'required|in:Pendiente,En Proceso,Finalizado',
            'notas' => 'nullable|string',
        ]);

        $fechaFinReal = $orden->fecha_fin_real;
        if ($validated['estado'] === 'Finalizado' && is_null($fechaFinReal)) {
            $fechaFinReal = now()->toDateString();
        } elseif ($validated['estado'] !== 'Finalizado') {
            $fechaFinReal = null;
        }

        // producto, cantidad e insumos quedan fijos: el producto/cantidad están
        // ligados a la línea del pedido y los insumos ya comprometieron stock al
        // crear la orden (editarlos exigiría reconciliar inventario → cancelar+recrear).
        $orden->update([
            'empleado_id' => $validated['empleado_id'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
            'estado' => $validated['estado'],
            'fecha_fin_real' => $fechaFinReal,
            'notas' => $validated['notas'] ?? null,
        ]);

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

        DB::transaction(function () use ($orden) {
            // La OP solo se puede eliminar estando 'Pendiente' (tela sin cortar):
            // se devuelve al inventario el stock comprometido al crearla.
            $this->inventario->reponer($orden, Auth::id());
            $orden->delete();
        });

        Pedido::find($pedidoId)?->recalcularEstado();
        return response()->json(['message' => 'Orden de producción eliminada exitosamente.']);
    }

    /**
     * Cancelar una orden de producción.
     *
     * Reposición de stock condicional al estatus al momento de cancelar (merma
     * en confección textil):
     *  - 'Pendiente': la tela no se ha cortado → se repone el stock comprometido.
     *  - 'En Proceso' / 'Finalizado': la tela ya se cortó (merma) → NO se repone
     *    y se exige un motivo que justifique la pérdida del material.
     */
    public function cancelar(Request $request, $id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        if ($orden->estado === 'Cancelado') {
            return response()->json(['message' => 'La orden ya está cancelada.'], 422);
        }

        // Solo la cancelación temprana (Pendiente) repone stock.
        $reponeStock = $orden->estado === 'Pendiente';

        $validated = $request->validate(
            [
                'motivo_cancelacion' => ($reponeStock ? 'nullable' : 'required') . '|string|max:500',
            ],
            [
                'motivo_cancelacion.required' =>
                    'La orden ya está en producción (material cortado): indica el motivo de la cancelación para justificar la merma.',
            ]
        );

        $pedidoId = $orden->pedido_id;

        DB::transaction(function () use ($orden, $validated, $reponeStock) {
            if ($reponeStock) {
                $this->inventario->reponer($orden, Auth::id());
            }

            $orden->update([
                'estado'             => 'Cancelado',
                'motivo_cancelacion' => $validated['motivo_cancelacion'] ?? null,
            ]);
        });

        Pedido::find($pedidoId)?->recalcularEstado();

        return response()->json([
            'message' => $reponeStock
                ? 'Orden cancelada. Se repuso el stock de los insumos al inventario.'
                : 'Orden cancelada. El material se registró como merma (sin reposición de stock).',
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  SUB-ÓRDENES DE PRODUCCIÓN (etapas con múltiples empleados asignados)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Sub-órdenes que dependen de la OP principal, con los empleados asignados
     * a cada una. Alimenta el modal dinámico de la vista de Producción.
     */
    public function subordenes($id)
    {
        $orden = OrdenProduccion::with([
                'subordenes.empleados.persona',
                'pedido',
            ])->findOrFail($id);
        $orden->append('nombre_producto');

        return response()->json([
            'orden' => [
                'id'        => $orden->id,
                'producto'  => $orden->nombre_producto,
                'pedido_id' => $orden->pedido_id,
                'estado'    => $orden->estado,
            ],
            'subordenes' => $orden->subordenes->map(fn($s) => [
                'id'                => $s->id,
                'nombre'            => $s->nombre,
                'cantidad_asignada' => $s->cantidad_asignada,
                'estado'            => $s->estado,
                'notas'             => $s->notas,
                'empleados'         => $s->empleados->map(fn($e) => [
                    'id'     => $e->id,
                    'nombre' => $e->persona->nombre_completo ?? ('Empleado #' . $e->id),
                    'rol'    => $e->pivot->rol,
                ])->values(),
            ])->values(),
        ]);
    }

    /**
     * Crear una sub-orden de la OP y asignarle uno o varios empleados.
     */
    public function storeSubOrden(Request $request, $id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        $validated = $request->validate([
            'nombre'            => 'required|string|max:120',
            'cantidad_asignada' => 'nullable|integer|min:1',
            'notas'             => 'nullable|string|max:500',
            'empleados'         => 'required|array|min:1',
            'empleados.*.id'    => 'required|integer|exists:empleado,id',
            'empleados.*.rol'   => 'nullable|string|max:80',
        ], [
            'empleados.required' => 'Asigna al menos un empleado a la sub-orden.',
            'empleados.min'      => 'Asigna al menos un empleado a la sub-orden.',
        ]);

        // Sin empleados duplicados en la misma sub-orden
        $ids = array_column($validated['empleados'], 'id');
        if (count($ids) !== count(array_unique($ids))) {
            return response()->json(['message' => 'Hay empleados repetidos en la asignación.'], 422);
        }

        $sub = DB::transaction(function () use ($orden, $validated) {
            $sub = $orden->subordenes()->create([
                'nombre'            => $validated['nombre'],
                'cantidad_asignada' => $validated['cantidad_asignada'] ?? null,
                'estado'            => 'Pendiente',
                'notas'             => $validated['notas'] ?? null,
            ]);

            $attach = [];
            foreach ($validated['empleados'] as $e) {
                $attach[$e['id']] = ['rol' => $e['rol'] ?? null];
            }
            $sub->empleados()->sync($attach);

            return $sub;
        });

        return response()->json([
            'message'      => 'Sub-orden creada y empleados asignados.',
            'sub_orden_id' => $sub->id,
        ]);
    }

    /**
     * Eliminar una sub-orden (y sus asignaciones por cascade en el pivot).
     */
    public function destroySubOrden($id, $subId)
    {
        $sub = SubOrdenProduccion::where('orden_produccion_id', $id)->findOrFail($subId);
        $sub->delete();

        return response()->json(['message' => 'Sub-orden eliminada.']);
    }
}
