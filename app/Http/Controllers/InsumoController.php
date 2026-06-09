<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTables;

class InsumoController extends Controller
{
    public function index()
    {
        $tiposInsumo = TipoInsumo::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);
        return view('admin.insumos.index', compact('tiposInsumo'));
    }

    /** Regla de validación del tipo: debe existir en el catálogo y estar activo. */
    private function tipoRule(): array
    {
        return ['required', 'string', Rule::exists('tipo_insumo', 'nombre')
            ->where('activo', true)->whereNull('deleted_at')];
    }

    public function getInsumos(Request $request)
    {
        $query = Insumo::query();

        // Historial: mostrar solo los inhabilitados (soft-deleted).
        if ($request->boolean('historial')) {
            $query->onlyTrashed();
        }

        // ══════════════════════════════════════════════════════════
        // FILTROS AVANZADOS — Server-Side (Patrón Maestro S-07)
        // ══════════════════════════════════════════════════════════

        if ($request->filled('filter_tipo')) {
            $query->where('tipo', $request->input('filter_tipo'));
        }

        if ($request->filled('filter_stock')) {
            $stock = $request->input('filter_stock');
            if ($stock === 'con_stock') {
                $query->where('insumo.stock_actual', '>', 0);
            } elseif ($stock === 'agotado') {
                $query->where('insumo.stock_actual', '<=', 0);
            }
        }

        $orden = $request->input('filter_orden', 'recientes');
        switch ($orden) {
            case 'mayor_costo':
                $query->orderBy('insumo.costo_unitario', 'desc');
                break;
            case 'menor_costo':
                $query->orderBy('insumo.costo_unitario', 'asc');
                break;
            case 'mayor_stock':
                $query->orderBy('insumo.stock_actual', 'desc');
                break;
            case 'menor_stock':
                $query->orderBy('insumo.stock_actual', 'asc');
                break;
            case 'recientes':
            default:
                $query->orderBy('insumo.created_at', 'desc');
                break;
        }

        return DataTables::of($query)
            // Búsqueda estricta: solo por nombre, código y tipo del insumo. Sobrescribe
            // el buscador global para no matchear contra las columnas numéricas (stock,
            // costo) que también son "searchable" por defecto.
            ->filter(function ($query) use ($request) {
                $keyword = trim((string) $request->input('search.value'));
                if ($keyword === '') {
                    return;
                }
                $query->where(function ($q) use ($keyword) {
                    $q->where('insumo.nombre', 'like', "{$keyword}%")
                      ->orWhere('insumo.codigo', 'like', "{$keyword}%")
                      ->orWhere('insumo.tipo', 'like', "{$keyword}%");
                });
            }, true)
            ->addColumn('stock_status', function ($insumo) {
                if ($insumo->stock_actual <= $insumo->stock_minimo) {
                    return 'bajo';
                } elseif ($insumo->stock_actual <= ($insumo->stock_minimo * 1.5)) {
                    return 'medio';
                } else {
                    return 'normal';
                }
            })
            ->addColumn('trashed', fn($insumo) => $insumo->trashed())
            ->make(true);
    }

    public function store(Request $request)
    {
        // Normalizar el código a MAYÚSCULAS antes de validar: el input usa
        // text-uppercase (solo visual), así que el valor real puede venir en minúsculas.
        if ($request->filled('codigo')) {
            $request->merge(['codigo' => strtoupper(trim($request->input('codigo')))]);
        }

        $request->validate([
            'nombre'          => 'required|string|max:100',
            'codigo'          => 'nullable|string|min:2|max:8|regex:/^[A-Z0-9]+$/|unique:insumo,codigo',
            'tipo'            => $this->tipoRule(),
            'unidad_medida'   => 'required|in:Metro,Kg,Gramo,Unidad,Rollo,Cono,Docena',
            'is_inventoriable'=> 'nullable|boolean',
            'costo_unitario'  => 'required|numeric|min:0.01',
            'stock_actual'    => 'nullable|numeric|min:0',
            'stock_minimo'    => 'nullable|numeric|min:0',
            'stock_maximo'    => 'nullable|numeric|min:0|gte:stock_minimo',
            'estado'          => 'nullable|boolean',
        ], [
            'codigo.regex'  => 'El código solo admite letras mayúsculas y números.',
            'codigo.unique' => 'Ya existe un insumo con este código.',
            'stock_maximo.gte' => 'La existencia máxima no puede ser menor que la mínima.',
        ]);

        $inventoriable = $request->boolean('is_inventoriable', true);
        $data = $request->only(['nombre', 'tipo', 'unidad_medida', 'costo_unitario']);
        // Todo insumo nace habilitado; el estatus se gobierna con Inhabilitar/Habilitar.
        $data['estado']           = true;
        $data['codigo']           = $request->filled('codigo') ? strtoupper(trim($request->codigo)) : null;
        $data['is_inventoriable'] = $inventoriable;
        $data['stock_actual']     = $inventoriable ? ($request->input('stock_actual', 0)) : 0;
        $data['stock_minimo']     = $inventoriable ? ($request->input('stock_minimo', 0)) : 0;
        $data['stock_maximo']     = $inventoriable ? ($request->input('stock_maximo', 0)) : 0;

        $insumo = Insumo::create($data);

        return response()->json(['success' => 'Insumo creado exitosamente.', 'insumo' => $insumo]);
    }

    public function show($id)
    {
        // withTrashed: permite ver el detalle de un insumo inhabilitado desde el historial.
        $insumo = Insumo::withTrashed()->findOrFail($id);
        $data = $insumo->toArray();
        $data['trashed'] = $insumo->trashed();
        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);

        // Normalizar el código a MAYÚSCULAS antes de validar (input usa text-uppercase visual).
        if ($request->filled('codigo')) {
            $request->merge(['codigo' => strtoupper(trim($request->input('codigo')))]);
        }

        $request->validate([
            'nombre'          => 'required|string|max:100',
            'codigo'          => 'nullable|string|min:2|max:8|regex:/^[A-Z0-9]+$/|unique:insumo,codigo,' . $insumo->id,
            'tipo'            => $this->tipoRule(),
            'unidad_medida'   => 'required|in:Metro,Kg,Gramo,Unidad,Rollo,Cono,Docena',
            'is_inventoriable'=> 'nullable|boolean',
            'costo_unitario'  => 'required|numeric|min:0.01',
            'stock_actual'    => 'nullable|numeric|min:0',
            'stock_minimo'    => 'nullable|numeric|min:0',
            'stock_maximo'    => 'nullable|numeric|min:0|gte:stock_minimo',
            'estado'          => 'nullable|boolean',
        ], [
            'codigo.regex'  => 'El código solo admite letras mayúsculas y números.',
            'codigo.unique' => 'Ya existe un insumo con este código.',
            'stock_maximo.gte' => 'La existencia máxima no puede ser menor que la mínima.',
        ]);

        $inventoriable = $request->boolean('is_inventoriable', true);
        // 'estado' NO se edita aquí: lo gobiernan Inhabilitar/Habilitar.
        $data = $request->only(['nombre', 'tipo', 'unidad_medida', 'costo_unitario']);
        $data['is_inventoriable'] = $inventoriable;
        $data['stock_actual']     = $inventoriable ? ($request->input('stock_actual', 0)) : 0;
        $data['stock_minimo']     = $inventoriable ? ($request->input('stock_minimo', 0)) : 0;
        $data['stock_maximo']     = $inventoriable ? ($request->input('stock_maximo', 0)) : 0;
        if (empty($insumo->codigo) && $request->filled('codigo')) {
            $data['codigo'] = strtoupper(trim($request->codigo));
        }

        $insumo->update($data);

        return response()->json(['success' => 'Insumo actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        // Inhabilitar = estado=false (lo respetan los selectores `where('estado',true)`)
        // + soft delete (queda en el historial, reversible con Habilitar).
        $insumo->update(['estado' => false]);
        $insumo->delete();
        return response()->json(['success' => 'Insumo inhabilitado exitosamente.']);
    }

    /**
     * Habilitar un insumo inhabilitado (soft-deleted): lo restaura y reactiva su estado.
     */
    public function restore($id)
    {
        $insumo = Insumo::onlyTrashed()->findOrFail($id);
        $insumo->restore();
        $insumo->update(['estado' => true]);
        return response()->json(['success' => 'Insumo habilitado exitosamente.']);
    }

    public function checkNombre(Request $request)
    {
        $nombre = $request->input('nombre');
        $excludeId = $request->input('exclude_id');
        if (!$nombre) return response()->json(['exists' => false]);

        $query = Insumo::whereRaw('LOWER(nombre) = ?', [strtolower($nombre)]);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        return response()->json(['exists' => $query->exists()]);
    }

    public function reportePdf(Request $request)
    {
        $query = Insumo::query();
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('stock')) {
            if ($request->stock === 'con_stock') $query->where('stock_actual', '>', 0);
            elseif ($request->stock === 'agotado') $query->where('stock_actual', '<=', 0);
        }
        // Rango por fecha de registro (created_at)
        if ($request->filled('fecha_desde')) {
            $query->whereDate('created_at', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->whereDate('created_at', '<=', $request->fecha_hasta);
        }
        $insumos = $query->get();
        $pdf = \PDF::loadView('admin.insumos.reporte_pdf', compact('insumos'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('insumos_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
