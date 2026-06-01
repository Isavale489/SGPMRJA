<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\Logo;
use App\Models\Pedido;
use App\Models\Insumo;
use App\Models\Banco;
use App\Models\Cliente;
use App\Models\BordadoUbicacion;
use App\Services\CotizacionService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Rules\CiRifFormat;
use PDF;

class CotizacionController extends Controller
{
    public function __construct(
        private CotizacionService $cotizacionService
    ) {
    }
    public function index()
    {
        $productos = Producto::with([
            'tipoProducto',
            'tela:id,nombre,codigo,costo_unitario,unidad_medida',
            'atributoValores:id,atributo_id,nombre,codigo',
            'atributoValores.atributo:id,nombre,codigo',
        ])->where('estado', true)->get();

        $logos = Logo::orderBy('name')->get(['id', 'name', 'original_filename']);
        $insumos = Insumo::all();
        $bancos = Banco::all();
        return view('admin.cotizaciones.index', compact('productos', 'logos', 'insumos', 'bancos'));
    }

    public function getCotizaciones(Request $request)
    {
        // Actualizar automáticamente cotizaciones vencidas
        Cotizacion::actualizarCotizacionesVencidas();

        // Cargar clientes incluso si están eliminados (soft deleted)
        $cotizaciones = Cotizacion::with(['user:id,name'])
            ->with([
                'cliente' => function ($query) {
                    $query->withTrashed()->with('persona');
                }
            ])
            ->select('cotizacion.*');

        if ($request->filled('filter_estado')) {
            $cotizaciones->where('cotizacion.estado', $request->input('filter_estado'));
        }

        if ($request->filled('filter_fecha')) {
            $cotizaciones->whereDate('cotizacion.fecha_cotizacion', $request->input('filter_fecha'));
        }

        $orden = $request->input('filter_orden', 'recientes');

        switch ($orden) {
            case 'total_desc':
                $cotizaciones->orderBy('cotizacion.total', 'desc');
                break;
            case 'total_asc':
                $cotizaciones->orderBy('cotizacion.total', 'asc');
                break;
            case 'recientes':
            default:
                $cotizaciones->orderBy('cotizacion.created_at', 'desc');
                break;
        }
        return DataTables::of($cotizaciones)
            ->filterColumn('cliente_nombre', function ($query, $keyword) {
                $query->whereHas('cliente', function ($clienteQuery) use ($keyword) {
                    $clienteQuery->withTrashed()->whereHas('persona', function ($personaQuery) use ($keyword) {
                        $personaQuery->where('nombre', 'like', "%{$keyword}%")
                            ->orWhere('apellido', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%{$keyword}%"]);
                    });
                });
            })
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
            ->addColumn('fecha_cotizacion', function ($cotizacion) {
                return $cotizacion->fecha_cotizacion ? $cotizacion->fecha_cotizacion->format('d/m/Y') : 'N/A';
            })
            ->addColumn('fecha_validez', function ($cotizacion) {
                return $cotizacion->fecha_validez ? $cotizacion->fecha_validez->format('d/m/Y') : 'N/A';
            })
            ->addColumn('actions', function ($cotizacion) {
                $actions = '<div class="d-flex gap-2 justify-content-center">';
                $actions .= '<button type="button" class="btn btn-sm btn-soft-info view-btn" data-id="' . $cotizacion->id . '" title="Ver detalles"><i class="ri-eye-fill"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-soft-success edit-btn" data-id="' . $cotizacion->id . '" title="Editar cotización"><i class="ri-pencil-fill"></i></button>';
                $actions .= '<button type="button" class="btn btn-sm btn-soft-danger remove-btn" data-id="' . $cotizacion->id . '" title="Eliminar cotización"><i class="ri-delete-bin-fill"></i></button>';
                $actions .= '<a href="' . route('cotizaciones.pdf', $cotizacion->id) . '" class="btn btn-sm btn-soft-warning" title="Descargar PDF"><i class="ri-file-pdf-fill"></i></a>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['actions', 'cliente_nombre', 'cliente_email', 'cliente_telefono', 'ci_rif'])
            ->make(true);
    }

    public function getUbicacionesBordado()
    {
        $catalogo = BordadoUbicacion::activo()
            ->orderBy('grupo')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'grupo', 'precio_base', 'orden']);

        return response()->json($catalogo);
    }

    public function store(Request $request)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'nullable|date|after_or_equal:fecha_cotizacion',
            'notas' => 'nullable|string|max:2000',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.talla_id' => ['required', 'integer', Rule::exists('talla', 'id')],
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1',
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'required|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
        ], [
            // Mensajes personalizados
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_cotizacion.required' => 'La fecha de cotización es obligatoria.',
            'fecha_cotizacion.date' => 'La fecha de cotización debe ser una fecha válida.',
            'fecha_validez.date' => 'La fecha de validez debe ser una fecha válida.',
            'fecha_validez.after_or_equal' => 'La fecha de validez debe ser igual o posterior a la fecha de cotización.',
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            'productos.*.producto_id.required' => 'Debe seleccionar un producto.',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
            'productos.*.bordados.*.logo_id.required' => 'Cada bordado debe tener un logo asignado.',
            'productos.*.bordados.*.logo_id.exists' => 'El logo seleccionado no existe en el catálogo.',
            'productos.*.bordados.required_if' => 'Debe seleccionar al menos una ubicación de bordado.',
            'productos.*.bordados.min' => 'Debe seleccionar al menos una ubicación de bordado.',
            'productos.*.bordados.*.nombre_aplicado.required' => 'Cada bordado debe tener un nombre de ubicación.',
            'productos.*.bordados.*.precio_aplicado.required' => 'Cada bordado debe tener un precio aplicado.',
            'productos.*.bordados.*.precio_aplicado.numeric' => 'El precio aplicado de cada bordado debe ser numérico.',
            'productos.*.bordados.*.precio_aplicado.min' => 'El precio aplicado de cada bordado no puede ser negativo.',
            'productos.*.bordados.*.cantidad.min' => 'La cantidad de cada bordado debe ser al menos 1.',
            'productos.*.talla_id.required' => 'La talla es obligatoria.',
            'productos.*.talla_id.exists' => 'La talla seleccionada no es válida.',
            'productos.*.color_id.exists' => 'El color seleccionado no es válido.',
            'productos.*.insumos.*.id.required' => 'Debe seleccionar un insumo.',
            'productos.*.insumos.*.id.exists' => 'El insumo seleccionado no existe.',
            'productos.*.insumos.*.cantidad_estimada.required' => 'La cantidad estimada del insumo es obligatoria.',
            'productos.*.insumos.*.cantidad_estimada.numeric' => 'La cantidad estimada debe ser un número.',
            'productos.*.insumos.*.cantidad_estimada.min' => 'La cantidad estimada debe ser mayor a 0.',
        ]);

        $this->cotizacionService->crear($request->all());

        return response()->json(['success' => 'Cotización creada exitosamente.']);
    }

    public function show($id)
    {
        // Cargar cliente incluso si está eliminado (soft deleted)
        $cotizacion = Cotizacion::with(['user:id,name,avatar', 'productos.producto.tipoProducto', 'productos.bordados.logo:id,name'])
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
                'tipo_documento' => optional($cotizacion->cliente->persona)->tipo_documento,
                'razon_social' => optional($cotizacion->cliente->persona)->razon_social,
                'direccion' => $cotizacion->cliente->direccion,
                'ciudad' => $cotizacion->cliente->ciudad,
                'eliminado' => $cotizacion->cliente->deleted_at ? true : false,
            ];
        }

        $response = $cotizacion->toArray();
        $response['cliente'] = $clienteData;
        // Creador real (no se sobrescribe al editar) para el chip "Creada por"
        $response['creador'] = $cotizacion->user ? [
            'name' => $cotizacion->user->name,
            'avatar_url' => $cotizacion->user->avatar_url,
        ] : null;

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'nullable|date|after_or_equal:fecha_cotizacion',
            'estado' => 'required|in:Pendiente,Aprobada,Cancelada,Convertida,Vencida',
            'notas' => 'nullable|string|max:2000',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'required|exists:producto,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.talla_id' => ['required', 'integer', Rule::exists('talla', 'id')],
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1',
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'required|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
        ], [
            // Mensajes personalizados
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_cotizacion.required' => 'La fecha de cotización es obligatoria.',
            'fecha_cotizacion.date' => 'La fecha de cotización debe ser una fecha válida.',
            'fecha_validez.date' => 'La fecha de validez debe ser una fecha válida.',
            'fecha_validez.after_or_equal' => 'La fecha de validez debe ser igual o posterior a la fecha de cotización.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            'productos.*.producto_id.required' => 'Debe seleccionar un producto.',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
            'productos.*.bordados.*.logo_id.required' => 'Cada bordado debe tener un logo asignado.',
            'productos.*.bordados.*.logo_id.exists' => 'El logo seleccionado no existe en el catálogo.',
            'productos.*.bordados.required_if' => 'Debe seleccionar al menos una ubicación de bordado.',
            'productos.*.bordados.min' => 'Debe seleccionar al menos una ubicación de bordado.',
            'productos.*.bordados.*.nombre_aplicado.required' => 'Cada bordado debe tener un nombre de ubicación.',
            'productos.*.bordados.*.precio_aplicado.required' => 'Cada bordado debe tener un precio aplicado.',
            'productos.*.bordados.*.precio_aplicado.numeric' => 'El precio aplicado de cada bordado debe ser numérico.',
            'productos.*.bordados.*.precio_aplicado.min' => 'El precio aplicado de cada bordado no puede ser negativo.',
            'productos.*.bordados.*.cantidad.min' => 'La cantidad de cada bordado debe ser al menos 1.',
            'productos.*.talla_id.required' => 'La talla es obligatoria.',
            'productos.*.talla_id.exists' => 'La talla seleccionada no es válida.',
            'productos.*.color_id.exists' => 'El color seleccionado no es válido.',
            'productos.*.insumos.*.id.required' => 'Debe seleccionar un insumo.',
            'productos.*.insumos.*.id.exists' => 'El insumo seleccionado no existe.',
            'productos.*.insumos.*.cantidad_estimada.required' => 'La cantidad estimada del insumo es obligatoria.',
            'productos.*.insumos.*.cantidad_estimada.numeric' => 'La cantidad estimada debe ser un número.',
            'productos.*.insumos.*.cantidad_estimada.min' => 'La cantidad estimada debe ser mayor a 0.',
        ]);

        $cotizacion = Cotizacion::findOrFail($id);

        $this->cotizacionService->actualizar($cotizacion, $request->all());

        return response()->json(['success' => 'Cotización actualizada exitosamente.']);
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->delete();

        \Log::warning('Cotización eliminada', [
            'cotizacion_id' => $id,
            'cliente_id' => $cotizacion->cliente_id,
            'total' => $cotizacion->total,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['success' => 'Cotización eliminada exitosamente.']);
    }

    public function reportePdf(Request $request)
    {
        $query = Cotizacion::with(['user:id,name', 'cliente.persona']);
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        $cotizaciones = $query->get();
        $pdf = PDF::loadView('admin.cotizaciones.reporte_pdf', compact('cotizaciones'))
            ->setPaper('a4', 'portrait');
        return $pdf->download('reporte_cotizaciones_' . now()->format('Ymd_His') . '.pdf');
    }

    public function reporteGeneral()
    {
        $cotizaciones = Cotizacion::with('user:id,name')->get();
        return view('admin.cotizaciones.reporte_general', compact('cotizaciones'));
    }

    public function cotizacionPdf(Cotizacion $cotizacion)
    {
        // Cargar relaciones necesarias (incluyendo clientes eliminados y productos eliminados/tipos)
        $cotizacion->load(['user:id,name']);

        $cotizacion->load([
            'cliente' => function ($query) {
                $query->withTrashed()->with('persona');
            },
            'productos.producto' => function ($query) {
                $query->withTrashed()->with('tipoProducto');
            },
            'productos.bordados.logo:id,name',
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
            'estado' => 'required|in:Pendiente,Aprobada,Cancelada,Convertida,Vencida'
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
        $cotizacion = Cotizacion::with(['cliente.persona', 'productos.producto.tipoProducto', 'productos.bordados.logo:id,name'])
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
                $recargoUnitario = $detalle->bordados->sum(function ($bordado) {
                    return ((float) $bordado->precio_aplicado) * ((int) ($bordado->cantidad ?: 1));
                });

                $ubicacionLegacy = $detalle->bordados->pluck('nombre_aplicado')->implode(', ');
                $cantidadLegacy = $detalle->bordados->sum(function ($bordado) {
                    return (int) ($bordado->cantidad ?: 1);
                });

                return [
                    'producto_id' => $detalle->producto_id,
                    'producto_nombre' => $detalle->producto ? $detalle->producto->nombre_completo : 'N/A',
                    'cantidad' => $detalle->cantidad,
                    'descripcion' => $detalle->descripcion,
                    'lleva_bordado' => $detalle->lleva_bordado,
                    'nombre_logo' => $detalle->nombre_logo,
                    'bordados' => $detalle->bordados->map(function ($bordado) {
                        return [
                            'ubicacion_bordado_id' => $bordado->ubicacion_bordado_id,
                            'logo_id' => $bordado->logo_id,
                            'nombre_aplicado' => $bordado->nombre_aplicado,
                            'nombre_logo' => $bordado->logo ? $bordado->logo->name : $bordado->nombre_logo_aplicado,
                            'nombre_logo_aplicado' => $bordado->nombre_logo_aplicado,
                            'es_personalizada' => (bool) $bordado->es_personalizada,
                            'cantidad' => (int) $bordado->cantidad,
                            'precio_aplicado' => (float) $bordado->precio_aplicado,
                        ];
                    })->values(),
                    'recargo_bordado_unitario' => $recargoUnitario,
                    'ubicacion_logo' => $ubicacionLegacy ?: null,
                    'cantidad_logo' => $cantidadLegacy ?: null,
                    'talla_id' => $detalle->talla_id,
                    'color_id' => $detalle->color_id,
                    'precio_unitario' => $detalle->precio_unitario,
                ];
            }),
        ];

        return response()->json($datosParaPedido);
    }

    /**
     * Convertir cotización a pedido directamente (endpoint atómico).
     */
    public function convertirAPedido($id)
    {
        $cotizacion = Cotizacion::with(['cliente', 'productos'])->findOrFail($id);

        try {
            $pedido = $this->cotizacionService->convertirAPedido($cotizacion);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Error al convertir cotización a pedido', [
                'cotizacion_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Error interno al convertir la cotización. Intente de nuevo.'], 500);
        }

        return response()->json([
            'success' => 'Cotización convertida a pedido exitosamente.',
            'pedido_id' => $pedido->id,
            'message' => 'Se ha creado el pedido #' . $pedido->id . '. Puede editar el pedido para agregar fechas de entrega, abonos y método de pago.'
        ]);
    }
}
