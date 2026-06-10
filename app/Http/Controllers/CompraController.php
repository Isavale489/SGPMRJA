<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\TipoInsumo;
use App\Services\CompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class CompraController extends Controller
{
    public function __construct(private CompraService $service) {}

    public function index(Request $request)
    {
        // Vista "anuladas": la píldora del header alterna entre el listado de
        // compras activas (borrador + recibida) y las anuladas.
        $verAnuladas = $request->boolean('anuladas');

        // Solo para el <select> de filtro del listado; el wizard elige el
        // proveedor por búsqueda de documento (no precarga el catálogo).
        $proveedores = Proveedor::with('persona')
            ->where('estado', 1)
            ->get();
        $insumos = Insumo::where('estado', 1)
            ->where('is_inventoriable', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'tipo', 'unidad_medida', 'costo_unitario', 'aplica_iva']);

        // Catálogo de tipos para el quick-create de insumos (mini-modal del wizard).
        $tiposInsumo = TipoInsumo::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        return view('admin.compras.index', compact('proveedores', 'insumos', 'tiposInsumo', 'verAnuladas'));
    }

    public function store(StoreCompraRequest $request)
    {
        try {
            $compra = $this->service->registrar($request->validated(), Auth::id());

            return response()->json([
                'success'   => true,
                'message'   => "Borrador de compra #{$compra->id} guardado. Procésalo cuando estés listo.",
                'compra_id' => $compra->id,
            ]);
        } catch (\Exception $e) {
            Log::error('CompraController@store: ' . $e->getMessage(), ['user' => Auth::id()]);
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error interno al registrar la compra. Intente nuevamente.',
            ], 500);
        }
    }

    public function update(StoreCompraRequest $request, Compra $compra)
    {
        try {
            $this->service->actualizar($compra, $request->validated());

            return response()->json([
                'success' => true,
                'message' => "Compra #{$compra->id} actualizada correctamente.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('CompraController@update: ' . $e->getMessage(), ['user' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar la compra.'], 500);
        }
    }

    public function procesar(Compra $compra)
    {
        try {
            $this->service->procesar($compra, Auth::id());

            return response()->json([
                'success' => true,
                'message' => "Compra #{$compra->id} procesada. Stock de insumos actualizado.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('CompraController@procesar: ' . $e->getMessage(), ['user' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Error al procesar la compra.'], 500);
        }
    }

    public function anular(Compra $compra)
    {
        try {
            $this->service->anular($compra, Auth::id());

            return response()->json([
                'success' => true,
                'message' => "Compra #{$compra->id} anulada. El stock ha sido revertido.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al anular la compra.'], 500);
        }
    }

    public function clonar(Compra $compra)
    {
        try {
            $nueva = $this->service->clonar($compra, Auth::id());

            return response()->json([
                'success'   => true,
                'message'   => "Compra clonada como borrador #{$nueva->id}. Revísala y procésala.",
                'compra_id' => $nueva->id,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('CompraController@clonar: ' . $e->getMessage(), ['user' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Error al clonar la compra.'], 500);
        }
    }

    public function destroy(Compra $compra)
    {
        try {
            $this->service->eliminar($compra);

            return response()->json([
                'success' => true,
                'message' => "Borrador #{$compra->id} eliminado correctamente.",
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('CompraController@destroy: ' . $e->getMessage(), ['user' => Auth::id()]);
            return response()->json(['success' => false, 'message' => 'Error al eliminar la compra.'], 500);
        }
    }

    public function getParaEditar(Compra $compra)
    {
        if ($compra->estado !== 'borrador') {
            return response()->json(['success' => false, 'message' => 'Solo se pueden editar borradores.'], 422);
        }

        $compra->load(['detalles.insumo', 'proveedor.persona']);
        $compra->proveedor?->loadCount('compras')->loadMax('compras', 'fecha_compra');

        return response()->json([
            'id'                => $compra->id,
            'proveedor_id'      => $compra->proveedor_id,
            'numero_factura'    => $compra->numero_factura,
            'fecha_compra'      => $compra->fecha_compra?->format('Y-m-d'),
            'observaciones'     => $compra->observaciones,
            'proveedor_data'    => [
                'id'     => $compra->proveedor_id,
                'nombre' => $compra->proveedor?->nombre_completo ?? '—',
                'doc'    => $compra->proveedor?->documento ?? '',
                'tel'    => $compra->proveedor?->telefono_unificado ?? '',
                'email'  => $compra->proveedor?->email_unificado ?? '',
                'tipo'   => $compra->proveedor?->tipo_proveedor ?? '',
                'compras' => $compra->proveedor?->compras_count ?? 0,
                'ultima'  => $compra->proveedor?->compras_max_fecha_compra,
            ],
            'items'             => $compra->detalles->map(fn($d) => [
                'insumo_id'      => $d->insumo_id,
                'nombre'         => $d->insumo?->nombre,
                'cantidad'       => $d->cantidad,
                'costo_unitario' => $d->costo_unitario,
                'aplica_iva'     => (bool) $d->aplica_iva,
                'subtotal'       => $d->subtotal,
            ]),
        ]);
    }

    public function getDetalle(Compra $compra)
    {
        $compra->load(['proveedor.persona', 'detalles.insumo', 'registradoPor']);

        $provName = $compra->proveedor?->nombre_completo ?? '—';
        $provIni  = collect(explode(' ', trim($provName)))
            ->filter()->take(2)
            ->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('') ?: '—';

        return response()->json([
            'id'               => $compra->id,
            'estado'           => $compra->estado,
            'numero_factura'   => $compra->numero_factura ?? 'S/N',
            'fecha_compra'     => $compra->fecha_compra?->format('d/m/Y') ?? '—',
            'observaciones'    => $compra->observaciones,
            'subtotal'         => number_format($compra->subtotal, 2),
            'iva'              => number_format($compra->iva, 2),
            'iva_porcentaje'   => rtrim(rtrim(number_format($compra->iva_porcentaje, 2, '.', ''), '0'), '.'),
            'total'            => number_format($compra->total, 2),
            'created_at'       => $compra->created_at?->format('d/m/Y H:i') ?? '—',
            'proveedor'        => [
                'nombre' => $provName,
                'ini'    => $provIni,
                'tipo'   => match ($compra->proveedor?->tipo_proveedor) {
                    'natural'  => 'Natural',
                    'juridico' => 'Jurídico',
                    default    => 'Proveedor',
                },
                'doc'   => $compra->proveedor?->documento ?? '',
                'tel'   => $compra->proveedor?->telefono_unificado ?? '',
                'email' => $compra->proveedor?->email_unificado ?? '',
            ],
            'registrado_por' => [
                'name'       => $compra->registradoPor?->name ?? 'Sistema',
                'avatar_url' => $compra->registradoPor?->avatar_url ?? '',
            ],
            'items' => $compra->detalles->map(fn($d) => [
                'nombre'         => $d->insumo?->nombre ?? 'N/A',
                'tipo'           => $d->insumo?->tipo ?? '—',
                'unidad'         => $d->insumo?->unidad_medida ?? '—',
                'cantidad'       => number_format($d->cantidad, 2),
                'costo_unitario' => number_format($d->costo_unitario, 2),
                'aplica_iva'     => (bool) $d->aplica_iva,
                'subtotal'       => number_format($d->subtotal, 2),
            ]),
        ]);
    }

    public function getCompras(Request $request)
    {
        $query = Compra::with(['proveedor.persona', 'registradoPor', 'anuladoPor'])
            ->select('compra.*');

        if ($request->filled('filter_proveedor_id')) {
            $query->where('proveedor_id', $request->filter_proveedor_id);
        }

        // Vista "anuladas" (píldora del header) vs vista de activas (default).
        if ($request->boolean('ver_anuladas')) {
            $query->where('estado', 'anulada');
        } elseif (in_array($request->filter_estado, ['recibida', 'borrador'], true)) {
            // Sub-filtro opcional dentro de las activas.
            $query->where('estado', $request->filter_estado);
        } else {
            $query->whereIn('estado', ['borrador', 'recibida']);
        }
        if ($request->filled('filter_fecha_desde')) {
            $query->whereDate('fecha_compra', '>=', $request->filter_fecha_desde);
        }
        if ($request->filled('filter_fecha_hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->filter_fecha_hasta);
        }

        // El orden lo gobierna DataTables (encabezados clicables). El default
        // "más reciente primero" se declara en el front (order: [[0,'desc']]).
        return DataTables::of($query)
            // Búsqueda estricta: solo por proveedor (nombre/razón social y documento)
            // y número de factura, tal como indica la barra. Sobrescribe el buscador
            // global para no romper sobre la columna derivada del proveedor.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->where('compra.numero_factura', 'like', "%{$keyword}%")
                      ->orWhereHas('proveedor.persona', function ($p) use ($keyword) {
                          $p->where('nombre', 'like', "%{$keyword}%")
                            ->orWhere('apellido', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(nombre, ' ', apellido) like ?", ["%{$keyword}%"])
                            ->orWhereRaw("CONCAT(tipo_documento, documento_identidad) like ?", ["%{$keyword}%"]);
                      });
                });
            }, true)
            ->addColumn('proveedor_nombre', fn($c) => $c->proveedor?->nombre_completo ?? 'N/A')
            ->addColumn('registrado_por', fn($c) => $c->registradoPor?->name ?? 'Sistema')
            ->addColumn('fecha_formateada', fn($c) => $c->fecha_compra?->format('d/m/Y') ?? '')
            ->addColumn('estado_badge', function ($c) {
                $map   = ['recibida' => 'success', 'borrador' => 'warning', 'anulada' => 'danger'];
                $color = $map[$c->estado] ?? 'secondary';
                $html  = '<span class="badge bg-' . $color . '">' . ucfirst($c->estado) . '</span>';

                if ($c->estado === 'anulada' && $c->anuladoPor) {
                    $nombre = e($c->anuladoPor->name);
                    $fecha  = $c->fecha_anulacion?->format('d/m/Y H:i') ?? '';
                    $html  .= '<div class="small text-muted mt-1">Anulada por: ' . $nombre;
                    if ($fecha) {
                        $html .= '<br><span class="text-muted">' . $fecha . '</span>';
                    }
                    $html .= '</div>';
                }

                return $html;
            })
            ->addColumn('actions', function ($c) {
                $btn = '<div class="d-flex gap-2 justify-content-center">';
                $btn .= '<button class="btn btn-sm btn-soft-info ver-btn" data-id="' . $c->id . '" title="Ver detalle"><i class="ri-eye-fill"></i></button>';

                if ($c->estado === 'borrador') {
                    $btn .= '<button class="btn btn-sm btn-soft-warning editar-btn" data-id="' . $c->id . '" title="Editar borrador"><i class="ri-pencil-fill"></i></button>';
                    $btn .= '<button class="btn btn-sm btn-soft-success procesar-btn" data-id="' . $c->id . '" title="Procesar — actualiza stock"><i class="ri-check-double-line"></i></button>';
                    $btn .= '<button class="btn btn-sm btn-soft-danger eliminar-compra-btn" data-id="' . $c->id . '" title="Eliminar borrador"><i class="ri-delete-bin-line"></i></button>';
                }

                if ($c->estado === 'recibida') {
                    $btn .= '<button class="btn btn-sm btn-soft-danger anular-btn" data-id="' . $c->id . '" title="Anular — revierte stock"><i class="ri-close-circle-line"></i></button>';
                }

                if ($c->estado === 'anulada') {
                    if ($c->clonada) {
                        $btn .= '<button class="btn btn-sm btn-soft-secondary" disabled title="Esta compra ya fue clonada"><i class="ri-file-copy-line"></i></button>';
                    } else {
                        $btn .= '<button class="btn btn-sm btn-soft-secondary clonar-btn" data-id="' . $c->id . '" title="Clonar como nuevo borrador"><i class="ri-file-copy-line"></i></button>';
                    }
                }

                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['estado_badge', 'actions'])
            ->make(true);
    }

    public function reportePdf(Request $request)
    {
        $query = Compra::with(['proveedor.persona', 'registradoPor:id,name']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('proveedor_id')) {
            $query->where('proveedor_id', $request->proveedor_id);
        }
        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_compra', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->fecha_hasta);
        }

        $compras = $query->orderByDesc('fecha_compra')->orderByDesc('id')->get();

        $pdf = PDF::loadView('admin.compras.reporte_pdf', compact('compras'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('reporte_compras_' . now()->format('Ymd_His') . '.pdf');
    }

    public function compraPdf(Compra $compra)
    {
        $compra->load(['proveedor.persona', 'detalles.insumo', 'registradoPor:id,name']);

        $pdf = PDF::loadView('admin.compras.comprobante', compact('compra'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('compra_' . str_pad($compra->id, 5, '0', STR_PAD_LEFT) . '.pdf');
    }
}
