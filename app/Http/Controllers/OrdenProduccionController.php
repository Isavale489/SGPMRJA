<?php

namespace App\Http\Controllers;

use App\Exceptions\ReglaOrdenException;
use App\Exceptions\StockInsuficienteException;
use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Models\DetallePedido;
use App\Models\Empleado;
use App\Models\Insumo;
use App\Models\OrdenProduccion;
use App\Models\Pedido;
use App\Models\SubOrdenProduccion;
use App\Services\DisponibilidadInsumoService;
use App\Services\ProduccionInventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrdenProduccionController extends Controller
{
    use RespondeSegunCliente;

    public function __construct(
        private ProduccionInventarioService $inventario,
        private DisponibilidadInsumoService $disponibilidad
    ) {
    }

    /**
     * Aviso de stock proyectado (NO bloqueante) para el wizard de Órdenes:
     * agrega los insumos REALES de las órdenes que se están armando y los compara
     * contra el stock. Devuelve el mismo shape que consume ProyeccionInsumos (React).
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

    /**
     * Regla de negocio que impide guardar. Inertia: error en el campo `$campo`
     * del formulario (y, si los hay, los faltantes por flash para ofrecer la
     * compra). JSON: el 422 de siempre ({message, ...extra}).
     */
    private function fallar(Request $request, string $mensaje, string $campo = 'general', array $extra = []): JsonResponse
    {
        if ($this->esInertia($request)) {
            if ($extra) {
                Inertia::flash($extra);
            }
            throw ValidationException::withMessages([$campo => $mensaje]);
        }

        return response()->json(['message' => $mensaje, ...$extra], 422);
    }

    /** Empleados del departamento de Producción (asignables a una orden). */
    private function empleadosProduccion(): array
    {
        return Empleado::with('persona')
            ->whereHas('departamento', fn ($q) => $q->whereRaw("LOWER(nombre) LIKE 'producc%'"))
            ->where('estado', 1)
            ->get()
            ->map(fn ($e) => ['id' => $e->id, 'nombre' => $e->persona->nombre_completo ?? 'Sin nombre'])
            ->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }

    /**
     * Página Inertia: una fila por pedido (más una para las órdenes manuales)
     * con los agregados de sus órdenes. Por recarga parcial llegan las órdenes
     * de un pedido (?pedido=ID|manual), el detalle de una orden (?ver=ID) y las
     * órdenes de un empleado (?empleado=ID).
     *
     * Los filtros de estado/fecha se aplican ANTES de agrupar: el pedido
     * aparece solo si tiene órdenes que cumplan, y los agregados reflejan
     * únicamente esas órdenes.
     */
    public function index(Request $request): Response
    {
        $filtros = array_filter($request->only(['buscar', 'estado', 'desde', 'hasta', 'orden', 'pedido', 'ver', 'empleado']), fn ($v) => $v !== null && $v !== '');

        return Inertia::render('Ordenes/Index', [
            'filtros' => (object) $filtros,
            'registros' => fn () => $this->pedidosConOrdenes($filtros),
            'ordenes' => fn () => isset($filtros['pedido']) ? $this->ordenesDePedido($filtros['pedido']) : null,
            'orden' => fn () => ($o = isset($filtros['ver']) ? OrdenProduccion::find($filtros['ver']) : null) ? $this->detalle($o) : null,
            'misOrdenes' => fn () => isset($filtros['empleado']) ? $this->ordenesDeEmpleado((int) $filtros['empleado']) : null,
            'empleados' => $this->empleadosProduccion(),
            'urls' => [
                'index' => route('ordenes.index', absolute: false),
                'crear' => route('ordenes.create', absolute: false),
                'reportePdf' => route('ordenes.reporte.pdf', absolute: false),
            ],
        ]);
    }

    private function pedidosConOrdenes(array $f)
    {
        $q = OrdenProduccion::query()
            ->leftJoin('pedido', 'pedido.id', '=', 'orden_produccion.pedido_id')
            ->leftJoin('cliente', 'cliente.id', '=', 'pedido.cliente_id')
            ->leftJoin('persona', 'persona.id', '=', 'cliente.persona_id')
            ->groupBy('orden_produccion.pedido_id')
            // MAX() sobre persona.nombre: valor único por grupo (1 pedido = 1 cliente),
            // envuelto en agregado para cumplir ONLY_FULL_GROUP_BY de MySQL 8.
            ->selectRaw("
                orden_produccion.pedido_id,
                MAX(persona.nombre) as cliente,
                COUNT(*) as total_ordenes,
                SUM(orden_produccion.estado = 'Pendiente')  as pendientes,
                SUM(orden_produccion.estado = 'En Proceso') as en_proceso,
                SUM(orden_produccion.estado = 'Finalizado') as finalizadas,
                SUM(orden_produccion.estado = 'Cancelado')  as canceladas,
                SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_solicitada, 0)) as solicitado,
                SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_producida, 0))  as producido,
                MAX(orden_produccion.fecha_fin_estimada) as entrega
            ");

        if (! empty($f['estado'])) {
            $q->where('orden_produccion.estado', $f['estado']);
        }
        if (! empty($f['desde'])) {
            $q->whereDate('orden_produccion.fecha_fin_estimada', '>=', $f['desde']);
        }
        if (! empty($f['hasta'])) {
            $q->whereDate('orden_produccion.fecha_fin_estimada', '<=', $f['hasta']);
        }
        if (! empty($f['buscar'])) {
            $kw = trim($f['buscar']);
            $q->where(function ($w) use ($kw) {
                $w->where('persona.nombre', 'like', "%{$kw}%");
                if (preg_match('/\d+/', $kw, $m)) {
                    // Acepta "11" o "Pedido #11"
                    $w->orWhereRaw('CAST(orden_produccion.pedido_id AS CHAR) LIKE ?', ["%{$m[0]}%"]);
                }
                if (stripos('manuales', $kw) !== false) {
                    $w->orWhereNull('orden_produccion.pedido_id');
                }
            });
        }

        // El progreso global excluye canceladas (mismos SUM del select).
        $progreso = "SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_producida, 0))
            / NULLIF(SUM(IF(orden_produccion.estado <> 'Cancelado', orden_produccion.cantidad_solicitada, 0)), 0)";
        match ($f['orden'] ?? 'recientes') {
            'progreso_desc' => $q->orderByRaw("({$progreso}) desc"),
            'progreso_asc' => $q->orderByRaw("({$progreso}) asc"),
            default => $q->orderByRaw('MAX(orden_produccion.created_at) desc'),
        };

        return $q->paginate(15)->withQueryString()->through(fn ($r) => [
            'pedido_id' => $r->pedido_id,
            'cliente' => $r->cliente,
            'total_ordenes' => (int) $r->total_ordenes,
            'pendientes' => (int) $r->pendientes,
            'en_proceso' => (int) $r->en_proceso,
            'finalizadas' => (int) $r->finalizadas,
            'canceladas' => (int) $r->canceladas,
            'solicitado' => (int) $r->solicitado,
            'producido' => (int) $r->producido,
            'entrega' => $r->entrega ? substr((string) $r->entrega, 0, 10) : null,
        ]);
    }

    /** Equipo de la orden con su reparto y avance. */
    private function equipo(OrdenProduccion $o): array
    {
        return $o->empleadosAsignados->map(fn ($e) => [
            'id' => $e->id,
            'nombre' => $e->persona->nombre_completo ?? ('Empleado #'.$e->id),
            'cantidad' => (int) $e->pivot->cantidad,
            'producida' => (int) $e->pivot->cantidad_producida,
            'defectuosa' => (int) $e->pivot->cantidad_defectuosa,
        ])->values()->all();
    }

    /** Talla, color y género de la línea del pedido ("M · Azul · Caballero"). */
    private function variante(OrdenProduccion $o): ?string
    {
        $d = $o->detallePedido;
        if (! $d) {
            return null;
        }

        // Sin repetir lo que el nombre del producto ya dice (suele incluir el género).
        $nombre = mb_strtolower($o->nombre_producto);

        return collect([$d->talla ? ($d->talla->etiqueta ?: $d->talla->nombre) : null, $d->color?->nombre, $d->genero?->nombre])
            ->filter(fn ($v) => $v && ! str_contains($nombre, mb_strtolower($v)))->implode(' · ') ?: null;
    }

    /** Órdenes de un pedido ('manual' = sin pedido), para el diálogo «Órdenes del pedido». */
    private function ordenesDePedido(string $pedido): array
    {
        return OrdenProduccion::with(['producto.tipoProducto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'detallePedido.color', 'detallePedido.talla', 'empleadosAsignados.persona', 'creadoPor:id,name'])
            ->withCount('subordenes')
            ->when($pedido === 'manual', fn ($q) => $q->whereNull('pedido_id'), fn ($q) => $q->where('pedido_id', (int) $pedido))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->get()
            ->map(fn (OrdenProduccion $o) => [
                'id' => $o->id,
                'producto' => $o->nombre_producto,
                'variante' => $this->variante($o),
                'equipo' => $this->equipo($o),
                'cantidad_solicitada' => $o->cantidad_solicitada,
                'cantidad_producida' => $o->cantidad_producida,
                'cantidad_defectuosa' => (int) $o->cantidad_defectuosa,
                'estado' => $o->estado,
                'fecha_inicio' => $o->fecha_inicio?->toDateString(),
                'fecha_fin_estimada' => $o->fecha_fin_estimada?->toDateString(),
                'etapas' => (int) $o->subordenes_count,
                'creado_por' => $o->creadoPor?->name,
            ])->all();
    }

    /** Ficha de una orden: la usan «Ver», «Registrar avance» y «Etapas». */
    private function detalle(OrdenProduccion $o): array
    {
        $o->load([
            'producto.tipoProducto', 'empleadosAsignados.persona', 'insumos',
            'detallePedido.tipoProducto', 'detallePedido.genero', 'detallePedido.color', 'detallePedido.talla', 'detallePedido.bordados.logo',
            'subordenes.empleados.persona', 'creadoPor:id,name,avatar', 'pedido.cliente.persona',
        ]);
        $tipo = $o->producto?->tipoProducto ?? $o->detallePedido?->tipoProducto;

        return [
            'id' => $o->id,
            'pedido_id' => $o->pedido_id,
            'pedido_cancelado' => $o->pedido?->estado === 'Cancelado',
            'cliente' => $o->pedido?->cliente_nombre_completo,
            'cliente_documento' => $o->pedido?->cliente_documento,
            'producto' => $o->nombre_producto,
            'variante' => $this->variante($o),
            'imagen' => $tipo?->imagen_url,
            'estado' => $o->estado,
            'cantidad_solicitada' => $o->cantidad_solicitada,
            'cantidad_producida' => $o->cantidad_producida,
            'cantidad_defectuosa' => (int) $o->cantidad_defectuosa,
            'fecha_inicio' => $o->fecha_inicio?->toDateString(),
            'fecha_fin_estimada' => $o->fecha_fin_estimada?->toDateString(),
            'fecha_fin_real' => $o->fecha_fin_real?->toDateString(),
            'notas' => $o->notas,
            'motivo_cancelacion' => $o->motivo_cancelacion,
            'creado' => $o->created_at?->format('Y-m-d H:i'),
            'creador' => $o->creadoPor ? ['nombre' => $o->creadoPor->name, 'avatar' => $o->creadoPor->avatar_url] : null,
            'equipo' => $this->equipo($o),
            'insumos' => $o->insumos->map(fn ($i) => [
                'id' => $i->id,
                'nombre' => $i->nombre,
                'unidad' => $i->unidad_medida,
                'estimada' => (float) $i->pivot->cantidad_estimada,
            ])->values()->all(),
            'bordados' => ($o->detallePedido?->bordados ?? collect())->map(fn ($b) => [
                'id' => $b->id,
                'aplicacion' => $b->nombre_aplicado,
                'logo' => $b->logo?->name ?? $b->nombre_logo_aplicado,
                'cantidad' => (int) ($b->cantidad ?: 1),
            ])->values()->all(),
            'etapas' => $o->subordenes->map(fn ($s) => [
                'id' => $s->id,
                'nombre' => $s->nombre,
                'cantidad' => $s->cantidad_asignada,
                'estado' => $s->estado,
                'notas' => $s->notas,
                'empleados' => $s->empleados->map(fn ($e) => [
                    'id' => $e->id,
                    'nombre' => $e->persona->nombre_completo ?? ('Empleado #'.$e->id),
                    'rol' => $e->pivot->rol,
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    /**
     * Órdenes en las que participa un empleado (responsable o parte del
     * equipo), las activas primero. Antes solo veía las que tenía como
     * responsable principal.
     */
    private function ordenesDeEmpleado(int $empleadoId): ?array
    {
        $empleado = Empleado::with('persona')->find($empleadoId);
        if (! $empleado) {
            return null;
        }

        $ordenes = OrdenProduccion::with(['producto', 'detallePedido.tipoProducto', 'detallePedido.genero', 'empleadosAsignados'])
            ->where(fn ($q) => $q->where('empleado_id', $empleadoId)->orWhereHas('empleadosAsignados', fn ($e) => $e->where('empleado.id', $empleadoId)))
            ->orderByRaw("FIELD(estado,'En Proceso','Pendiente','Finalizado','Cancelado')")
            ->orderBy('fecha_fin_estimada')
            ->get()
            ->map(function (OrdenProduccion $o) use ($empleadoId) {
                $mio = $o->empleadosAsignados->firstWhere('id', $empleadoId);

                return [
                    'id' => $o->id,
                    'pedido_id' => $o->pedido_id,
                    'producto' => $o->nombre_producto,
                    'estado' => $o->estado,
                    'cantidad_solicitada' => $o->cantidad_solicitada,
                    'cantidad_producida' => $o->cantidad_producida,
                    // Su parte del reparto (null en órdenes antiguas sin equipo).
                    'mi_cantidad' => $mio ? (int) $mio->pivot->cantidad : null,
                    'mi_producida' => $mio ? (int) $mio->pivot->cantidad_producida : null,
                    'fecha_fin_estimada' => $o->fecha_fin_estimada?->toDateString(),
                ];
            });

        return [
            'empleado' => ['id' => $empleado->id, 'nombre' => $empleado->persona?->nombre_completo ?? ('Empleado #'.$empleado->id)],
            'ordenes' => $ordenes->values()->all(),
        ];
    }

    /**
     * Pedidos activos con sus líneas fabricables y las unidades que aún no
     * tienen orden. Una línea admite VARIAS órdenes (reparto entre empleados).
     */
    private function pedidosDisponibles(): array
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
        $asignadoPorDetalle = OrdenProduccion::whereIn('pedido_id', $pedidos->pluck('id'))
            ->whereNotNull('detalle_pedido_id')
            ->where('estado', '!=', 'Cancelado')
            ->selectRaw('detalle_pedido_id, SUM(cantidad_solicitada) as asignado')
            ->groupBy('detalle_pedido_id')
            ->pluck('asignado', 'detalle_pedido_id');

        return $pedidos->map(function (Pedido $pedido) use ($asignadoPorDetalle) {
            $lineas = $pedido->productos
                // Solo líneas fabricables: los productos de reventa
                // (tipo->requiere_produccion = false) se venden pero no se producen.
                ->filter(fn ($d) => $d->requiereProduccion())
                ->map(function ($d) use ($asignadoPorDetalle) {
                    // Tipo: legacy desde el producto; dinámico desde la relación directa.
                    $tipo = $d->producto ? $d->producto->tipoProducto : $d->tipoProducto;

                    // Consumo POR UNIDAD del tipo de producto (plantilla de la orden):
                    // el formulario lo multiplica por las unidades de cada orden.
                    $insumos = $tipo
                        ? $tipo->insumosDefault->map(fn ($i) => [
                            'id' => $i->id,
                            'nombre' => $i->nombre,
                            'unidad' => $i->unidad_medida,
                            'por_unidad' => (float) $i->pivot->cantidad_estimada,
                        ])->values()
                        : collect();

                    // Tela de la variante: legacy desde producto->tela; dinámico desde tela_snapshot.
                    $tela = null;
                    if ($d->producto && $d->producto->tela) {
                        $tela = ['id' => $d->producto->tela->id, 'nombre' => $d->producto->tela->nombre, 'unidad' => $d->producto->tela->unidad_medida];
                    } elseif (is_array($d->tela_snapshot) && ! empty($d->tela_snapshot['id'])) {
                        $tela = ['id' => $d->tela_snapshot['id'], 'nombre' => $d->tela_snapshot['nombre'] ?? 'Tela', 'unidad' => $d->tela_snapshot['unidad_medida'] ?? ''];
                    }
                    // Si el tipo requiere tela y define consumo por unidad, la tela va primero.
                    if ($tipo && $tipo->requiere_tela && $tipo->consumo_tela_por_unidad > 0 && $tela) {
                        $insumos->prepend([...$tela, 'por_unidad' => (float) $tipo->consumo_tela_por_unidad]);
                    }

                    $nombre = $d->producto
                        ? $d->producto->nombre
                        : (trim(($tipo->nombre ?? '').' '.($tela['nombre'] ?? '')) ?: ($d->sku_snapshot ?? ('Producto #'.$d->id)));
                    $asignado = (int) ($asignadoPorDetalle[$d->id] ?? 0);

                    return [
                        'detalle_id' => $d->id,
                        'producto' => $nombre,
                        'variante' => collect([$d->talla ? ($d->talla->etiqueta ?: $d->talla->nombre) : null, $d->color?->nombre, $d->genero?->nombre])->filter()->implode(' · ') ?: null,
                        'bordados' => $d->bordados->count(),
                        'cantidad' => (int) $d->cantidad,
                        'pendiente' => max(0, (int) $d->cantidad - $asignado),
                        'insumos' => $insumos->values()->all(),
                    ];
                })->values();

            return [
                'id' => $pedido->id,
                'cliente' => $pedido->cliente_nombre_completo,
                'cliente_documento' => $pedido->cliente_documento,
                'fecha_pedido' => $pedido->fecha_pedido?->toDateString(),
                'fecha_entrega' => $pedido->fecha_entrega_estimada?->toDateString(),
                'estado' => $pedido->estado,
                // Abono mínimo (regla de negocio): sin él no se puede producir.
                'porcentaje_abonado' => round($pedido->porcentajeAbonado(), 1),
                'cumple_abono' => $pedido->cumpleAbonoMinimo(),
                'lineas' => $lineas->all(),
            ];
        })
            // Ocultar pedidos sin nada que producir (solo productos de reventa).
            ->filter(fn ($p) => count($p['lineas']) > 0)
            ->values()->all();
    }

    /** Formulario de órdenes nuevas (1..N de un pedido), en su propia página. */
    public function create(): Response
    {
        return Inertia::render('Ordenes/Formulario', [
            'pedidos' => fn () => $this->pedidosDisponibles(),
            'empleados' => $this->empleadosProduccion(),
            'insumos' => Insumo::where('estado', true)->where('is_inventoriable', true)->orderBy('nombre')
                ->get(['id', 'nombre', 'unidad_medida', 'stock_actual'])
                ->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'unidad' => $i->unidad_medida, 'stock' => (float) $i->stock_actual])->all(),
            'abonoMinimo' => Pedido::porcentajeAbonoMinimo(),
            'urls' => [
                'index' => route('ordenes.index', absolute: false),
                'guardar' => route('ordenes.batch', absolute: false),
                'proyeccion' => route('ordenes.proyeccionInsumos', absolute: false),
                // Sin permiso de compras no se ofrece «Comprar lo que falta».
                'crearCompra' => tienePermiso('compras.gestionar') ? route('compras.create', absolute: false) : null,
            ],
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
        $ids = collect($empleados)->pluck('id')->map(fn ($i) => (int) $i);
        if ($ids->count() !== $ids->unique()->count()) {
            throw new \InvalidArgumentException('Hay empleados repetidos en el reparto.');
        }

        $suma = collect($empleados)->sum(fn ($e) => (int) $e['cantidad']);
        if ($suma !== (int) $orden->cantidad_solicitada) {
            throw new \InvalidArgumentException(
                "El reparto por empleado ({$suma}) no coincide con las {$orden->cantidad_solicitada} unidades de la orden."
            );
        }

        // Pivot actual (para preservar producido/defectuoso de quienes continúan).
        $previo = $orden->empleadosAsignados()->with('persona')->get()
            ->keyBy('id')
            ->map(fn ($e) => [
                'nombre'     => $e->persona->nombre ?? ('empleado #'.$e->id),
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
            // La cuota no puede quedar por debajo de lo que el empleado ya produjo.
            $hecho = $previo[$id]['producida'] ?? 0;
            if ((int) $e['cantidad'] < $hecho) {
                throw new \InvalidArgumentException(
                    "{$previo[$id]['nombre']} ya produjo {$hecho} unidades: su parte del reparto no puede ser menor."
                );
            }
            $syncData[$id] = [
                'cantidad'            => (int) $e['cantidad'],
                'cantidad_producida'  => $previo[$id]['producida']  ?? 0,
                'cantidad_defectuosa' => $previo[$id]['defectuosa'] ?? 0,
            ];
        }
        $orden->empleadosAsignados()->sync($syncData);
    }

    /**
     * Lo mismo que filtra el formulario (pedidosDisponibles), validado en el
     * servidor: solo pedidos activos y solo líneas fabricables (no reventa).
     */
    private function bloqueoProduccion(Pedido $pedido, $detalles): ?string
    {
        if (in_array($pedido->estado, ['Cancelado', 'Completado'], true)) {
            return "El pedido está {$pedido->estado}: no admite nuevas órdenes de producción.";
        }
        $reventa = $detalles->first(fn (DetallePedido $d) => ! $d->requiereProduccion());
        if ($reventa) {
            $nombre = $reventa->producto->nombre ?? $reventa->sku_snapshot ?? ('línea #'.$reventa->id);

            return "\"{$nombre}\" es un producto de reventa: no se fabrica.";
        }

        return null;
    }

    /**
     * Bloquea el pedido y confirma que sigue activo: si se canceló mientras se
     * llenaba el formulario, no recibe órdenes nuevas.
     */
    private function bloquearPedidoActivo(?int $pedidoId): void
    {
        $pedido = Pedido::whereKey($pedidoId)->lockForUpdate()->first();
        if ($pedidoId && ! $pedido) {
            throw new \InvalidArgumentException('El pedido ya no existe: no admite órdenes de producción.');
        }
        if ($pedido && in_array($pedido->estado, ['Cancelado', 'Completado'], true)) {
            throw new \InvalidArgumentException("El pedido está {$pedido->estado}: no admite nuevas órdenes de producción.");
        }
    }

    /** Al guardar desde Inertia se vuelve al listado con las órdenes del pedido abiertas. */
    private function alListado(Request $request, ?int $pedidoId, string $mensaje, array $json): JsonResponse|RedirectResponse
    {
        return $this->esInertia($request)
            ? redirect()->route('ordenes.index', ['pedido' => $pedidoId ?? 'manual'])->with('success', $mensaje)
            : response()->json($json);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
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
        if ($error = $this->bloqueoProduccion($pedido, collect([$detalle]))) {
            return $this->fallar($request, $error);
        }
        if ($error = $this->bloqueoPorAbonoMinimo($pedido)) {
            return $this->fallar($request, $error['message']);
        }

        try {
            // Crear la orden, asociar insumos y descontar stock en una sola
            // transacción: si falta stock, no se crea nada (rollback total).
            DB::transaction(function () use ($validated, $detalle) {
                $this->bloquearPedidoActivo($detalle->pedido_id);
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

                $this->crearOrden($detalle, $validated, $cantidad);
            });
        } catch (StockInsuficienteException $e) {
            // Faltantes estructurados para prellenar la compra.
            return $this->fallar($request, $e->getMessage(), 'general', ['faltantes' => $this->faltantesParaCompra([$validated['insumos']])]);
        } catch (\InvalidArgumentException $e) {
            return $this->fallar($request, $e->getMessage());
        }

        $pedido->recalcularEstado();

        return $this->alListado($request, $pedido->id, 'Orden de producción creada exitosamente.', ['message' => 'Orden de producción creada exitosamente.']);
    }

    /** Crea la orden con su equipo e insumos y descuenta el stock (dentro de la transacción del llamador). */
    private function crearOrden(DetallePedido $detalle, array $o, int $cantidad): OrdenProduccion
    {
        $orden = OrdenProduccion::create([
            'pedido_id'           => $detalle->pedido_id,
            'detalle_pedido_id'   => $detalle->id,
            'producto_id'         => $detalle->producto_id,
            'empleado_id'         => $o['empleados'][0]['id'], // responsable principal
            'cantidad_solicitada' => $cantidad,
            'cantidad_producida'  => 0,
            'cantidad_defectuosa' => 0,
            'fecha_inicio'        => $o['fecha_inicio'],
            'fecha_fin_estimada'  => $o['fecha_fin_estimada'],
            'estado'              => 'Pendiente',
            'notas'               => $o['notas'] ?? null,
            'created_by'          => Auth::id(),
        ]);

        $this->syncEmpleadosConCantidad($orden, $o['empleados']);

        $insumoIds = collect($o['insumos'])->pluck('id')->map(fn ($i) => (int) $i);
        if ($insumoIds->count() !== $insumoIds->unique()->count()) {
            throw new \InvalidArgumentException('Hay insumos repetidos en la orden.');
        }
        foreach ($o['insumos'] as $ins) {
            $orden->insumos()->attach($ins['id'], [
                'cantidad_estimada' => $ins['cantidad_estimada'],
                'cantidad_utilizada' => 0,
            ]);
        }

        $this->inventario->validarYDescontar($orden, Auth::id());

        return $orden;
    }

    /**
     * Crear varias órdenes del mismo pedido en una sola transacción (lo que
     * usa el formulario). Cada orden trae su equipo, fechas e insumos. Una
     * línea PUEDE aparecer varias veces (su producción se reparte); si alguna
     * falla, no se crea ninguna.
     */
    public function storeBatch(Request $request): JsonResponse|RedirectResponse
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
        ], [
            'ordenes.*.empleados.required' => 'Asigna al menos un empleado.',
            'ordenes.*.fecha_fin_estimada.after' => 'El fin estimado debe ser posterior al inicio.',
            'ordenes.*.insumos.required' => 'La orden necesita al menos un insumo.',
        ]);

        $detalleIds = collect($validated['ordenes'])->pluck('detalle_pedido_id')->unique()->values();

        // Todas las líneas deben pertenecer al mismo pedido_id (anti-tampering)
        $pertenecen = DetallePedido::whereIn('id', $detalleIds)->where('pedido_id', $validated['pedido_id'])->count();
        if ($pertenecen !== $detalleIds->count()) {
            return $this->fallar($request, 'Una o más líneas no pertenecen al pedido indicado.');
        }

        // Regla de negocio: el pedido debe alcanzar el abono mínimo para producir.
        $pedido = Pedido::findOrFail($validated['pedido_id']);
        if ($error = $this->bloqueoProduccion($pedido, DetallePedido::whereIn('id', $detalleIds)->get())) {
            return $this->fallar($request, $error);
        }
        if ($error = $this->bloqueoPorAbonoMinimo($pedido)) {
            return $this->fallar($request, $error['message']);
        }

        $creadas = [];
        try {
            DB::transaction(function () use ($validated, $detalleIds, &$creadas) {
                $this->bloquearPedidoActivo((int) $validated['pedido_id']);
                // Lock por línea: la suma de órdenes activas (las previas + las de
                // este lote) no puede superar las unidades de la línea.
                $detalles = DetallePedido::whereIn('id', $detalleIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($detalles as $detalle) {
                    $solicitado = collect($validated['ordenes'])->where('detalle_pedido_id', $detalle->id)->sum('cantidad');
                    $disponible = $detalle->cantidad - $this->cantidadAsignadaActiva($detalle->id);
                    if ($solicitado > $disponible) {
                        $nombre = $detalle->producto->nombre ?? $detalle->sku_snapshot ?? ('línea #'.$detalle->id);
                        throw new \InvalidArgumentException(
                            "\"{$nombre}\" solo tiene {$disponible} unidades sin asignar y se intentó asignar {$solicitado}. Recarga e intenta de nuevo."
                        );
                    }
                }

                foreach ($validated['ordenes'] as $o) {
                    $creadas[] = $this->crearOrden($detalles[$o['detalle_pedido_id']], $o, (int) $o['cantidad'])->id;
                }
            });
        } catch (StockInsuficienteException $e) {
            // Faltante agregado entre TODAS las órdenes del lote (lo que hay que comprar de verdad).
            return $this->fallar($request, $e->getMessage(), 'general', [
                'faltantes' => $this->faltantesParaCompra(collect($validated['ordenes'])->pluck('insumos')->all()),
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->fallar($request, $e->getMessage());
        }

        $pedido->recalcularEstado();
        $mensaje = count($creadas).' '.(count($creadas) === 1 ? 'orden creada' : 'órdenes creadas').' correctamente.';

        return $this->alListado($request, $pedido->id, $mensaje, ['message' => $mensaje, 'ordenes' => $creadas]);
    }

    /** Editar una orden en su propia página (equipo, fechas, estado; cantidad solo en Pendiente). */
    public function edit(OrdenProduccion $ordene): Response|RedirectResponse
    {
        $orden = $ordene;
        if ($orden->estado === 'Cancelado') {
            return redirect()->route('ordenes.index', ['pedido' => $orden->pedido_id ?? 'manual'])->with('error', 'Una orden cancelada no se puede editar.');
        }
        $orden->load(['empleadosAsignados.persona', 'insumos', 'detallePedido.genero', 'detallePedido.color', 'detallePedido.talla', 'detallePedido.tipoProducto', 'producto', 'pedido.cliente.persona']);

        return Inertia::render('Ordenes/Editar', [
            'orden' => [
                'id' => $orden->id,
                'pedido_id' => $orden->pedido_id,
                'cliente' => $orden->pedido?->cliente_nombre_completo,
                'producto' => $orden->nombre_producto,
                'variante' => $this->variante($orden),
                'estado' => $orden->estado,
                'cantidad' => $orden->cantidad_solicitada,
                'cantidad_producida' => $orden->cantidad_producida,
                // Producido o rechazado en Calidad: ya no vuelve a Pendiente.
                'con_produccion' => $orden->tieneProduccion(),
                // Tope al que puede crecer ESTA orden: sus unidades + las que la línea aún no asignó.
                'cantidad_maxima' => $orden->detallePedido
                    ? max(0, $orden->detallePedido->cantidad - $this->cantidadAsignadaActiva($orden->detalle_pedido_id, $orden->id))
                    : $orden->cantidad_solicitada,
                'fecha_inicio' => $orden->fecha_inicio?->toDateString(),
                'fecha_fin_estimada' => $orden->fecha_fin_estimada?->toDateString(),
                'notas' => $orden->notas,
                'equipo' => $this->equipo($orden),
                'insumos' => $orden->insumos->map(fn ($i) => ['id' => $i->id, 'nombre' => $i->nombre, 'unidad' => $i->unidad_medida, 'estimada' => (float) $i->pivot->cantidad_estimada])->values()->all(),
            ],
            'empleados' => $this->empleadosProduccion(),
            'urls' => [
                'index' => route('ordenes.index', ['pedido' => $orden->pedido_id ?? 'manual'], absolute: false),
                'guardar' => route('ordenes.update', $orden, absolute: false),
            ],
        ]);
    }

    /**
     * El estado que se elige a mano debe ser coherente con lo producido:
     *  - con producción (incluidas las unidades que Calidad rechazó) no se
     *    vuelve a Pendiente (Pendiente = tela sin cortar: eliminar/cancelar
     *    repondría un material que ya se usó);
     *  - Finalizado exige haber producido todas las unidades solicitadas.
     */
    private function errorDeEstado(OrdenProduccion $orden, string $estado, int $solicitada): ?string
    {
        $producida = (int) $orden->cantidad_producida;

        if ($estado === 'Pendiente' && $orden->tieneProduccion()) {
            return 'La orden ya tiene unidades producidas (o rechazadas en Calidad): no puede volver a Pendiente.';
        }
        if ($estado === 'Finalizado' && $producida < $solicitada) {
            $faltan = $solicitada - $producida;

            return "No se puede finalizar: faltan {$faltan} de {$solicitada} unidades por producir. Registra el avance primero.";
        }

        return null;
    }

    /**
     * Todo en una transacción: si el reparto no cuadra, no queda nada a medias
     * (antes se guardaban fechas y estado aunque el equipo fallara).
     */
    public function update(Request $request, $id): JsonResponse|RedirectResponse
    {
        $orden = OrdenProduccion::findOrFail($id);

        // Una cancelada no se edita: volver a un estado activo la "resucitaría"
        // después de haber repuesto (o dado por merma) su material.
        if ($orden->estado === 'Cancelado') {
            return $this->fallar($request, 'Una orden cancelada no se puede editar.', 'estado');
        }

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
        ], [
            'empleados.required' => 'Asigna al menos un empleado.',
            'fecha_fin_estimada.after' => 'El fin estimado debe ser posterior al inicio.',
        ]);

        // Cantidad: solo se puede rebalancear con la orden Pendiente (la tela no
        // se ha cortado y los insumos no se ajustan; en marcha → cancelar+recrear).
        $nuevaCantidad = (int) ($validated['cantidad'] ?? $orden->cantidad_solicitada);

        try {
            DB::transaction(function () use ($orden, $validated, $nuevaCantidad) {
                // Bloqueo de la orden: un avance simultáneo no puede colarse entre
                // las comprobaciones y el guardado.
                $this->bloquear($orden);
                if ($orden->estado === 'Cancelado') {
                    throw new ReglaOrdenException('Una orden cancelada no se puede editar.', 'estado');
                }

                $cambiaCantidad = $nuevaCantidad !== (int) $orden->cantidad_solicitada;
                if ($cambiaCantidad && ($orden->estado !== 'Pendiente' || $orden->tieneProduccion())) {
                    throw new ReglaOrdenException('La cantidad solo puede cambiarse mientras la orden está Pendiente y sin producción.', 'cantidad');
                }
                if ($cambiaCantidad && ! $orden->detalle_pedido_id) {
                    throw new ReglaOrdenException('La orden no está ligada a una línea de pedido; su cantidad no puede cambiarse.', 'cantidad');
                }
                if ($error = $this->errorDeEstado($orden, $validated['estado'], $nuevaCantidad)) {
                    throw new ReglaOrdenException($error, 'estado');
                }

                if ($cambiaCantidad) {
                    // El tope es lo que la línea tenga sin asignar en otras órdenes activas.
                    $detalle = DetallePedido::whereKey($orden->detalle_pedido_id)->lockForUpdate()->firstOrFail();
                    $maximo = $detalle->cantidad - $this->cantidadAsignadaActiva($detalle->id, $orden->id);
                    if ($nuevaCantidad > $maximo) {
                        throw new \InvalidArgumentException("La línea solo admite hasta {$maximo} unidades para esta orden (el resto está asignado a otras órdenes activas).", 1);
                    }
                    $orden->cantidad_solicitada = $nuevaCantidad;
                }

                $fechaFinReal = $orden->fecha_fin_real;
                if ($validated['estado'] === 'Finalizado' && is_null($fechaFinReal)) {
                    $fechaFinReal = now()->toDateString();
                } elseif ($validated['estado'] !== 'Finalizado') {
                    $fechaFinReal = null;
                }

                // producto e insumos quedan fijos: el producto está ligado a la línea del
                // pedido y los insumos ya comprometieron stock al crear la orden.
                $orden->fill([
                    'empleado_id'        => $validated['empleados'][0]['id'], // responsable principal
                    'fecha_inicio'       => $validated['fecha_inicio'],
                    'fecha_fin_estimada' => $validated['fecha_fin_estimada'],
                    'estado'             => $validated['estado'],
                    'fecha_fin_real'     => $fechaFinReal,
                    'notas'              => $validated['notas'] ?? null,
                ])->save();

                $this->syncEmpleadosConCantidad($orden, $validated['empleados']);
            });
        } catch (ReglaOrdenException $e) {
            return $this->fallar($request, $e->getMessage(), $e->campo);
        } catch (\InvalidArgumentException $e) {
            return $this->fallar($request, $e->getMessage(), $e->getCode() === 1 ? 'cantidad' : 'empleados');
        }

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return $this->alListado($request, $orden->pedido_id, 'Orden de producción actualizada exitosamente.', ['message' => 'Orden de producción actualizada exitosamente.']);
    }

    /**
     * Registrar un avance de producción directamente sobre la orden.
     * Acumula cantidad_producida y actualiza el estado. Las unidades
     * defectuosas NO se registran aquí: son competencia exclusiva de
     * Control de Calidad (inspección post-producción con atribución
     * por empleado y reproceso — ControlCalidadService).
     */
    public function registrarAvance(Request $request, $id): JsonResponse|RedirectResponse
    {
        $orden = OrdenProduccion::with('pedido', 'empleadosAsignados.persona')->findOrFail($id);

        // Bloqueo cruzado: si el pedido padre está cancelado, no se admite
        // ningún avance ni movimiento sobre sus órdenes.
        if ($orden->pedido && $orden->pedido->estado === 'Cancelado') {
            return $this->fallar($request, 'El pedido asociado está cancelado: no se pueden registrar avances en sus órdenes de producción.', 'cantidad_producida');
        }

        if (in_array($orden->estado, ['Finalizado', 'Cancelado'])) {
            return $this->fallar($request, "La orden ya está en estado \"{$orden->estado}\" y no puede recibir más avances.", 'cantidad_producida');
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
        $validated = $request->validate($rules, ['empleado_id.required' => 'Indica quién produjo estas unidades.']);

        $producida = (int) $validated['cantidad_producida'];

        try {
            DB::transaction(function () use ($orden, $validated, $producida) {
                // Con la orden bloqueada se releen totales y reparto: dos avances
                // simultáneos no pueden pasar ambos el tope con datos viejos.
                OrdenProduccion::whereKey($orden->id)->lockForUpdate()->first();
                $orden->refresh();
                if (in_array($orden->estado, ['Finalizado', 'Cancelado'])) {
                    throw new ReglaOrdenException("La orden ya está en estado \"{$orden->estado}\" y no puede recibir más avances.", 'cantidad_producida');
                }
                $equipo = $orden->empleadosAsignados()->with('persona')->get();

                // Tope de la orden (vale siempre, también con equipo).
                $restanteOrden = (int) $orden->cantidad_solicitada - (int) $orden->cantidad_producida;
                if ($producida > $restanteOrden) {
                    throw new ReglaOrdenException("Solo quedan {$restanteOrden} unidades por producir en esta orden.", 'cantidad_producida');
                }

                // Órdenes legacy sin filas de pivot: se trabaja solo con los totales
                // de la orden (sin desglose per-cápita).
                if ($equipo->isNotEmpty()) {
                    $empleadoId = $validated['empleado_id']
                        ?? ($equipo->count() === 1 ? $equipo->first()->id : $orden->empleado_id);
                    $miembro = $equipo->firstWhere('id', (int) $empleadoId);
                    if (! $miembro) {
                        throw new ReglaOrdenException('El empleado indicado no pertenece al equipo de esta orden.', 'empleado_id');
                    }
                    // Tope per-cápita: lo asignado a ese empleado menos lo que ya produjo.
                    $restanteEmp = (int) $miembro->pivot->cantidad - (int) $miembro->pivot->cantidad_producida;
                    if ($producida > $restanteEmp) {
                        $nombre = $miembro->persona->nombre ?? ('empleado #'.$miembro->id);
                        throw new ReglaOrdenException("A {$nombre} solo le faltan {$restanteEmp} unidades por producir en esta orden.", 'cantidad_producida');
                    }

                    // Acumula en el pivot del empleado y en los totales de la orden
                    // (mantiene el invariante orden == suma por empleado).
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
        } catch (ReglaOrdenException $e) {
            return $this->fallar($request, $e->getMessage(), $e->campo);
        }

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return $this->responder($request, 'Avance registrado correctamente.', ['message' => 'Avance registrado correctamente.']);
    }

    public function destroy(Request $request, $id): JsonResponse|RedirectResponse
    {
        $orden = OrdenProduccion::findOrFail($id);

        try {
            DB::transaction(function () use ($orden) {
                // Con la orden bloqueada: un avance o una cancelación simultánea
                // no pueden colarse entre la comprobación y la reposición.
                $this->bloquear($orden);
                if ($orden->estado !== 'Pendiente') {
                    throw new ReglaOrdenException('No se puede eliminar una orden que no está en estado Pendiente');
                }
                // Eliminar repone todo el material: con producción (o unidades
                // rechazadas por Calidad) ese material ya se usó.
                if ($orden->tieneProduccion()) {
                    throw new ReglaOrdenException('La orden ya tiene unidades producidas: cancélala en lugar de eliminarla.');
                }
                $this->inventario->reponer($orden, Auth::id());
                $orden->delete();
            });
        } catch (ReglaOrdenException $e) {
            return $this->rechazar($request, $e->getMessage());
        }

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return $this->responder($request, 'Orden de producción eliminada exitosamente.', ['message' => 'Orden de producción eliminada exitosamente.']);
    }

    /**
     * Cancelar una orden de producción.
     *
     * Reposición de stock condicional al estatus al momento de cancelar (merma
     * en confección textil):
     *  - 'Pendiente' sin producción: la tela no se ha cortado → se repone el
     *    stock comprometido.
     *  - En cualquier otro caso la tela ya se cortó (merma) → NO se repone y
     *    se exige un motivo que justifique la pérdida del material.
     */
    public function cancelar(Request $request, $id): JsonResponse|RedirectResponse
    {
        $orden = OrdenProduccion::findOrFail($id);
        $validated = $request->validate(
            ['motivo_cancelacion' => 'nullable|string|max:500'],
        );
        $motivo = trim((string) ($validated['motivo_cancelacion'] ?? '')) ?: null;

        try {
            $reponeStock = DB::transaction(function () use ($orden, $motivo) {
                $this->bloquear($orden);
                if ($orden->estado === 'Cancelado') {
                    throw new ReglaOrdenException('La orden ya está cancelada.', 'motivo_cancelacion');
                }
                $repone = $orden->estado === 'Pendiente' && ! $orden->tieneProduccion();
                if (! $repone && ! $motivo) {
                    // Error de validación del campo (mismo formato que antes, JSON e Inertia).
                    throw ValidationException::withMessages(['motivo_cancelacion' => 'La orden ya está en producción (material cortado): indica el motivo de la cancelación para justificar la merma.']);
                }

                if ($repone) {
                    $this->inventario->reponer($orden, Auth::id());
                }
                $orden->update(['estado' => 'Cancelado', 'motivo_cancelacion' => $motivo]);

                return $repone;
            });
        } catch (ReglaOrdenException $e) {
            return $this->fallar($request, $e->getMessage(), $e->campo);
        }

        Pedido::find($orden->pedido_id)?->recalcularEstado();

        $mensaje = $reponeStock
            ? 'Orden cancelada. Se repuso el stock de los insumos al inventario.'
            : 'Orden cancelada. El material se registró como merma (sin reposición de stock).';

        return $this->responder($request, $mensaje, ['message' => $mensaje]);
    }

    /** Bloquea la fila de la orden (dentro de una transacción) y relee sus datos. */
    private function bloquear(OrdenProduccion $orden): void
    {
        OrdenProduccion::whereKey($orden->id)->lockForUpdate()->first();
        $orden->refresh();
    }

    // ══════════════════════════════════════════════════════════════════════
    //  ETAPAS (sub-órdenes con varios empleados asignados)
    // ══════════════════════════════════════════════════════════════════════

    public function storeSubOrden(Request $request, $id): JsonResponse|RedirectResponse
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
            return $this->fallar($request, 'Hay empleados repetidos en la asignación.', 'empleados');
        }

        $sub = DB::transaction(function () use ($orden, $validated) {
            $sub = $orden->subordenes()->create([
                'nombre'            => $validated['nombre'],
                'cantidad_asignada' => $validated['cantidad_asignada'] ?? null,
                'estado'            => 'Pendiente',
                'notas'             => $validated['notas'] ?? null,
            ]);

            $sub->empleados()->sync(collect($validated['empleados'])->mapWithKeys(fn ($e) => [$e['id'] => ['rol' => $e['rol'] ?? null]])->all());

            return $sub;
        });

        return $this->responder($request, 'Sub-orden creada y empleados asignados.', ['message' => 'Sub-orden creada y empleados asignados.', 'sub_orden_id' => $sub->id]);
    }

    public function destroySubOrden(Request $request, $id, $subId): JsonResponse|RedirectResponse
    {
        $orden = OrdenProduccion::findOrFail($id);

        DB::transaction(function () use ($orden, $subId) {
            // Bloqueo: el recálculo usa lo producido al día (un avance simultáneo
            // no deja la orden en un estado viejo).
            $this->bloquear($orden);
            $orden->subordenes()->findOrFail($subId)->delete();
            // Quitar una etapa puede cambiar el estado de la orden (y el del pedido).
            $orden->recalcularEstadoDesdeSubordenes();
        });
        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return $this->responder($request, 'Sub-orden eliminada.', ['message' => 'Sub-orden eliminada.']);
    }

    /** Cambiar el estado de una etapa (Pendiente / En Proceso / Finalizado / Cancelado). */
    public function updateSubOrdenEstado(Request $request, $id, $subId): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'estado' => 'required|in:Pendiente,En Proceso,Finalizado,Cancelado',
        ], [
            'estado.in' => 'Estado de sub-orden no válido.',
        ]);

        $orden = OrdenProduccion::findOrFail($id);

        try {
            $sub = DB::transaction(function () use ($orden, $subId, $validated) {
                $this->bloquear($orden);
                $sub = $orden->subordenes()->findOrFail($subId);

                // Regla de integridad: finalizar la ÚLTIMA etapa activa finalizaría la
                // orden completa, y eso solo es válido si la producción registrada
                // (avances) ya cubre todas las unidades solicitadas.
                if ($validated['estado'] === 'Finalizado' && $orden->cantidad_producida < $orden->cantidad_solicitada) {
                    $seriaUltima = $orden->subordenes()
                        ->where('id', '!=', $sub->id)
                        ->where('estado', '!=', 'Cancelado')
                        ->get()
                        ->every(fn ($s) => $s->estado === 'Finalizado');

                    if ($seriaUltima) {
                        $faltan = $orden->cantidad_solicitada - $orden->cantidad_producida;
                        throw new ReglaOrdenException("No puedes finalizar la última etapa: la orden tiene {$faltan} de {$orden->cantidad_solicitada} unidades sin registrar. Registra el avance de producción primero.");
                    }
                }

                $sub->update(['estado' => $validated['estado']]);
                $orden->recalcularEstadoDesdeSubordenes();

                return $sub;
            });
        } catch (ReglaOrdenException $e) {
            return $this->rechazar($request, $e->getMessage());
        }

        $orden->refresh();
        Pedido::find($orden->pedido_id)?->recalcularEstado();

        return $this->responder($request, 'Estado de la sub-orden actualizado.', ['message' => 'Estado de la sub-orden actualizado.', 'estado' => $sub->estado, 'op_estado' => $orden->estado]);
    }

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
