<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompraRequest;
use App\Models\Compra;
use App\Models\Insumo;
use App\Models\Proveedor;
use App\Services\CompraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PDF;
use Yajra\DataTables\Facades\DataTables;

class CompraController extends Controller
{
    public function __construct(private CompraService $service) {}

    public function index()
    {
        $proveedores = Proveedor::with('persona')
            ->withCount(['compras as compras_count' => fn($q) => $q->where('estado', '!=', 'anulada')])
            ->withMax(['compras as ultima_compra' => fn($q) => $q->where('estado', '!=', 'anulada')], 'fecha_compra')
            ->where('estado', 1)
            ->get();
        $insumos = Insumo::where('estado', 1)
            ->where('is_inventoriable', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'tipo', 'unidad_medida', 'costo_unitario']);

        return view('admin.compras.index', compact('proveedores', 'insumos'));
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

    public function getParaEditar(Compra $compra)
    {
        if ($compra->estado !== 'borrador') {
            return response()->json(['success' => false, 'message' => 'Solo se pueden editar borradores.'], 422);
        }

        $compra->load('detalles.insumo');

        return response()->json([
            'id'                => $compra->id,
            'proveedor_id'      => $compra->proveedor_id,
            'numero_factura'    => $compra->numero_factura,
            'fecha_compra'      => $compra->fecha_compra?->format('Y-m-d'),
            'fecha_vencimiento' => $compra->fecha_vencimiento?->format('Y-m-d'),
            'tipo_pago'         => $compra->tipo_pago,
            'observaciones'     => $compra->observaciones,
            'iva_porcentaje'    => $compra->subtotal > 0
                ? round(($compra->iva / $compra->subtotal) * 100)
                : 16,
            'items'             => $compra->detalles->map(fn($d) => [
                'insumo_id'      => $d->insumo_id,
                'nombre'         => $d->insumo?->nombre,
                'cantidad'       => $d->cantidad,
                'costo_unitario' => $d->costo_unitario,
                'subtotal'       => $d->subtotal,
            ]),
        ]);
    }

    public function show(Compra $compra)
    {
        $compra->load(['proveedor.persona', 'detalles.insumo', 'registradoPor']);

        return view('admin.compras.show', compact('compra'));
    }

    public function getCompras(Request $request)
    {
        $query = Compra::with(['proveedor.persona', 'registradoPor'])
            ->select('compra.*');

        if ($request->filled('filter_proveedor_id')) {
            $query->where('proveedor_id', $request->filter_proveedor_id);
        }
        if ($request->filled('filter_estado')) {
            $query->where('estado', $request->filter_estado);
        }
        if ($request->filled('filter_tipo_pago')) {
            $query->where('tipo_pago', $request->filter_tipo_pago);
        }
        if ($request->filled('filter_fecha_desde')) {
            $query->whereDate('fecha_compra', '>=', $request->filter_fecha_desde);
        }
        if ($request->filled('filter_fecha_hasta')) {
            $query->whereDate('fecha_compra', '<=', $request->filter_fecha_hasta);
        }

        return DataTables::of($query)
            ->addColumn('proveedor_nombre', fn($c) => $c->proveedor?->nombre_completo ?? 'N/A')
            ->addColumn('registrado_por', fn($c) => $c->registradoPor?->name ?? 'Sistema')
            ->addColumn('fecha_formateada', fn($c) => $c->fecha_compra?->format('d/m/Y') ?? '')
            ->addColumn('estado_badge', function ($c) {
                $map = ['recibida' => 'success', 'borrador' => 'warning', 'anulada' => 'danger'];
                $color = $map[$c->estado] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($c->estado) . '</span>';
            })
            ->addColumn('actions', function ($c) {
                $btn = '<div class="d-flex gap-2 justify-content-center">';
                $btn .= '<a href="' . route('compras.show', $c->id) . '" class="btn btn-sm btn-soft-info" title="Ver detalle"><i class="ri-eye-fill"></i></a>';

                if ($c->estado === 'borrador') {
                    $btn .= '<button class="btn btn-sm btn-soft-warning editar-btn" data-id="' . $c->id . '" title="Editar borrador"><i class="ri-pencil-fill"></i></button>';
                    $btn .= '<button class="btn btn-sm btn-soft-success procesar-btn" data-id="' . $c->id . '" title="Procesar — actualiza stock"><i class="ri-check-double-line"></i></button>';
                }

                if ($c->estado === 'recibida') {
                    $btn .= '<button class="btn btn-sm btn-soft-danger anular-btn" data-id="' . $c->id . '" title="Anular — revierte stock"><i class="ri-close-circle-line"></i></button>';
                }

                if ($c->estado === 'anulada') {
                    $btn .= '<button class="btn btn-sm btn-soft-secondary clonar-btn" data-id="' . $c->id . '" title="Clonar como nuevo borrador"><i class="ri-file-copy-line"></i></button>';
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
        if ($request->filled('tipo_pago')) {
            $query->where('tipo_pago', $request->tipo_pago);
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
