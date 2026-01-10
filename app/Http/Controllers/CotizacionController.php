<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\DetalleCotizacion;
use App\Models\Insumo;
use App\Models\MovimientoInsumo;
use App\Models\Banco;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Rules\CiRifFormat;
use PDF;

class CotizacionController extends Controller
{
    public function index()
    {
        $productos = Producto::with('tipoProducto')->where('estado', true)->get();
        $insumos = Insumo::all();
        $bancos = Banco::all();
        return view('admin.cotizaciones.index', compact('productos', 'insumos', 'bancos'));
    }

    public function getCotizaciones()
    {
        // Cargar clientes incluso si están eliminados (soft deleted)
        $cotizaciones = Cotizacion::with(['user:id,name'])
            ->with([
                'cliente' => function ($query) {
                    $query->withTrashed()->with('persona');
                }
            ])
            ->select('cotizacion.*');
        return DataTables::of($cotizaciones)
            ->addColumn('usuario_creador', function ($cotizacion) {
                return $cotizacion->user ? $cotizacion->user->name : 'N/A';
            })
            ->addColumn('cliente_nombre', function ($cotizacion) {
                if ($cotizacion->cliente) {
                    $nombre = $cotizacion->cliente->nombre ?? '';
                    $apellido = $cotizacion->cliente->apellido ?? '';
                    $nombreCompleto = trim($nombre . ' ' . $apellido) ?: 'Sin nombre';
                    // Indicar si el cliente fue eliminado
                    if ($cotizacion->cliente->deleted_at) {
                        return $nombreCompleto . ' <span class="badge bg-danger ms-1" title="Cliente eliminado">Eliminado</span>';
                    }
                    return $nombreCompleto;
                }
                return '<span class="text-danger">Cliente no encontrado</span>';
            })
            ->addColumn('cliente_email', function ($cotizacion) {
                if ($cotizacion->cliente) {
                    $email = $cotizacion->cliente->email ?: 'N/A';
                    return $cotizacion->cliente->deleted_at ? '<span class="text-muted">' . $email . '</span>' : $email;
                }
                return 'N/A';
            })
            ->addColumn('cliente_telefono', function ($cotizacion) {
                if ($cotizacion->cliente) {
                    $telefono = $cotizacion->cliente->telefono ?: 'N/A';
                    return $cotizacion->cliente->deleted_at ? '<span class="text-muted">' . $telefono . '</span>' : $telefono;
                }
                return 'N/A';
            })
            ->addColumn('ci_rif', function ($cotizacion) {
                if ($cotizacion->cliente) {
                    $documento = $cotizacion->cliente->documento ?: 'N/A';
                    return $cotizacion->cliente->deleted_at ? '<span class="text-muted">' . $documento . '</span>' : $documento;
                }
                return 'N/A';
            })
            ->addColumn('actions', function ($cotizacion) {
                $actions = '<div class="d-flex gap-2">';
                $actions .= '<button type="button" class="btn btn-sm btn-info view-btn" data-id="' . $cotizacion->id . '" title="Ver detalles"><i class="ri-eye-fill"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-success edit-btn" data-id="' . $cotizacion->id . '" title="Editar cotización"><i class="ri-pencil-fill"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-danger remove-btn" data-id="' . $cotizacion->id . '" title="Eliminar cotización"><i class="ri-delete-bin-fill"></i></button>';
                $actions .= '<a href="' . route('cotizaciones.pdf', $cotizacion->id) . '" class="btn btn-sm btn-warning" title="Descargar PDF"><i class="ri-file-pdf-fill"></i></a>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions', 'cliente_nombre', 'cliente_email', 'cliente_telefono', 'ci_rif'])
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'nullable|date|after_or_equal:fecha_cotizacion',
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.nombre_logo' => 'nullable|string|required_if:productos.*.lleva_bordado,true',
            'productos.*.talla' => 'nullable|in:Talla Unica,XS,S,M,L,XL,XXL,2,4,6,8,10,12,14,16',
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.ubicacion_logo' => 'nullable|string|required_if:productos.*.lleva_bordado,true',
            'productos.*.cantidad_logo' => 'nullable|integer|min:1|required_if:productos.*.lleva_bordado,true',
        ]);

        DB::transaction(function () use ($request) {
            $total_cotizacion = 0;
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $total_cotizacion += $producto->precio_base * $item['cantidad'];
            }

            $cotizacion = Cotizacion::create([
                'cliente_id' => $request->cliente_id,
                'fecha_cotizacion' => $request->fecha_cotizacion,
                'fecha_validez' => $request->fecha_validez,
                'estado' => 'Pendiente',
                'total' => $total_cotizacion,
                'user_id' => Auth::id(),
            ]);

            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $detalleCotizacion = DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null,
                    'lleva_bordado' => $item['lleva_bordado'] ?? false,
                    'nombre_logo' => $item['nombre_logo'] ?? null,
                    'ubicacion_logo' => $item['ubicacion_logo'] ?? null,
                    'cantidad_logo' => $item['cantidad_logo'] ?? null,
                    'talla' => $item['talla'] ?? null,
                    'precio_unitario' => $producto->precio_base,
                ]);
            }
        });

        return response()->json(['success' => 'Cotización creada exitosamente.']);
    }

    public function show($id)
    {
        // Cargar cliente incluso si está eliminado (soft deleted)
        $cotizacion = Cotizacion::with(['user:id,name', 'productos.producto.tipoProducto'])
            ->with([
                'cliente' => function ($query) {
                    $query->withTrashed()->with('persona');
                }
            ])
            ->findOrFail($id);

        // Formatear datos del cliente usando los accessors
        $clienteData = null;
        if ($cotizacion->cliente) {
            $clienteData = [
                'id' => $cotizacion->cliente->id,
                'nombre' => $cotizacion->cliente->nombre,
                'apellido' => $cotizacion->cliente->apellido,
                'email' => $cotizacion->cliente->email,
                'telefono' => $cotizacion->cliente->telefono,
                'documento' => $cotizacion->cliente->documento,
                'direccion' => $cotizacion->cliente->direccion,
                'ciudad' => $cotizacion->cliente->ciudad,
                'eliminado' => $cotizacion->cliente->deleted_at ? true : false,
            ];
        }

        $response = $cotizacion->toArray();
        $response['cliente'] = $clienteData;

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'nullable|date|after_or_equal:fecha_cotizacion',
            'estado' => 'required|in:Pendiente,Procesando,Completado,Cancelado',
            'productos' => 'required|array',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.nombre_logo' => 'nullable|string|required_if:productos.*.lleva_bordado,true',
            'productos.*.talla' => 'nullable|in:Talla Unica,XS,S,M,L,XL,XXL,2,4,6,8,10,12,14,16',
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.ubicacion_logo' => 'nullable|string|required_if:productos.*.lleva_bordado,true',
            'productos.*.cantidad_logo' => 'nullable|integer|min:1|required_if:productos.*.lleva_bordado,true',
        ]);

        DB::transaction(function () use ($request, $id) {
            $cotizacion = Cotizacion::findOrFail($id);

            // Eliminar los detalles de cotización existentes
            $cotizacion->productos()->delete();

            $total_cotizacion = 0;
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $total_cotizacion += $producto->precio_base * $item['cantidad'];
            }

            $cotizacion->update([
                'cliente_id' => $request->cliente_id,
                'fecha_cotizacion' => $request->fecha_cotizacion,
                'fecha_validez' => $request->fecha_validez,
                'estado' => $request->estado,
                'total' => $total_cotizacion,
                'user_id' => Auth::id(),
            ]);

            // Sincronizar productos de la cotización
            foreach ($request->productos as $item) {
                $producto = Producto::find($item['producto_id']);
                $detalleCotizacion = DetalleCotizacion::create([
                    'cotizacion_id' => $cotizacion->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'descripcion' => $item['descripcion'] ?? null,
                    'lleva_bordado' => $item['lleva_bordado'] ?? false,
                    'nombre_logo' => $item['nombre_logo'] ?? null,
                    'ubicacion_logo' => $item['ubicacion_logo'] ?? null,
                    'cantidad_logo' => $item['cantidad_logo'] ?? null,
                    'talla' => $item['talla'] ?? null,
                    'precio_unitario' => $producto->precio_base,
                ]);
            }
        });

        return response()->json(['success' => 'Cotización actualizada exitosamente.']);
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->delete();
        return response()->json(['success' => 'Cotización eliminada exitosamente.']);
    }

    public function reportePdf()
    {
        // Obtener todas las cotizaciones con su usuario asociado
        $cotizaciones = Cotizacion::with('user:id,name')->get();

        // Cargar la vista y generar el PDF (A4 vertical)
        $pdf = PDF::loadView('admin.cotizaciones.reporte_pdf', compact('cotizaciones'))
            ->setPaper('a4', 'portrait');

        // Descargar el archivo con una marca de tiempo para evitar colisiones
        return $pdf->download('reporte_cotizaciones_' . now()->format('Ymd_His') . '.pdf');
    }

    public function reporteGeneral()
    {
        $cotizaciones = Cotizacion::with('user:id,name')->get();
        return view('admin.cotizaciones.reporte_general', compact('cotizaciones'));
    }

    public function cotizacionPdf(Cotizacion $cotizacion)
    {
        // Cargar relaciones necesarias (incluyendo clientes eliminados)
        $cotizacion->load(['user:id,name', 'productos.producto']);
        $cotizacion->load([
            'cliente' => function ($query) {
                $query->withTrashed()->with('persona');
            }
        ]);

        // Cálculos financieros
        $ivaTasa = 0.16; // 16 %
        $subtotal = $cotizacion->total;
        $descuento = 0; // Ajustable en el futuro si se implementa
        $iva = round(($subtotal - $descuento) * $ivaTasa, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        $pdf = PDF::loadView('admin.cotizaciones.factura', [
            'cotizacion' => $cotizacion,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'totalPagar' => $totalPagar,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('cotizacion_' . $cotizacion->id . '.pdf');
    }

    /**
     * Actualizar estado de cotización via AJAX
     */
    public function updateEstado(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Pendiente,Aprobada,Rechazada,Convertida,Vencida'
        ]);

        $cotizacion = Cotizacion::findOrFail($id);

        // No permitir cambiar estado si ya fue convertida
        if ($cotizacion->estado === 'Convertida') {
            return response()->json([
                'error' => 'No se puede cambiar el estado de una cotización ya convertida a pedido.'
            ], 422);
        }

        $cotizacion->update(['estado' => $request->estado]);

        return response()->json([
            'success' => 'Estado actualizado a: ' . $request->estado,
            'estado' => $request->estado
        ]);
    }

    /**
     * Obtener datos de cotización para pre-llenar formulario de pedido
     */
    public function getDatosParaPedido($id)
    {
        $cotizacion = Cotizacion::with(['cliente.persona', 'productos.producto.tipoProducto'])
            ->findOrFail($id);

        // Verificar que esté aprobada
        if ($cotizacion->estado !== 'Aprobada') {
            return response()->json([
                'error' => 'Solo se pueden convertir cotizaciones con estado Aprobada.'
            ], 422);
        }

        // Preparar datos para el formulario de pedido
        $datosParaPedido = [
            'cotizacion_id' => $cotizacion->id,
            'cliente_id' => $cotizacion->cliente_id,
            'cliente' => $cotizacion->cliente ? [
                'id' => $cotizacion->cliente->id,
                'nombre' => $cotizacion->cliente->nombre,
                'apellido' => $cotizacion->cliente->apellido,
                'email' => $cotizacion->cliente->email,
                'telefono' => $cotizacion->cliente->telefono,
                'documento' => $cotizacion->cliente->documento,
            ] : null,
            'total' => $cotizacion->total,
            'productos' => $cotizacion->productos->map(function ($detalle) {
                return [
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto ? $detalle->producto->nombre_completo : 'N/A',
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->descripcion,
                    'lleva_bordado' => $detalle->lleva_bordado,
                    'nombre_logo' => $detalle->nombre_logo,
                    'ubicacion_logo' => $detalle->ubicacion_logo,
                    'cantidad_logo' => $detalle->cantidad_logo,
                    'talla' => $detalle->talla,
                    'precio_unitario' => $detalle->precio_unitario,
                ];
            }),
        ];

        return response()->json($datosParaPedido);
    }

    /**
     * Marcar cotización como convertida después de crear el pedido
     */
    public function marcarComoConvertida($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        if ($cotizacion->estado !== 'Aprobada') {
            return response()->json([
                'error' => 'Solo se pueden convertir cotizaciones aprobadas.'
            ], 422);
        }

        $cotizacion->update(['estado' => 'Convertida']);

        return response()->json(['success' => 'Cotización marcada como convertida.']);
    }
}
