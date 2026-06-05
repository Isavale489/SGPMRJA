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
                    $q->where('persona.nombre', 'like', "%{$keyword}%")
                        ->orWhere('persona.apellido', 'like', "%{$keyword}%")
                        ->orWhereRaw("CONCAT(persona.nombre, ' ', persona.apellido) like ?", ["%{$keyword}%"]);
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
                        trim(($cotizacion->cliente->nombre ?? '') . ' ' . ($cotizacion->cliente->apellido ?? '')) :
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
            'productos.bordados.logo:id,name',
            'pagos.banco:id,nombre',
            'cliente.persona.telefonos',
            'cliente.persona.direcciones',
            'cotizacion:id,tasa_cambio_valor'
        ])->findOrFail($id);

        // Agregar datos normalizados del cliente al response
        $data = $pedido->toArray();
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
        ] : null;

        return response()->json($data);
    }

    public function update(UpdatePedidoRequest $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        if (in_array($pedido->estado, ['Completado', 'Cancelado'])) {
            return response()->json(['error' => 'No se puede editar un pedido completado o cancelado.'], 403);
        }
        if ($pedido->tieneProduccionActiva()) {
            return response()->json(['error' => 'No se puede editar un pedido con producción iniciada. Cancela primero sus órdenes de producción.'], 403);
        }

        try {
            $this->pedidoService->actualizar($pedido, $request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }

        return response()->json(['success' => 'Pedido actualizado exitosamente.']);
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
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_entrega', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_entrega', '<=', $request->fecha_hasta);
        }
        $pedidos = $query->get();
        $pdf = PDF::loadView('admin.pedidos.reporte_pdf', compact('pedidos'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('reporte_pedidos_' . now()->format('Ymd_His') . '.pdf');
    }

    public function reporteGeneral()
    {
        $pedidos = Pedido::with('user:id,name')->get();
        return view('admin.pedidos.reporte_general', compact('pedidos'));
    }

    public function pedidoPdf(Pedido $pedido)
    {
        // Cargar relaciones necesarias
        $pedido->load(['user:id,name', 'productos.producto', 'productos.bordados.logo:id,name', 'cliente', 'cliente.persona', 'cotizacion:id,tasa_cambio_valor']);

        // Cálculos financieros
        $ivaTasa = 0.16; // 16 %
        $subtotal = $pedido->total;
        $descuento = 0; // Ajustable en el futuro si se implementa
        $iva = round(($subtotal - $descuento) * $ivaTasa, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        $pdf = PDF::loadView('admin.pedidos.factura', [
            'pedido' => $pedido,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'totalPagar' => $totalPagar,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('pedido_' . $pedido->id . '.pdf');
    }
}
