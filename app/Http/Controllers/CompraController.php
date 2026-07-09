<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Models\TasaCambio;
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

    /**
     * Devuelve la tasa BCV (USD/VES) vigente para una fecha, para que el wizard
     * la precargue al elegir la fecha de compra. Si no hay tasa del día exacto,
     * cae a la última publicada antes de esa fecha (que es la que rige).
     */
    public function getTasa(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());

        // Validación liviana del formato de fecha; si no parsea, usamos hoy.
        try {
            $fecha = \Carbon\Carbon::parse($fecha)->toDateString();
        } catch (\Exception $e) {
            $fecha = now()->toDateString();
        }

        $tasa = TasaCambio::tasaVigente($fecha);

        if (!$tasa) {
            return response()->json([
                'encontrada' => false,
                'message'    => 'No hay tasa BCV registrada para esa fecha. Ingrésala manualmente.',
            ]);
        }

        return response()->json([
            'encontrada'    => true,
            'exacta'        => $tasa->fecha_bcv->toDateString() === $fecha,
            'valor'         => (float) $tasa->valor,
            'fecha_bcv'     => $tasa->fecha_bcv->format('Y-m-d'),
            'fecha_bcv_fmt' => $tasa->fecha_bcv->format('d/m/Y'),
        ]);
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
            'tasa_cambio'       => (float) $compra->tasa_cambio,
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
                'insumo_id'         => $d->insumo_id,
                'nombre'            => $d->insumo?->nombre,
                'cantidad'          => $d->cantidad,
                'costo_unitario'    => $d->costo_unitario,
                'costo_unitario_bs' => (float) $d->costo_unitario_bs,
                'aplica_iva'        => (bool) $d->aplica_iva,
                'subtotal'          => $d->subtotal,
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

        // Montos en bolívares (lo efectivamente pagado), derivados del costo en
        // Bs tecleado por línea. El IVA en Bs se aplica solo a la base gravada.
        $ivaPct      = (float) $compra->iva_porcentaje;
        $subtotalBs  = $compra->detalles->sum(fn($d) => (float) $d->cantidad * (float) $d->costo_unitario_bs);
        $baseGravBs  = $compra->detalles->where('aplica_iva', true)
            ->sum(fn($d) => (float) $d->cantidad * (float) $d->costo_unitario_bs);
        $ivaBs       = round($baseGravBs * $ivaPct / 100, 2);

        return response()->json([
            'id'               => $compra->id,
            'estado'           => $compra->estado,
            'numero_factura'   => $compra->numero_factura ?? 'S/N',
            'fecha_compra'     => $compra->fecha_compra?->format('d/m/Y') ?? '—',
            'observaciones'    => $compra->observaciones,
            'subtotal'         => number_format($compra->subtotal, 2, ',', '.'),
            'iva'              => number_format($compra->iva, 2, ',', '.'),
            'iva_porcentaje'   => rtrim(rtrim(number_format($compra->iva_porcentaje, 2, '.', ''), '0'), '.'),
            'total'            => number_format($compra->total, 2, ',', '.'),
            // Bolívares (formato venezolano: miles con '.', decimales con ',')
            'tasa_cambio'      => $compra->tasa_cambio ? number_format($compra->tasa_cambio, 4, ',', '.') : null,
            // Fecha de la tasa BCV aplicada (null si el valor no coincide con
            // la tasa vigente a la fecha de la compra, p. ej. tasa manual).
            'tasa_fecha_fmt'   => TasaCambio::fechaParaValor($compra->tasa_cambio, $compra->fecha_compra?->toDateString())?->format('d/m/Y'),
            'subtotal_bs'      => number_format($subtotalBs, 2, ',', '.'),
            'iva_bs'           => number_format($ivaBs, 2, ',', '.'),
            'total_bs'         => number_format($subtotalBs + $ivaBs, 2, ',', '.'),
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
                'nombre'            => $d->insumo?->nombre ?? 'N/A',
                'codigo'            => $d->insumo?->codigo,
                'tipo'              => $d->insumo?->tipo ?? '—',
                'unidad'            => $d->insumo?->unidad_medida ?? '—',
                'cantidad'          => number_format($d->cantidad, 2, ',', '.'),
                'costo_unitario'    => number_format($d->costo_unitario, 2, ',', '.'),
                'costo_unitario_bs' => number_format($d->costo_unitario_bs, 2, ',', '.'),
                'subtotal_bs'       => number_format((float) $d->cantidad * (float) $d->costo_unitario_bs, 2, ',', '.'),
                'aplica_iva'        => (bool) $d->aplica_iva,
                'subtotal'          => number_format($d->subtotal, 2, ',', '.'),
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
            // Búsqueda "contiene" (LIKE %texto%) sobre TODAS las columnas visibles
            // del listado: N° de factura, proveedor (nombre/razón social y
            // documento), fecha (formato d/m/Y como se muestra), total, estado
            // (texto del badge: recibida/borrador/anulada) e id. Sobrescribe POR
            // COMPLETO el buscador global de Yajra (sin pasar el 2º arg / false):
            // si se pasara `true`, Yajra correría además su búsqueda automática
            // sobre la columna derivada `proveedor_nombre` —que no existe en la
            // tabla `compra`— y generaría un SQL inválido que rompe el listado.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->where('compra.numero_factura', 'like', "%{$keyword}%")
                      ->orWhere('compra.id', 'like', "%{$keyword}%")
                      ->orWhere('compra.total', 'like', "%{$keyword}%")
                      ->orWhere('compra.estado', 'like', "%{$keyword}%")
                      ->orWhereRaw("DATE_FORMAT(compra.fecha_compra, '%d/%m/%Y') like ?", ["%{$keyword}%"])
                      ->orWhereHas('proveedor.persona', function ($p) use ($keyword) {
                          $p->where('nombre', 'like', "%{$keyword}%")
                            ->orWhereRaw("CONCAT(tipo_documento, documento_identidad) like ?", ["%{$keyword}%"]);
                      });
                });
            })
            ->addColumn('proveedor_nombre', fn($c) => $c->proveedor?->nombre_completo ?? 'N/A')
            ->addColumn('registrado_por', fn($c) => $c->registradoPor?->name ?? 'Sistema')
            ->addColumn('fecha_formateada', fn($c) => $c->fecha_compra?->format('d/m/Y') ?? '')
            ->addColumn('estado_badge', function ($c) {
                $map   = ['recibida' => 'success', 'borrador' => 'warning', 'anulada' => 'danger'];
                $icons = ['recibida' => 'ri-checkbox-circle-line', 'borrador' => 'ri-draft-line', 'anulada' => 'ri-close-circle-line'];
                $color = $map[$c->estado] ?? 'info';
                $icon  = $icons[$c->estado] ?? 'ri-question-line';
                $html  = '<span class="badge badge-soft-' . $color . '"><i class="' . $icon . ' me-1"></i>' . ucfirst($c->estado) . '</span>';

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
                // Botón Ver siempre visible
                $btn = '<div class="d-flex gap-1 justify-content-center align-items-center">';
                $btn .= '<button class="btn btn-sm btn-soft-info ver-btn" data-id="' . $c->id . '" title="Ver detalle"><i class="ri-eye-fill"></i></button>';

                $items = '';

                // PDF (Ver PDF)
                $items .= '<li><a href="' . route('compras.pdf', $c->id) . '" target="_blank" class="dropdown-item act-item act-pdf" title="Ver PDF"><span class="act-ic"><i class="ri-file-pdf-fill"></i></span>Ver PDF</a></li>';

                if ($c->estado === 'borrador') {
                    $items .= '<li><button type="button" class="dropdown-item act-item act-edit editar-btn" data-id="' . $c->id . '" title="Editar borrador"><span class="act-ic"><i class="ri-pencil-fill"></i></span>Editar</button></li>';
                    $items .= '<li><button type="button" class="dropdown-item act-item act-primary procesar-btn" data-id="' . $c->id . '" title="Procesar — actualiza stock"><span class="act-ic"><i class="ri-check-double-line"></i></span>Procesar</button></li>';
                    $items .= '<li><button type="button" class="dropdown-item act-item act-del eliminar-compra-btn" data-id="' . $c->id . '" title="Eliminar borrador"><span class="act-ic"><i class="ri-delete-bin-line"></i></span>Eliminar</button></li>';
                }

                if ($c->estado === 'recibida') {
                    $items .= '<li><button type="button" class="dropdown-item act-item act-del anular-btn" data-id="' . $c->id . '" title="Anular — revierte stock"><span class="act-ic"><i class="ri-close-circle-line"></i></span>Anular</button></li>';
                }

                if ($c->estado === 'anulada') {
                    if ($c->clonada) {
                        $items .= '<li><button type="button" class="dropdown-item act-item act-primary" disabled title="Esta compra ya fue clonada"><span class="act-ic"><i class="ri-file-copy-line"></i></span>Clonada</button></li>';
                    } else {
                        $items .= '<li><button type="button" class="dropdown-item act-item act-primary clonar-btn" data-id="' . $c->id . '" title="Clonar como nuevo borrador"><span class="act-ic"><i class="ri-file-copy-line"></i></span>Clonar</button></li>';
                    }
                }

                if (!empty($items)) {
                    $btn .= '<div class="dropdown d-inline-block">';
                    $btn .= '<button class="btn btn-sm btn-soft-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más acciones"><i class="ri-more-2-fill"></i></button>';
                    $btn .= '<ul class="dropdown-menu dropdown-menu-end actions-menu">' . $items . '</ul>';
                    $btn .= '</div>';
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

        // Orden del reporte.
        $orden = $request->input('orden', 'recientes');
        switch ($orden) {
            case 'monto_desc':
                $query->orderByDesc('total')->orderByDesc('id');
                break;
            case 'monto_asc':
                $query->orderBy('total')->orderByDesc('id');
                break;
            default:
                $orden = 'recientes';
                $query->orderByDesc('fecha_compra')->orderByDesc('id');
                break;
        }

        $compras = $query->get();

        $filtros = [];
        if ($request->filled('estado')) {
            $filtros['Estado'] = ucfirst($request->estado);
        }
        if ($request->filled('proveedor_id')) {
            $filtros['Proveedor'] = optional(\App\Models\Proveedor::find($request->proveedor_id))->nombre
                ?? ('#' . $request->proveedor_id);
        }
        if ($rango = \App\Support\ReporteFiltros::rango($request->fecha_desde, $request->fecha_hasta)) {
            $filtros['Fecha de compra'] = $rango;
        }
        $filtros['Orden'] = ['recientes' => 'Fecha reciente', 'monto_desc' => 'Mayor monto', 'monto_asc' => 'Menor monto'][$orden];

        $pdf = PDF::loadView('admin.compras.reporte_pdf', compact('compras', 'filtros'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_compras_' . now()->format('Ymd_His') . '.pdf');
    }

    public function compraPdf(Compra $compra)
    {
        $compra->load(['proveedor.persona', 'detalles.insumo', 'registradoPor:id,name']);

        // Fecha de la tasa BCV aplicada; null si el snapshot no coincide con la
        // tasa vigente a la fecha de la compra (tasa manual) — no se muestra.
        $tasaFecha = TasaCambio::fechaParaValor($compra->tasa_cambio, $compra->fecha_compra?->toDateString());

        $pdf = PDF::loadView('admin.compras.comprobante', compact('compra', 'tasaFecha'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('compra_' . str_pad($compra->id, 5, '0', STR_PAD_LEFT) . '.pdf');
    }
}
