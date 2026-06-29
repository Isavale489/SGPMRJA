<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Producto;
use App\Models\DetallePedido;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\Banco;
use App\Models\Cotizacion;
use App\Models\Talla;
use App\Models\Color;
use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
use Illuminate\Support\Facades\Log;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\Logo;
use App\Rules\CiRifFormat;
use PDF;

class PedidoController extends Controller
{
    public function __construct(
        private PedidoService $pedidoService
    ) {
    }

    public function index()
    {
        $productos = Producto::with('tipoProducto')->where('estado', true)->get();
        $insumos = Insumo::all();
        $bancos = Banco::all();
        $tallas = Talla::activo()->orderBy('orden')->orderBy('nombre')->get(['id', 'nombre', 'etiqueta']);
        $colores = Color::activo()->orderBy('grupo')->orderBy('nombre')->get(['id', 'nombre', 'hex_referencial']);
        $logos = Logo::orderBy('name')->get(['id', 'name']);
        return view('admin.pedidos.index', compact('productos', 'insumos', 'bancos', 'tallas', 'colores', 'logos'));
    }

    public function getPedidos(Request $request)
    {
        // Hacer JOINs para permitir ordenamiento y búsqueda por datos del cliente
        $pedidos = Pedido::select('pedido.*')
            ->join('cliente', 'pedido.cliente_id', '=', 'cliente.id')
            ->join('persona', 'cliente.persona_id', '=', 'persona.id')
            ->with(['user:id,name', 'cliente.persona'])
            ->withCount(['ordenes as ordenes_activas_count' => function ($q) {
                $q->where('estado', '!=', 'Cancelado');
            }]);

        if ($request->filled('filter_estado')) {
            $pedidos->where('pedido.estado', $request->input('filter_estado'));
        }

        if ($request->filled('filter_fecha_entrega')) {
            $pedidos->whereDate('pedido.fecha_entrega_estimada', $request->input('filter_fecha_entrega'));
        }

        $orden = $request->input('filter_orden', 'recientes');

        switch ($orden) {
            case 'monto_desc':
                $pedidos->orderBy('pedido.total', 'desc');
                break;
            case 'entrega_asc':
                $pedidos->orderBy('pedido.fecha_entrega_estimada', 'asc');
                break;
            case 'recientes':
            default:
                $pedidos->orderBy('pedido.created_at', 'desc');
                break;
        }

        return DataTables::of($pedidos)
            // Usar accessors para mostrar datos normalizados del cliente
            ->addColumn('cliente_nombre_display', function ($pedido) {
                return $pedido->cliente_nombre_completo ?? 'N/A';
            })
            ->filterColumn('cliente_nombre_display', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('persona.nombre', 'like', "%{$keyword}%");
                });
            })

            ->addColumn('cliente_telefono_display', function ($pedido) {
                return $pedido->cliente_telefono_normalizado ?? 'N/A';
            })


            ->addColumn('fecha_pedido', function ($pedido) {
                return $pedido->fecha_pedido ? $pedido->fecha_pedido->format('d/m/Y') : 'N/A';
            })
            ->addColumn('fecha_entrega_estimada', function ($pedido) {
                return $pedido->fecha_entrega_estimada ? $pedido->fecha_entrega_estimada->format('d/m/Y') : 'N/A';
            })
            // Bandera para el frontend: ¿tiene producción iniciada? (bloquea editar/eliminar)
            ->addColumn('tiene_produccion', function ($pedido) {
                return $pedido->ordenes_activas_count > 0;
            })

            ->make(true);
    }

    /**
     * Obtener cotizaciones aprobadas disponibles para crear pedidos
     * (que no tengan un pedido asociado)
     */
    public function getCotizacionesDisponibles()
    {
        $cotizaciones = Cotizacion::with(['cliente', 'productos'])
            ->where('estado', 'Aprobada')
            ->doesntHave('pedido') // Solo cotizaciones sin pedido asociado
            ->orderBy('fecha_cotizacion', 'desc')
            ->get()
            ->map(function ($cotizacion) {
                return [
                    'id' => $cotizacion->id,
                    'cliente_nombre' => $cotizacion->cliente ?
                        (trim((string) ($cotizacion->cliente->nombre ?? '')) ?: 'N/A') :
                        'N/A',
                    'cliente_documento' => $cotizacion->cliente->documento ?? 'N/A',
                    'fecha_cotizacion' => $cotizacion->fecha_cotizacion ?
                        $cotizacion->fecha_cotizacion->format('d/m/Y') :
                        'N/A',
                    'fecha_validez' => $cotizacion->fecha_validez ?
                        $cotizacion->fecha_validez->format('d/m/Y') :
                        'N/A',
                    'total' => number_format($cotizacion->total, 2),
                    'total_raw' => $cotizacion->total,
                    'cantidad_productos' => $cotizacion->productos->count(),
                ];
            });

        return response()->json($cotizaciones);
    }

    public function store(StorePedidoRequest $request)
    {
        try {
            $this->pedidoService->crear($request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['success' => 'Pedido creado exitosamente.']);
    }

    public function show($id)
    {
        // Cargar pedido con cliente y sus relaciones normalizadas
        $pedido = Pedido::with([
            'user:id,name,avatar',
            'productos.producto.tipoProducto',
            'productos.tipoProducto.atributos.valores',
            'productos.genero',
            'productos.bordados.logo:id,name',
            'pagos.banco:id,nombre',
            'cliente.persona.telefonos',
            'cliente.persona.direcciones',
            'cotizacion:id,tasa_cambio_valor'
        ])->findOrFail($id);

        // Agregar datos normalizados del cliente al response
        $data = $pedido->toArray();

        // Enriquecer cada línea con la variante resuelta (para reconstruir líneas dinámicas
        // al editar): tipo, tela, atributo_valor_ids (por nombre) y un nombre legible.
        $data['productos'] = $pedido->productos->map(function ($detalle) {
            $arr = $detalle->toArray();
            $esDinamica = empty($detalle->producto_id) && !empty($detalle->tipo_producto_id);
            $telaSnap = $detalle->tela_snapshot ?? null;
            $arr['insumo_tela_id'] = is_array($telaSnap) ? ($telaSnap['id'] ?? null) : null;
            $arr['atributo_valor_ids'] = $esDinamica && $detalle->tipoProducto
                ? $detalle->tipoProducto->valorIdsDesdeSnapshot($detalle->atributos_snapshot)
                : [];
            $arr['sku'] = $detalle->producto ? $detalle->producto->codigo : $detalle->sku_snapshot;
            $arr['producto_nombre'] = $detalle->producto
                ? $detalle->producto->nombre_completo
                : trim(($detalle->tipoProducto?->nombre ?? 'Variante')
                    . (is_array($telaSnap) && !empty($telaSnap['nombre']) ? ' · ' . $telaSnap['nombre'] : ''));
            // Miniatura: imagen del producto (legacy) o del tipo (catálogo = Tipo).
            $arr['imagen_url'] = ($detalle->producto && $detalle->producto->imagen)
                ? asset($detalle->producto->imagen)
                : ($detalle->tipoProducto?->imagen_url);
            return $arr;
        })->all();
        // Tasa BCV heredada de la cotización de origen (para reflejar el bordado en Bs)
        $data['tasa_cambio_valor'] = optional($pedido->cotizacion)->tasa_cambio_valor;
        $data['cliente_nombre_completo'] = $pedido->cliente_nombre_completo;
        $data['cliente_email_normalizado'] = $pedido->cliente_email_normalizado;
        $data['cliente_telefono_normalizado'] = $pedido->cliente_telefono_normalizado;
        $data['cliente_documento'] = $pedido->cliente_documento;
        // Creador real (no se sobrescribe al editar) para el chip "Creado por"
        $data['creador'] = $pedido->user ? [
            'name' => $pedido->user->name,
            'avatar_url' => $pedido->user->avatar_url,
            'fecha' => optional($pedido->created_at)->format('d/m/Y H:i'),
        ] : null;

        // Formalización: el front congela las líneas y deja editar solo pagos.
        $data['esta_formalizado']  = $pedido->estaFormalizado();
        $data['lineas_bloqueadas'] = $pedido->lineasBloqueadas();

        return response()->json($data);
    }

    public function update(UpdatePedidoRequest $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado === 'Cancelado') {
            return response()->json(['error' => 'No se puede editar un pedido cancelado.'], 403);
        }

        // Una vez formalizado (abono ≥ 50%), con producción iniciada o completado,
        // las líneas quedan congeladas: solo se permiten cambios de pagos (p. ej.
        // registrar el saldo restante a la entrega).
        $soloPagos = $pedido->lineasBloqueadas();

        try {
            if ($soloPagos) {
                $this->pedidoService->actualizarSoloPagos($pedido, $request->validated());
            } else {
                $this->pedidoService->actualizar($pedido, $request->validated());
            }
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json([
            'success' => $soloPagos
                ? 'Pagos del pedido actualizados.'
                : 'Pedido actualizado exitosamente.',
        ]);
    }

    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        if (in_array($pedido->estado, ['Completado', 'Cancelado'])) {
            return response()->json(['error' => 'No se puede eliminar un pedido completado o cancelado.'], 403);
        }
        if ($pedido->tieneProduccionActiva()) {
            return response()->json(['error' => 'No se puede eliminar un pedido con producción iniciada. Cancela primero sus órdenes de producción.'], 403);
        }

        $pedido->delete();

        Log::warning('Pedido eliminado', [
            'pedido_id' => $id,
            'cliente_id' => $pedido->cliente_id,
            'total' => $pedido->total,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => 'Pedido eliminado exitosamente.']);
    }

    /**
     * Cancelar un pedido (acción manual). Cancelado es terminal: el estado deja
     * de auto-recalcularse desde producción hasta que se reactive.
     */
    public function cancelar($id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado === 'Completado') {
            return response()->json(['error' => 'No se puede cancelar un pedido completado.'], 422);
        }
        if ($pedido->estado === 'Cancelado') {
            return response()->json(['success' => 'El pedido ya estaba cancelado.']);
        }
        // Bloqueo: no se puede cancelar un pedido con producción en curso.
        // Hay que cancelar primero sus órdenes activas o en proceso.
        if ($pedido->tieneProduccionEnCurso()) {
            return response()->json([
                'error' => 'No se puede cancelar el pedido: tiene órdenes de producción activas o en proceso. '
                    . 'Cancela primero esas órdenes desde el módulo de Producción.',
            ], 422);
        }

        $pedido->update(['estado' => 'Cancelado']);

        Log::warning('Pedido cancelado', ['pedido_id' => $id, 'user_id' => auth()->id()]);

        return response()->json(['success' => 'Pedido cancelado.']);
    }

    /**
     * Reactivar un pedido cancelado: vuelve a quedar gobernado por producción.
     */
    public function reactivar($id)
    {
        $pedido = Pedido::findOrFail($id);

        if ($pedido->estado !== 'Cancelado') {
            return response()->json(['error' => 'Solo se puede reactivar un pedido cancelado.'], 422);
        }

        // Sale de Cancelado y se deja que producción determine el estado real
        $pedido->update(['estado' => 'Pendiente']);
        $pedido->recalcularEstado();

        return response()->json(['success' => 'Pedido reactivado.']);
    }

    public function reportePdf(Request $request)
    {
        $query = Pedido::with(['user:id,name', 'cliente', 'cliente.persona']);
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        // Cliente: coincidencia parcial por nombre/razón social o documento.
        if ($request->filled('cliente')) {
            $term = trim($request->cliente);
            $query->whereHas('cliente', function ($c) use ($term) {
                $c->withTrashed()->whereHas('persona', function ($p) use ($term) {
                    $p->where('nombre', 'like', "%{$term}%")
                      ->orWhere('documento_identidad', 'like', "%{$term}%");
                });
            });
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_entrega_estimada', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_entrega_estimada', '<=', $request->fecha_hasta);
        }

        // Orden (paridad con el "Ordenar por" del listado en pantalla).
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'monto_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'entrega_asc':
                $query->orderBy('fecha_entrega_estimada', 'asc');
                break;
            default:
                $orden = 'recientes';
                $query->orderBy('created_at', 'desc');
                break;
        }

        $pedidos = $query->get();

        $filtros = [];
        if ($request->filled('estado')) {
            $filtros['Estado'] = $request->estado;
        }
        if ($request->filled('cliente')) {
            $filtros['Cliente'] = trim($request->cliente);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de entrega'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Más recientes', 'monto_desc' => 'Mayor monto', 'entrega_asc' => 'Entrega más próxima'][$orden];

        $pdf = PDF::loadView('admin.pedidos.reporte_pdf', compact('pedidos', 'filtros'))
            ->setPaper('a4', 'portrait');
        return $pdf->stream('reporte_pedidos_' . now()->format('Ymd_His') . '.pdf');
    }

    public function reporteGeneral()
    {
        $pedidos = Pedido::with('user:id,name')->get();
        return view('admin.pedidos.reporte_general', compact('pedidos'));
    }

    public function pedidoPdf(Pedido $pedido)
    {
        // Cargar relaciones necesarias
        $pedido->load(['user:id,name', 'productos.producto', 'productos.tipoProducto', 'productos.genero', 'productos.color', 'productos.talla', 'productos.bordados.logo:id,name', 'cliente', 'cliente.persona', 'cotizacion:id,tasa_cambio_valor,created_at']);

        // Cálculos financieros
        $ivaTasa = 0.16; // 16 %
        $subtotal = $pedido->total;
        $descuento = 0; // Ajustable en el futuro si se implementa
        $iva = round(($subtotal - $descuento) * $ivaTasa, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        // Tasa de cambio para el equivalente en Bs + su fecha exacta (trazabilidad).
        // Prefiere el snapshot de la cotización de origen; si no hay, la BCV vigente.
        $tasaValor = optional($pedido->cotizacion)->tasa_cambio_valor;
        if ($tasaValor) {
            $refFecha = optional(optional($pedido->cotizacion)->created_at ?? $pedido->fecha_pedido)->toDateString();
            $tasaFecha = optional(\App\Models\TasaCambio::tasaVigente($refFecha ?? now()->toDateString(), 'USD'))->fecha_bcv;
        } else {
            $row = \App\Models\TasaCambio::obtenerTasaActual('USD');
            $tasaValor = optional($row)->valor;
            $tasaFecha = optional($row)->fecha_bcv;
        }

        $pdf = PDF::loadView('admin.pedidos.factura', [
            'pedido' => $pedido,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'totalPagar' => $totalPagar,
            'tasaValor' => $tasaValor,
            'tasaFecha' => $tasaFecha,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('pedido_' . $pedido->id . '.pdf');
    }
}
