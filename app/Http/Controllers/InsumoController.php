<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class InsumoController extends Controller
{
    public function index()
    {
        return view('admin.insumos.index');
    }

    public function getInsumos(Request $request)
    {
        $query = Insumo::query();

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
            ->addColumn('stock_status', function ($insumo) {
                if ($insumo->stock_actual <= $insumo->stock_minimo) {
                    return 'bajo';
                } elseif ($insumo->stock_actual <= ($insumo->stock_minimo * 1.5)) {
                    return 'medio';
                } else {
                    return 'normal';
                }
            })
            ->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:100',
            'codigo'          => 'nullable|string|min:2|max:8|regex:/^[A-Z0-9]+$/|unique:insumo,codigo',
            'tipo'            => 'required|in:Tela,Hilo,Boton,Cierre,Etiqueta',
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
        $data = $request->only(['nombre', 'tipo', 'unidad_medida', 'costo_unitario', 'estado']);
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
        $insumo = Insumo::findOrFail($id);
        return response()->json($insumo);
    }

    public function update(Request $request, $id)
    {
        $insumo = Insumo::findOrFail($id);

        $request->validate([
            'nombre'          => 'required|string|max:100',
            'codigo'          => 'nullable|string|min:2|max:8|regex:/^[A-Z0-9]+$/|unique:insumo,codigo,' . $insumo->id,
            'tipo'            => 'required|in:Tela,Hilo,Boton,Cierre,Etiqueta',
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
        $data = $request->only(['nombre', 'tipo', 'unidad_medida', 'costo_unitario', 'estado']);
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
        $insumo->delete();
        return response()->json(['success' => 'Insumo eliminado exitosamente.']);
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
        $insumos = $query->get();
        $pdf = \PDF::loadView('admin.insumos.reporte_pdf', compact('insumos'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('insumos_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
