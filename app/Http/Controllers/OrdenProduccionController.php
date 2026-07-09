<?php

namespace App\Http\Controllers;

use App\Models\OrdenProduccion;
use App\Models\SubOrdenProduccion;
use App\Models\Insumo;
use App\Models\Pedido;
use App\Models\DetallePedido;
use App\Models\Empleado;
use App\Exceptions\StockInsuficienteException;
use App\Services\ProduccionInventarioService;
use App\Services\DisponibilidadInsumoService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrdenProduccionController extends Controller
{
    public function __construct(
        private ProduccionInventarioService $inventario,
        private DisponibilidadInsumoService $disponibilidad
    ) {
    }

    /**
     * Aviso de stock proyectado (NO bloqueante) para el wizard de Órdenes:
     * agrega los insumos REALES de las órdenes que se están armando y los compara
     * contra el stock. Devuelve el mismo shape que consume proyeccion-insumos.js.
     */
    public function proyeccionInsumos(Request $request)
    {
        $validated = $request->validate([
            'insumos'              => 'present|array',
            'insumos.*.insumo_id'  => 'required|integer',
            'insumos.*.cantidad'   => 'required|numeric|min:0',
        ]);

        $requeridos = [];
        foreach ($validated['insumos'] as $i) {
            $id = (int) $i['insumo_id'];
            $requeridos[$id] = ($requeridos[$id] ?? 0) + (float) $i['cantidad'];
        }

        return response()->json($this->disponibilidad->proyectarInsumos($requeridos));
    }

    /**
     * Lista de faltantes (insumo → cuánto comprar) para prellenar una compra,
     * agregando los insumos de una o varias órdenes. Se usa al responder el 422
     * por stock insuficiente. Devuelve [{insumo_id, nombre, codigo, unidad, cantidad}].
     */
    private function faltantesParaCompra(array $listasInsumos): array
    {
        $requeridos = [];
        foreach ($listasInsumos as $lista) {
            foreach (($lista ?? []) as $ins) {
                $id = (int) ($ins['id'] ?? 0);
                if (!$id) {
                    continue;
                }
                $requeridos[$id] = ($requeridos[$id] ?? 0) + (float) ($ins['cantidad_estimada'] ?? 0);
            }
        }

        $proy = $this->disponibilidad->proyectarInsumos($requeridos);

        return collect($proy['items'] ?? [])
            ->where('estado', 'falta')
            ->map(fn ($it) => [
                'insumo_id' => $it['insumo_id'],
                'nombre'    => $it['nombre'],
                'codigo'    => $it['codigo'],
                'unidad'    => $it['unidad'],
                'cantidad'  => $it['faltante'],
            ])
            ->values()
            ->all();
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

    /**
     * Órdenes de un pedido (o las manuales) para el DataTable del modal
     * "Ver órdenes". `pedido_id` = id numérico o 'manual' (sin pedido).
     */
    public function getOrdenes(Request $request)
    {
        $ordenes = OrdenProduccion::with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'empleado.persona', 'creadoPor:id,name', 'pedido.cliente.persona'])
            ->select('orden_produccion.*');

        if ($request->filled('pedido_id')) {
            $request->input('pedido_id') === 'manual'
                ? $ordenes->whereNull('orden_produccion.pedido_id')
                : $ordenes->where('orden_produccion.pedido_id', $request->input('pedido_id'));
        }

        $ordenes->orderByDesc('orden_produccion.created_at');

        return DataTables::of($ordenes)
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
            ->rawColumns(['producto_info', 'actions'])
            ->make(true);
    }

    /**
     * Tabla principal de /ordenes: una fila por pedido (más una para las
     * órdenes manuales) con agregados de sus órdenes de producción. El detalle
     * por orden vive en el modal "Ver órdenes" (getOrdenes con pedido_id).
     *
     * Los filtros de estado/fecha se aplican ANTES de agrupar: el pedido
     * aparece solo si tiene órdenes que cumplan, y los agregados (conteos,
     * progreso) reflejan únicamente esas órdenes.
     */
    public function getPedidosOrdenes(Request $request)
    {
        $pedidos = OrdenProduccion::query()
            ->leftJoin('pedido', 'pedido.id', '=', 'orden_produccion.pedido_id')
            ->leftJoin('cliente', 'cliente.id', '=', 'pedido.cliente_id')
            ->leftJoin('persona', 'persona.id', '=', 'cliente.persona_id')
            ->groupBy('orden_produccion.pedido_id')
            // MAX() sobre persona.nombre: valor único por grupo (1 pedido = 1 cliente),
            // envuelto en agregado para cumplir ONLY_FULL_GROUP_BY de MySQL 8.
            ->selectRaw("
                orden_produccion.pedido_id,
                MAX(persona.nombre) as cliente_nombre,
                COUNT(*) as total_ordenes,
                SUM(orden_produccion.estado = 'Pendiente')  as pendientes,
                SUM(orden_produccion.estado = 'En Proceso') as en_proceso,
                SUM(orden_produccion.estado = 'Finalizado') as finalizadas,
                SUM(orden_produccion.estado = 'Cancelado')  as canceladas,
                SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_solicitada, 0)) as solicitado,
                SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_producida, 0))  as producido,
                MAX(orden_produccion.created_at) as ultima_orden
            ");

        if ($request->filled('filter_estado')) {
            $pedidos->where('orden_produccion.estado', $request->input('filter_estado'));
        }

        if ($request->filled('filter_fecha_desde')) {
            $pedidos->whereDate('orden_produccion.fecha_fin_estimada', '>=', $request->input('filter_fecha_desde'));
        }

        if ($request->filled('filter_fecha_hasta')) {
            $pedidos->whereDate('orden_produccion.fecha_fin_estimada', '<=', $request->input('filter_fecha_hasta'));
        }

        // El progreso global excluye canceladas (mismos SUM del select).
        $progreso = "SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_producida, 0))
            / NULLIF(SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_solicitada, 0)), 0)";

        switch ($request->input('filter_orden', 'recientes')) {
            case 'progreso_desc':
                $pedidos->orderByRaw("({$progreso}) desc");
                break;
            case 'progreso_asc':
                $pedidos->orderByRaw("({$progreso}) asc");
                break;
            case 'recientes':
            default:
                $pedidos->orderByRaw('MAX(orden_produccion.created_at) desc');
                break;
        }

        return DataTables::of($pedidos)
            ->filter(function ($query) use ($request) {
                $kw = trim((string) $request->input('search.value', ''));
                if ($kw === '') {
                    return;
                }
                $query->where(function ($w) use ($kw) {
                    $w->where('persona.nombre', 'like', "%{$kw}%");
                    if (preg_match('/\d+/', $kw, $m)) {
                        // Acepta "11" o "Pedido #11"
                        $w->orWhereRaw('CAST(orden_produccion.pedido_id AS CHAR) LIKE ?', ["%{$m[0]}%"]);
                    }
                    if (stripos('manual', $kw) !== false || stripos('manuales', $kw) !== false) {
                        $w->orWhereNull('orden_produccion.pedido_id');
                    }
                });
            })
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
                'productos.genero',
                'productos.bordados',
            ])
            ->whereNotIn('estado', ['Cancelado', 'Completado'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Unidades comprometidas por línea en órdenes activas (no canceladas).
        // Una línea admite VARIAS órdenes (reparto entre empleados) y sigue
        // disponible mientras le queden unidades sin asignar.
        $asignadoPorDetalle = OrdenProduccion::whereIn('pedido_id', $pedidos->pluck('id'))
            ->whereNotNull('detalle_pedido_id')
            ->where('estado', '!=', 'Cancelado')
            ->selectRaw('detalle_pedido_id, SUM(cantidad_solicitada) as asignado, COUNT(*) as ordenes')
            ->groupBy('detalle_pedido_id')
            ->get()
            ->keyBy('detalle_pedido_id');

        $data = $pedidos->map(function ($pedido) use ($asignadoPorDetalle) {
            $lineas = $pedido->productos
                // Solo líneas fabricables: los productos de reventa
                // (tipo->requiere_produccion = false) se venden pero no se producen.
                ->filter(fn($d) => $d->requiereProduccion())
                ->map(function ($d) use ($asignadoPorDetalle) {
                // Tipo: legacy desde el producto; dinámico desde la relación directa.
                $tipo = $d->producto ? $d->producto->tipoProducto : $d->tipoProducto;

                // Insumos por defecto del tipo de producto (template para la orden).
                // Se envía el consumo POR UNIDAD: el wizard lo multiplica por las
                // unidades asignadas a cada orden (la línea puede repartirse).
                $insumosDefault = $tipo
                    ? $tipo->insumosDefault->map(fn($i) => [
                        'id'                => $i->id,
                        'nombre'            => $i->nombre,
                        'unidad'            => $i->unidad_medida,
                        'cantidad_unitaria' => (float) $i->pivot->cantidad_estimada,
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

                // Auto-prefill de la tela: si el tipo requiere tela y define consumo
                // por unidad, se agrega (también como consumo unitario).
                if ($tipo && $tipo->requiere_tela && $tipo->consumo_tela_por_unidad > 0 && $telaId) {
                    $insumosDefault->prepend([
                        'id'                => $telaId,
                        'nombre'            => $telaNombre,
                        'unidad'            => $telaUnidad,
                        'cantidad_unitaria' => (float) $tipo->consumo_tela_por_unidad,
                    ]);
                }

                // Nombre legible de la línea (legacy o dinámico desde snapshot).
                $productoNombre = $d->producto
                    ? $d->producto->nombre
                    : (trim(($tipo->nombre ?? '') . ' ' . ($telaNombre ?? ''))
                        ?: ($d->sku_snapshot ?? ('Producto #' . $d->id)));

                $asignado = (int) ($asignadoPorDetalle[$d->id]->asignado ?? 0);

                return [
                    'detalle_id'         => $d->id,
                    'producto_id'        => $d->producto_id,
                    'producto_nombre'    => $productoNombre,
                    'cantidad'           => $d->cantidad,
                    'cantidad_asignada'  => min($asignado, $d->cantidad),
                    'cantidad_pendiente' => max(0, $d->cantidad - $asignado),
                    'ordenes_activas'    => (int) ($asignadoPorDetalle[$d->id]->ordenes ?? 0),
                    'color'              => $d->color->nombre ?? null,
                    'talla'              => $d->talla ? ($d->talla->etiqueta ?: $d->talla->nombre) : null,
                    'genero'             => $d->genero->nombre ?? null,
                    'precio_unitario'    => (float) $d->precio_unitario,
                    'subtotal'           => round($d->cantidad * $d->precio_unitario, 2),
                    'lleva_bordado'      => (bool) $d->lleva_bordado,
                    'bordados_count'     => $d->bordados->count(),
                    'insumos_default'    => $insumosDefault,
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
                'lineas_pendientes' => $lineas->where('cantidad_pendiente', '>', 0)->count(),
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

        $ordenes = OrdenProduccion::with(['producto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'pedido'])
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

    /**
     * Unidades de la línea ya comprometidas en órdenes activas (no canceladas).
     * Para validar con seguridad, llamar dentro de una transacción después de
     * un lockForUpdate sobre la línea (serializa asignaciones concurrentes).
     */
    private function cantidadAsignadaActiva(int $detalleId, ?int $excluirOrdenId = null): int
    {
        return (int) OrdenProduccion::where('detalle_pedido_id', $detalleId)
            ->where('estado', '!=', 'Cancelado')
            ->when($excluirOrdenId, fn ($q) => $q->where('id', '!=', $excluirOrdenId))
            ->sum('cantidad_solicitada');
    }

    /**
     * Sincroniza el equipo de la orden con su reparto de unidades por empleado.
     *
     * $empleados es un arreglo de objetos {id, cantidad}. Valida que la suma del
     * reparto iguale exactamente la cantidad solicitada de la orden (invariante).
     * Preserva lo ya producido/defectuoso de los empleados que continúan, y prohíbe
     * quitar del equipo a quien ya registró producción. Llamar dentro de la misma
     * transacción que crea/actualiza la orden.
     *
     * @throws \InvalidArgumentException si el reparto no cuadra o se quita a alguien con avance.
     */
    private function syncEmpleadosConCantidad(OrdenProduccion $orden, array $empleados): void
    {
        $suma = collect($empleados)->sum(fn ($e) => (int) $e['cantidad']);
        if ($suma !== (int) $orden->cantidad_solicitada) {
            throw new \InvalidArgumentException(
                "El reparto por empleado ({$suma}) no coincide con las {$orden->cantidad_solicitada} unidades de la orden."
            );
        }

        // Pivot actual (para preservar producido/defectuoso de quienes continúan).
        $previo = $orden->empleadosAsignados()->get()
            ->keyBy('id')
            ->map(fn ($e) => [
                'producida'  => (int) $e->pivot->cantidad_producida,
                'defectuosa' => (int) $e->pivot->cantidad_defectuosa,
            ]);

        $nuevosIds = collect($empleados)->pluck('id')->map(fn ($i) => (int) $i)->all();

        // Nadie con avance puede ser retirado del equipo.
        foreach ($previo as $empId => $datos) {
            if (!in_array((int) $empId, $nuevosIds, true) && ($datos['producida'] > 0 || $datos['defectuosa'] > 0)) {
                throw new \InvalidArgumentException(
                    'No se puede quitar del equipo a un empleado que ya registró producción. Cancela y recrea la orden si necesitas rehacer el reparto.'
                );
            }
        }

        $syncData = [];
        foreach ($empleados as $e) {
            $id = (int) $e['id'];
            $syncData[$id] = [
                'cantidad'            => (int) $e['cantidad'],
                'cantidad_producida'  => $previo[$id]['producida']  ?? 0,
                'cantidad_defectuosa' => $previo[$id]['defectuosa'] ?? 0,
            ];
        }
        $orden->empleadosAsignados()->sync($syncData);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'detalle_pedido_id'    => 'required|exists:detalle_pedido,id',
            'empleados'            => 'required|array|min:1',
            'empleados.*.id'       => 'required|exists:empleado,id',
            'empleados.*.cantidad' => 'required|integer|min:1',
            'cantidad'             => 'nullable|integer|min:1',
            'fecha_inicio'         => 'required|date',
            'fecha_fin_estimada'   => 'required|date|after:fecha_inicio',
            'notas'                => 'nullable|string',
            'insumos'              => 'required|array|min:1',
            'insumos.*.id'         => 'required|exists:insumo,id',
            'insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        $detalle = DetallePedido::findOrFail($validated['detalle_pedido_id']);

        // Regla de negocio: el pedido debe alcanzar el abono mínimo para producir.
        $pedido = Pedido::findOrFail($detalle->pedido_id);
        if ($error = $this->bloqueoPorAbonoMinimo($pedido)) {
            return response()->json($error, 422);
        }

        try {
            // Crear la orden, asociar insumos y descontar stock en una sola
            // transacción: si falta stock, no se crea nada (rollback total).
            DB::transaction(function () use ($validated, $detalle, $request) {
                // Lock sobre la línea: serializa asignaciones concurrentes para que
                // la suma de órdenes activas nunca supere las unidades de la línea.
                $detalle = DetallePedido::whereKey($detalle->id)->lockForUpdate()->firstOrFail();

                $disponible = $detalle->cantidad - $this->cantidadAsignadaActiva($detalle->id);
                $cantidad   = (int) ($validated['cantidad'] ?? $disponible);
                if ($disponible < 1) {
                    throw new \InvalidArgumentException('Esta línea del pedido ya tiene todas sus unidades asignadas a órdenes activas.');
                }
                if ($cantidad > $disponible) {
                    throw new \InvalidArgumentException("Solo quedan {$disponible} unidades sin asignar en esta línea (se intentó asignar {$cantidad}).");
                }

                $orden = OrdenProduccion::create([
                    'pedido_id'           => $detalle->pedido_id,
                    'detalle_pedido_id'   => $detalle->id,
                    'producto_id'         => $detalle->producto_id,
                    'empleado_id'         => $validated['empleados'][0]['id'], // responsable principal
                    'cantidad_solicitada' => $cantidad,
                    'cantidad_producida'  => 0,
                    'fecha_inicio'        => $validated['fecha_inicio'],
                    'fecha_fin_estimada'  => $validated['fecha_fin_estimada'],
                    'estado'              => 'Pendiente',
                    'notas'               => $validated['notas'] ?? null,
                    'created_by'          => Auth::id(),
                ]);

                $this->syncEmpleadosConCantidad($orden, $validated['empleados']);

                foreach ($request->insumos as $insumo) {
                    $orden->insumos()->attach($insumo['id'], [
                        'cantidad_estimada' => $insumo['cantidad_estimada'],
                        'cantidad_utilizada' => 0,
                    ]);
                }

                $this->inventario->validarYDescontar($orden, Auth::id());
            });
        } catch (StockInsuficienteException $e) {
            // Faltantes estructurados para prellenar la compra (atajo del wizard).
            return response()->json([
                'message'   => $e->getMessage(),
                'faltantes' => $this->faltantesParaCompra([$validated['insumos']]),
            ], 422);
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
            'pedido_id'                           => 'required|exists:pedido,id',
            'ordenes'                             => 'required|array|min:1',
            'ordenes.*.detalle_pedido_id'         => 'required|exists:detalle_pedido,id',
            'ordenes.*.empleados'                 => 'required|array|min:1',
            'ordenes.*.empleados.*.id'            => 'required|exists:empleado,id',
            'ordenes.*.empleados.*.cantidad'      => 'required|integer|min:1',
            'ordenes.*.cantidad'                  => 'required|integer|min:1',
            'ordenes.*.fecha_inicio'              => 'required|date',
            'ordenes.*.fecha_fin_estimada'        => 'required|date|after:ordenes.*.fecha_inicio',
            'ordenes.*.notas'                     => 'nullable|string',
            'ordenes.*.insumos'                   => 'required|array|min:1',
            'ordenes.*.insumos.*.id'              => 'required|exists:insumo,id',
            'ordenes.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
        ]);

        // Una misma línea PUEDE aparecer varias veces: su producción se reparte
        // entre varias órdenes/empleados. La integridad la da la validación de
        // suma de cantidades dentro de la transacción (con lock por línea).
        $detalleIds = collect($validated['ordenes'])->pluck('detalle_pedido_id')->unique()->values();

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

        $creadas = [];
        try {
            DB::transaction(function () use ($validated, $detalleIds, &$creadas) {
                // Lock por línea: la suma de cantidades de órdenes activas (las
                // previas + las de este batch) no puede superar las unidades de
                // la línea, incluso con asignaciones concurrentes.
                $detalles = DetallePedido::whereIn('id', $detalleIds)
                    ->lockForUpdate()->get()->keyBy('id');

                foreach ($detalles as $detalle) {
                    $solicitado = collect($validated['ordenes'])
                        ->where('detalle_pedido_id', $detalle->id)
                        ->sum('cantidad');
                    $disponible = $detalle->cantidad - $this->cantidadAsignadaActiva($detalle->id);
                    if ($solicitado > $disponible) {
                        $nombre = $detalle->producto->nombre ?? $detalle->sku_snapshot ?? ('línea #' . $detalle->id);
                        throw new \InvalidArgumentException(
                            "\"{$nombre}\" solo tiene {$disponible} unidades sin asignar y se intentó asignar {$solicitado}. Recarga e intenta de nuevo."
                        );
                    }
                }

                foreach ($validated['ordenes'] as $o) {
                    $detalle = $detalles[$o['detalle_pedido_id']];
                    $orden = OrdenProduccion::create([
                        'pedido_id'           => $detalle->pedido_id,
                        'detalle_pedido_id'   => $detalle->id,
                        'producto_id'         => $detalle->producto_id,
                        'empleado_id'         => $o['empleados'][0]['id'], // responsable principal
                        'cantidad_solicitada' => (int) $o['cantidad'],
                        'cantidad_producida'  => 0,
                        'cantidad_defectuosa' => 0,
                        'fecha_inicio'        => $o['fecha_inicio'],
                        'fecha_fin_estimada'  => $o['fecha_fin_estimada'],
                        'estado'              => 'Pendiente',
                        'notas'               => $o['notas'] ?? null,
                        'created_by'          => Auth::id(),
                    ]);

                    $this->syncEmpleadosConCantidad($orden, $o['empleados']);

                    foreach ($o['insumos'] as $ins) {
                        $orden->insumos()->attach($ins['id'], [
                            'cantidad_estimada' => $ins['cantidad_estimada'],
                            'cantidad_utilizada' => 0,
                        ]);
                    }

                    $this->inventario->validarYDescontar($orden, Auth::id());

                    $creadas[] = $orden->id;
                }
            });
        } catch (StockInsuficienteException $e) {
            // Faltante agregado entre TODAS las órdenes del batch (lo que hay que
            // comprar de verdad), para prellenar la compra.
            return response()->json([
                'message'   => $e->getMessage(),
                'faltantes' => $this->faltantesParaCompra(collect($validated['ordenes'])->pluck('insumos')->all()),
            ], 422);
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
                'empleadosAsignados.persona',
                'detallePedido.tipoProducto', 'detallePedido.genero',
                'detallePedido.bordados.logo',
                'detallePedido.color',
                'detallePedido.talla',
                'insumos',
                'creadoPor:id,name,avatar',
                'pedido.cliente',
            ])->findOrFail($id);

        $orden->append('nombre_producto');

        $data = $orden->toArray();
        // Creador real para el chip "Creado por" (nombre + avatar con fallback del accessor).
        $data['creador'] = $orden->creadoPor ? [
            'name'       => $orden->creadoPor->name,
            'avatar_url' => $orden->creadoPor->avatar_url,
        ] : null;
        // Cliente del pedido ligado, para el chip espejo "Cliente" (gutter izquierdo).
        $data['cliente_nombre']    = $orden->pedido?->cliente_nombre_completo;
        $data['cliente_documento'] = $orden->pedido?->cliente_documento;

        return response()->json($data);
    }

    /**
     * Registrar un avance de producción directamente sobre la orden.
     * Acumula cantidad_producida y actualiza el estado. Las unidades
     * defectuosas NO se registran aquí: son competencia exclusiva de
     * Control de Calidad (inspección post-producción con atribución
     * por empleado y reproceso — ControlCalidadService).
     * El empleado responsable es el asignado a la orden (empleado_id).
     */
    public function registrarAvance(Request $request, $id)
    {
        $orden = OrdenProduccion::with('pedido', 'empleadosAsignados.persona')->findOrFail($id);

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

        $equipo = $orden->empleadosAsignados;

        // Con equipo de 2+ el avance debe atribuirse a un empleado concreto; con
        // uno solo se atribuye automáticamente a él (sin fricción en la UI).
        $rules = [
            'cantidad_producida' => 'required|integer|min:1',
            'empleado_id'        => 'nullable|integer',
        ];
        if ($equipo->count() > 1) {
            $rules['empleado_id'] = 'required|integer';
        }
        $validated = $request->validate($rules);

        $producida = (int) $validated['cantidad_producida'];

        // Órdenes legacy sin filas de pivot: se trabaja solo con los totales de la
        // orden (sin desglose per-cápita), preservando el comportamiento previo.
        $miembro = null;
        if ($equipo->isNotEmpty()) {
            $empleadoId = $validated['empleado_id']
                ?? ($equipo->count() === 1 ? $equipo->first()->id : $orden->empleado_id);
            $miembro = $equipo->firstWhere('id', (int) $empleadoId);
            if (!$miembro) {
                return response()->json(['message' => 'El empleado indicado no pertenece al equipo de esta orden.'], 422);
            }
            // Tope per-cápita: lo asignado a ese empleado menos lo que ya produjo.
            $restanteEmp = (int) $miembro->pivot->cantidad - (int) $miembro->pivot->cantidad_producida;
            if ($producida > $restanteEmp) {
                $nombre = $miembro->persona->nombre ?? ('empleado #' . $miembro->id);
                return response()->json([
                    'message' => "A {$nombre} solo le faltan {$restanteEmp} unidades por producir en esta orden."
                ], 422);
            }
        } else {
            // Sin equipo: tope = restante de la orden.
            $restanteOrden = (int) $orden->cantidad_solicitada - (int) $orden->cantidad_producida;
            if ($producida > $restanteOrden) {
                return response()->json([
                    'message' => "Solo quedan {$restanteOrden} unidades por producir en esta orden."
                ], 422);
            }
        }

        DB::transaction(function () use ($orden, $miembro, $producida) {
            // Acumula en el pivot del empleado (si lo hay) y en los totales de la
            // orden (mantiene el invariante orden == suma por empleado).
            if ($miembro) {
                $orden->empleadosAsignados()->updateExistingPivot($miembro->id, [
                    'cantidad_producida' => (int) $miembro->pivot->cantidad_producida + $producida,
                ]);
            }

            $orden->cantidad_producida += $producida;

            if ($orden->cantidad_producida >= $orden->cantidad_solicitada) {
                $orden->estado = 'Finalizado';
                $orden->fecha_fin_real = now()->toDateString();
            } elseif ($orden->estado === 'Pendiente') {
                $orden->estado = 'En Proceso';
            }
            $orden->save();
        });

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return response()->json(['message' => 'Avance registrado correctamente.']);
    }

    public function edit($id)
    {
        $orden = OrdenProduccion::with([
                'insumos',
                'producto',
                'empleado.persona',
                'empleadosAsignados.persona',
                'pedido.cliente',
                'detallePedido.tipoProducto', 'detallePedido.genero',
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

        // Reparto de la línea: tope al que puede crecer la cantidad de ESTA
        // orden = sus unidades + las que la línea aún tiene sin asignar.
        $data['linea_cantidad'] = $orden->detallePedido?->cantidad;
        $data['cantidad_maxima'] = $orden->detallePedido
            ? max(0, $orden->detallePedido->cantidad - $this->cantidadAsignadaActiva($orden->detalle_pedido_id, $orden->id))
            : $orden->cantidad_solicitada;

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $orden = OrdenProduccion::findOrFail($id);

        // 'Cancelado' no se setea aquí: la cancelación tiene su propio endpoint
        // (cancelar) porque define la reposición de stock y exige motivo de merma.
        $validated = $request->validate([
            'empleados'            => 'required|array|min:1',
            'empleados.*.id'       => 'required|exists:empleado,id',
            'empleados.*.cantidad' => 'required|integer|min:1',
            'cantidad'             => 'nullable|integer|min:1',
            'fecha_inicio'         => 'required|date',
            'fecha_fin_estimada'   => 'required|date|after:fecha_inicio',
            'estado'               => 'required|in:Pendiente,En Proceso,Finalizado',
            'notas'                => 'nullable|string',
        ]);

        // Cantidad: solo se puede rebalancear con la orden Pendiente (la tela no
        // se ha cortado y los insumos no se ajustan; en marcha → cancelar+recrear).
        // El tope es lo que la línea tenga sin asignar en otras órdenes activas.
        $nuevaCantidad = (int) ($validated['cantidad'] ?? $orden->cantidad_solicitada);
        if ($nuevaCantidad !== (int) $orden->cantidad_solicitada) {
            if ($orden->estado !== 'Pendiente') {
                return response()->json([
                    'message' => 'La cantidad solo puede cambiarse mientras la orden está Pendiente.'
                ], 422);
            }
            if (!$orden->detalle_pedido_id) {
                return response()->json([
                    'message' => 'La orden no está ligada a una línea de pedido; su cantidad no puede cambiarse.'
                ], 422);
            }
            try {
                DB::transaction(function () use ($orden, $nuevaCantidad) {
                    $detalle = DetallePedido::whereKey($orden->detalle_pedido_id)->lockForUpdate()->firstOrFail();
                    $maximo = $detalle->cantidad - $this->cantidadAsignadaActiva($detalle->id, $orden->id);
                    if ($nuevaCantidad > $maximo) {
                        throw new \InvalidArgumentException(
                            "La línea solo admite hasta {$maximo} unidades para esta orden (el resto está asignado a otras órdenes activas)."
                        );
                    }
                    $orden->update(['cantidad_solicitada' => $nuevaCantidad]);
                });
            } catch (\InvalidArgumentException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        }

        $fechaFinReal = $orden->fecha_fin_real;
        if ($validated['estado'] === 'Finalizado' && is_null($fechaFinReal)) {
            $fechaFinReal = now()->toDateString();
        } elseif ($validated['estado'] !== 'Finalizado') {
            $fechaFinReal = null;
        }

        // producto e insumos quedan fijos: el producto está ligado a la línea del
        // pedido y los insumos ya comprometieron stock al crear la orden
        // (editarlos exigiría reconciliar inventario → cancelar+recrear).
        $orden->update([
            'empleado_id'         => $validated['empleados'][0]['id'], // responsable principal
            'fecha_inicio'        => $validated['fecha_inicio'],
            'fecha_fin_estimada'  => $validated['fecha_fin_estimada'],
            'estado'              => $validated['estado'],
            'fecha_fin_real'      => $fechaFinReal,
            'notas'               => $validated['notas'] ?? null,
        ]);

        try {
            $this->syncEmpleadosConCantidad($orden, $validated['empleados']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
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

    /**
     * Cambiar el estado de una sub-orden
     * (Pendiente / En Proceso / Finalizado / Cancelado), validado contra el ENUM.
     */
    public function updateSubOrdenEstado(Request $request, $id, $subId)
    {
        $validated = $request->validate([
            'estado' => 'required|in:Pendiente,En Proceso,Finalizado,Cancelado',
        ], [
            'estado.in' => 'Estado de sub-orden no válido.',
        ]);

        $sub = SubOrdenProduccion::where('orden_produccion_id', $id)->findOrFail($subId);
        $orden = OrdenProduccion::findOrFail($id);

        // Regla de integridad: finalizar la ÚLTIMA etapa activa finalizaría la
        // orden completa, y eso solo es válido si la producción registrada
        // (avances) ya cubre todas las unidades solicitadas. Evita órdenes
        // "Finalizadas" con 0 piezas registradas (reportes y pedido mentirosos).
        if ($validated['estado'] === 'Finalizado'
            && $orden->cantidad_producida < $orden->cantidad_solicitada) {
            $seriaUltima = $orden->subordenes()
                ->where('id', '!=', $sub->id)
                ->where('estado', '!=', 'Cancelado')
                ->get()
                ->every(fn ($s) => $s->estado === 'Finalizado');

            if ($seriaUltima) {
                $faltan = $orden->cantidad_solicitada - $orden->cantidad_producida;
                return response()->json([
                    'message' => "No puedes finalizar la última etapa: la orden tiene {$faltan} de {$orden->cantidad_solicitada} unidades sin registrar. Registra el avance de producción primero."
                ], 422);
            }
        }

        $sub->update(['estado' => $validated['estado']]);

        $orden->recalcularEstadoDesdeSubordenes();
        $orden->refresh();

        return response()->json([
            'message'   => 'Estado de la sub-orden actualizado.',
            'estado'    => $sub->estado,
            'op_estado' => $orden->estado,
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  EXPORTACIÓN PDF
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Exportar las órdenes de producción a PDF, con filtros por estado y rango
     * de fecha estimada de entrega (fecha_fin_estimada).
     */
    public function reportePdf(Request $request)
    {
        $query = OrdenProduccion::with(['producto', 'pedido', 'detallePedido.tipoProducto', 'detallePedido.genero']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_fin_estimada', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_fin_estimada', '<=', $request->fecha_hasta);
        }

        // Orden (paridad con el "Ordenar por" del listado en pantalla).
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'progreso_desc':
                $query->orderByRaw('(cantidad_producida / NULLIF(cantidad_solicitada, 0)) desc');
                break;
            case 'progreso_asc':
                $query->orderByRaw('(cantidad_producida / NULLIF(cantidad_solicitada, 0)) asc');
                break;
            default:
                $orden = 'recientes';
                $query->orderBy('created_at', 'desc');
                break;
        }

        $ordenes = $query->get();

        $filtros = [];
        if ($request->filled('estado')) {
            $filtros['Estado'] = $request->estado;
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Entrega estimada'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Más recientes', 'progreso_desc' => 'Mayor progreso', 'progreso_asc' => 'Menor progreso'][$orden];

        $pdf = \PDF::loadView('admin.ordenes.reporte_pdf', compact('ordenes', 'filtros'))
            ->setPaper('a4', 'landscape');
        return $pdf->stream('ordenes_produccion_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }

    /**
     * Comprobante individual de una Orden de Producción (documento por registro):
     * datos de la orden, progreso, cronograma, diseño/bordado, insumos y sub-órdenes.
     */
    public function ordenPdf($id)
    {
        $orden = OrdenProduccion::with([
                'producto.tipoProducto',
                'empleado.persona',
                'empleadosAsignados.persona',
                'detallePedido.tipoProducto', 'detallePedido.genero',
                'detallePedido.color', 'detallePedido.talla',
                'detallePedido.bordados.logo',
                'insumos',
                'subordenes.empleados.persona',
                'pedido.cliente.persona',
                'creadoPor:id,name',
            ])->findOrFail($id);

        $orden->append('nombre_producto');

        $pdf = \PDF::loadView('admin.ordenes.comprobante', compact('orden'))
            ->setPaper('a4', 'portrait');
        return $pdf->stream('orden_produccion_' . str_pad($orden->id, 5, '0', STR_PAD_LEFT) . '.pdf');
    }
}
