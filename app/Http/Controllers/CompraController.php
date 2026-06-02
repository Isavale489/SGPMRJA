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
        $insumos     = Insumo::where('estado', 1)
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
                'message'   => "Compra #{$compra->id} registrada exitosamente.",
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
                $map = [
                    'recibida' => 'success',
                    'borrador' => 'warning',
                    'anulada'  => 'danger',
                ];
                $color = $map[$c->estado] ?? 'secondary';
                return '<span class="badge bg-' . $color . '">' . ucfirst($c->estado) . '</span>';
            })
            ->addColumn('actions', function ($c) {
                $btn  = '<div class="d-flex gap-2 justify-content-center">';
                $btn .= '<a href="' . route('compras.show', $c->id) . '" class="btn btn-sm btn-soft-info" title="Ver"><i class="ri-eye-fill"></i></a>';
                if ($c->estado !== 'anulada') {
                    $btn .= '<button class="btn btn-sm btn-soft-danger anular-btn" data-id="' . $c->id . '" title="Anular"><i class="ri-close-circle-line"></i></button>';
                }
                $btn .= '</div>';
                return $btn;
            })
            ->rawColumns(['estado_badge', 'actions'])
            ->make(true);
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
}
