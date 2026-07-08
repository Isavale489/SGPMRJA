<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Producto;
use App\Models\TipoProducto;
use App\Models\Logo;
use App\Models\Pedido;
use App\Models\Insumo;
use App\Models\Banco;
use App\Models\Cliente;
use App\Models\BordadoUbicacion;
use App\Services\CotizacionService;
use App\Services\BordadoPricingService;
use Illuminate\Validation\ValidationException;
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

        // Catálogo = Tipo de Producto. El grid de la cotización se arma desde los Tipos
        // (no desde filas producto): el cliente elige tela + variaciones al cotizar.
        $tiposProducto = TipoProducto::withCount(['telas', 'atributos'])
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'prefijo', 'imagen', 'precio_confeccion', 'requiere_tela']);

        $logos = Logo::orderBy('name')->get(['id', 'name', 'original_filename']);
        $insumos = Insumo::all();
        $bancos = Banco::all();
        $maxBordadosProducto = parametro('cotizaciones.max_bordados_producto');
        return view('admin.cotizaciones.index', compact('productos', 'tiposProducto', 'logos', 'insumos', 'bancos', 'maxBordadosProducto'));
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
                        $personaQuery->where('nombre', 'like', "%{$keyword}%");
                    });
                });
            })
            ->addColumn('usuario_creador', function ($cotizacion) {
                return $cotizacion->user ? $cotizacion->user->name : 'N/A';
            })
            ->addColumn('cliente_nombre', function ($cotizacion) {
                if ($cotizacion->cliente) {
                    $nombreCompleto = trim((string) ($cotizacion->cliente->nombre ?? '')) ?: 'Sin nombre';
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

    /**
     * Lanza ValidationException si algún producto excede el máximo de bordados
     * por prenda. La unidad es la SUMA de cantidades de cada línea de bordado
     * (una ubicación con cantidad 10 son 10 bordados), no el número de líneas.
     */
    private function assertMaxBordados(Request $request, int $max): void
    {
        $indices = BordadoPricingService::indicesQueExcedenMaximo($request->input('productos', []), $max);

        if (empty($indices)) {
            return;
        }

        $errores = [];
        foreach ($indices as $i) {
            $errores["productos.$i.bordados"] = "No se pueden agregar más de {$max} bordados por producto.";
        }

        throw ValidationException::withMessages($errores);
    }

    public function store(Request $request)
    {
        $maxBordados = parametro('cotizaciones.max_bordados_producto');

        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'required|date|after_or_equal:fecha_cotizacion',
            'notas' => 'nullable|string|max:2000',
            'condiciones_terminos' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'nullable|required_without:productos.*.tipo_producto_id|integer|exists:producto,id',
            'productos.*.tipo_producto_id' => 'nullable|required_without:productos.*.producto_id|integer|exists:tipo_producto,id',
            'productos.*.insumo_tela_id' => 'nullable|integer|exists:insumo,id',
            'productos.*.atributo_valor_ids' => 'nullable|array',
            'productos.*.atributo_valor_ids.*' => 'integer|exists:atributo_valor,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.talla_id' => ['required', 'integer', Rule::exists('talla', 'id')],
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.genero_id' => ['required', 'integer', Rule::exists('genero', 'id')],
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1',
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'nullable|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
        ], [
            // Mensajes personalizados
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_cotizacion.required' => 'La fecha de cotización es obligatoria.',
            'fecha_cotizacion.date' => 'La fecha de cotización debe ser una fecha válida.',
            'fecha_validez.required' => 'La fecha de validez es obligatoria.',
            'fecha_validez.date' => 'La fecha de validez debe ser una fecha válida.',
            'fecha_validez.after_or_equal' => 'La fecha de validez debe ser igual o posterior a la fecha de cotización.',
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            'productos.*.producto_id.required_without' => 'Debe seleccionar un producto o configurar una variante (tipo).',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'productos.*.tipo_producto_id.required_without' => 'Debe seleccionar un producto o configurar una variante (tipo).',
            'productos.*.tipo_producto_id.exists' => 'El tipo de producto seleccionado no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
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
            'productos.*.genero_id.required' => 'El género es obligatorio.',
            'productos.*.genero_id.exists' => 'El género seleccionado no es válido.',
            'productos.*.insumos.*.id.required' => 'Debe seleccionar un insumo.',
            'productos.*.insumos.*.id.exists' => 'El insumo seleccionado no existe.',
            'productos.*.insumos.*.cantidad_estimada.required' => 'La cantidad estimada del insumo es obligatoria.',
            'productos.*.insumos.*.cantidad_estimada.numeric' => 'La cantidad estimada debe ser un número.',
            'productos.*.insumos.*.cantidad_estimada.min' => 'La cantidad estimada debe ser mayor a 0.',
        ]);

        $this->assertMaxBordados($request, $maxBordados);

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
                'apellido' => '',
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
            'fecha' => optional($cotizacion->created_at)->format('d/m/Y H:i'),
        ] : null;

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $maxBordados = parametro('cotizaciones.max_bordados_producto');

        $request->validate([
            'cliente_id' => 'required|exists:cliente,id',
            'fecha_cotizacion' => 'required|date',
            'fecha_validez' => 'required|date|after_or_equal:fecha_cotizacion',
            'estado' => 'required|in:Pendiente,Aprobada,Cancelada,Convertida,Vencida',
            'notas' => 'nullable|string|max:2000',
            'condiciones_terminos' => 'nullable|string',
            'productos' => 'required|array|min:1',
            'productos.*.producto_id' => 'nullable|required_without:productos.*.tipo_producto_id|integer|exists:producto,id',
            'productos.*.tipo_producto_id' => 'nullable|required_without:productos.*.producto_id|integer|exists:tipo_producto,id',
            'productos.*.insumo_tela_id' => 'nullable|integer|exists:insumo,id',
            'productos.*.atributo_valor_ids' => 'nullable|array',
            'productos.*.atributo_valor_ids.*' => 'integer|exists:atributo_valor,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.descripcion' => 'nullable|string|max:500',
            'productos.*.lleva_bordado' => 'nullable|boolean',
            'productos.*.talla_id' => ['required', 'integer', Rule::exists('talla', 'id')],
            'productos.*.color_id' => ['nullable', 'integer', Rule::exists('color', 'id')],
            'productos.*.genero_id' => ['required', 'integer', Rule::exists('genero', 'id')],
            'productos.*.insumos' => 'nullable|array',
            'productos.*.insumos.*.id' => 'required|exists:insumo,id',
            'productos.*.insumos.*.cantidad_estimada' => 'required|numeric|min:0.01',
            'productos.*.bordados' => 'nullable|array|required_if:productos.*.lleva_bordado,true|min:1',
            'productos.*.bordados.*.ubicacion_bordado_id' => 'nullable|exists:bordado_ubicacion,id',
            'productos.*.bordados.*.nombre_aplicado' => 'required|string|max:120',
            'productos.*.bordados.*.logo_id' => 'nullable|exists:logo,id',
            'productos.*.bordados.*.es_personalizada' => 'nullable|boolean',
            'productos.*.bordados.*.precio_aplicado' => 'required|numeric|min:0',
            'productos.*.bordados.*.cantidad' => 'nullable|integer|min:1',
        ], [
            // Mensajes personalizados
            'cliente_id.required' => 'Debe seleccionar un cliente.',
            'cliente_id.exists' => 'El cliente seleccionado no existe.',
            'fecha_cotizacion.required' => 'La fecha de cotización es obligatoria.',
            'fecha_cotizacion.date' => 'La fecha de cotización debe ser una fecha válida.',
            'fecha_validez.required' => 'La fecha de validez es obligatoria.',
            'fecha_validez.date' => 'La fecha de validez debe ser una fecha válida.',
            'fecha_validez.after_or_equal' => 'La fecha de validez debe ser igual o posterior a la fecha de cotización.',
            'estado.required' => 'El estado es obligatorio.',
            'estado.in' => 'El estado seleccionado no es válido.',
            'productos.required' => 'Debe agregar al menos un producto.',
            'productos.min' => 'Debe agregar al menos un producto.',
            'productos.*.producto_id.required_without' => 'Debe seleccionar un producto o configurar una variante (tipo).',
            'productos.*.producto_id.exists' => 'El producto seleccionado no existe.',
            'productos.*.tipo_producto_id.required_without' => 'Debe seleccionar un producto o configurar una variante (tipo).',
            'productos.*.tipo_producto_id.exists' => 'El tipo de producto seleccionado no existe.',
            'productos.*.cantidad.required' => 'La cantidad es obligatoria.',
            'productos.*.cantidad.integer' => 'La cantidad debe ser un número entero.',
            'productos.*.cantidad.min' => 'La cantidad debe ser al menos 1.',
            'productos.*.descripcion.max' => 'La descripción no puede exceder 500 caracteres.',
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
            'productos.*.genero_id.required' => 'El género es obligatorio.',
            'productos.*.genero_id.exists' => 'El género seleccionado no es válido.',
            'productos.*.insumos.*.id.required' => 'Debe seleccionar un insumo.',
            'productos.*.insumos.*.id.exists' => 'El insumo seleccionado no existe.',
            'productos.*.insumos.*.cantidad_estimada.required' => 'La cantidad estimada del insumo es obligatoria.',
            'productos.*.insumos.*.cantidad_estimada.numeric' => 'La cantidad estimada debe ser un número.',
            'productos.*.insumos.*.cantidad_estimada.min' => 'La cantidad estimada debe ser mayor a 0.',
        ]);

        $this->assertMaxBordados($request, $maxBordados);

        $cotizacion = Cotizacion::findOrFail($id);

        $this->cotizacionService->actualizar($cotizacion, $request->all());

        return response()->json(['success' => 'Cotización actualizada exitosamente.']);
    }

    public function reactivar($id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $this->cotizacionService->reactivar($cotizacion);
        return response()->json(['success' => 'Cotización reactivada correctamente. Nueva validez: 15 días.']);
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
        // Cliente: preferimos el id exacto (Select2 del modal); si no viene, se
        // conserva la búsqueda parcial por nombre/razón social o documento.
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        } elseif ($request->filled('cliente')) {
            $term = trim($request->cliente);
            $query->whereHas('cliente', function ($c) use ($term) {
                $c->withTrashed()->whereHas('persona', function ($p) use ($term) {
                    $p->where('nombre', 'like', "%{$term}%")
                      ->orWhere('documento_identidad', 'like', "%{$term}%");
                });
            });
        }
        // Fecha de negocio de la cotización (paridad con el listado y la factura).
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_cotizacion', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_cotizacion', '<=', $request->fecha_hasta);
        }

        // Orden (paridad con el "Ordenar por" del listado en pantalla).
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;
            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;
            default:
                $orden = 'recientes';
                $query->orderBy('created_at', 'desc');
                break;
        }

        $cotizaciones = $query->get();

        $filtros = [];
        if ($request->filled('estado')) {
            $filtros['Estado'] = $request->estado;
        }
        if ($request->filled('cliente_id')) {
            $cli = Cliente::withTrashed()->with('persona')->find($request->cliente_id);
            $filtros['Cliente'] = $cli ? ($cli->nombre ?: '#' . $request->cliente_id) : '#' . $request->cliente_id;
        } elseif ($request->filled('cliente')) {
            $filtros['Cliente'] = trim($request->cliente);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de emisión'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Más recientes', 'total_desc' => 'Mayor total', 'total_asc' => 'Menor total'][$orden];

        $pdf = PDF::loadView('admin.cotizaciones.reporte_pdf', compact('cotizaciones', 'filtros'))
            ->setPaper('a4', 'portrait');
        return $pdf->stream('reporte_cotizaciones_' . now()->format('Ymd_His') . '.pdf');
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
            'productos.tipoProducto',
            'productos.genero',
            'productos.color',
            'productos.talla',
            'productos.bordados.logo:id,name',
        ]);

        // Cálculos financieros
        $ivaTasa = 0.16; // 16 %
        $subtotal = $cotizacion->total;
        $descuento = 0; // Ajustable en el futuro si se implementa
        $iva = round(($subtotal - $descuento) * $ivaTasa, 2);
        $totalPagar = round($subtotal - $descuento + $iva, 2);

        // Tasa de cambio aplicada (snapshot de la cotización) + su fecha BCV exacta.
        $tasaValor = $cotizacion->tasa_cambio_valor;
        $tasaFecha = $tasaValor
            ? optional(\App\Models\TasaCambio::tasaVigente(
                \Illuminate\Support\Carbon::parse($cotizacion->fecha_cotizacion ?? $cotizacion->created_at)->toDateString(), 'USD'
            ))->fecha_bcv
            : null;

        $pdf = PDF::loadView('admin.cotizaciones.factura', [
            'cotizacion' => $cotizacion,
            'subtotal' => $subtotal,
            'descuento' => $descuento,
            'iva' => $iva,
            'totalPagar' => $totalPagar,
            'tasaValor' => $tasaValor,
            'tasaFecha' => $tasaFecha,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('cotizacion_' . $cotizacion->id . '.pdf');
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
        $cotizacion = Cotizacion::with([
            'cliente.persona',
            'productos.producto.tipoProducto',
            'productos.tipoProducto.atributos.valores',
            'productos.bordados.logo:id,name',
        ])->findOrFail($id);

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
                'apellido' => '',
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

                // Línea dinámica (sin producto_id): arrastrar la variante (tipo + tela + atributos)
                // para que el pedido la persista, y construir un nombre legible desde el snapshot.
                $esDinamica = empty($detalle->producto_id) && !empty($detalle->tipo_producto_id);
                $telaSnap   = $detalle->tela_snapshot ?? null;
                $nombreLinea = $detalle->producto
                    ? $detalle->producto->nombre_completo
                    : trim(($detalle->tipoProducto?->nombre ?? 'Variante')
                        . (is_array($telaSnap) && !empty($telaSnap['nombre']) ? ' · ' . $telaSnap['nombre'] : ''));

                return [
                    'producto_id' => $detalle->producto_id,
                    'tipo_producto_id' => $detalle->tipo_producto_id,
                    'insumo_tela_id' => is_array($telaSnap) ? ($telaSnap['id'] ?? null) : null,
                    'atributo_valor_ids' => $esDinamica && $detalle->tipoProducto
                        ? $detalle->tipoProducto->valorIdsDesdeSnapshot($detalle->atributos_snapshot)
                        : [],
                    'sku' => $detalle->producto ? $detalle->producto->codigo : $detalle->sku_snapshot,
                    'imagen_url' => ($detalle->producto && $detalle->producto->imagen)
                        ? asset($detalle->producto->imagen)
                        : ($detalle->tipoProducto?->imagen_url),
                    'producto_nombre' => $nombreLinea ?: 'N/A',
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
                    'genero_id' => $detalle->genero_id,
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
