<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Proveedor;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class InsumoController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::where('estado', true)->get();
        return view('admin.insumos.index', compact('proveedores'));
    }

    public function getInsumos()
    {
        $insumos = Insumo::with('proveedor:id,razon_social');
        return DataTables::of($insumos)
            ->addColumn('proveedor_nombre', function ($insumo) {
                return $insumo->proveedor ? $insumo->proveedor->razon_social : 'Sin proveedor';
            })
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
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:Tela,Hilo,Botón,Cierre,Etiqueta,Otro',
            'unidad_medida' => 'required|string|max:20',
            'costo_unitario' => 'required|numeric|min:0',
            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'proveedor_id' => 'nullable|exists:proveedor,id',
        ]);

        $insumo = Insumo::create($request->all());

        return response()->json(['success' => 'Insumo creado exitosamente.']);
    }

    public function show($id)
    {
        $insumo = Insumo::with('proveedor:id,razon_social')->findOrFail($id);
        return response()->json($insumo);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'tipo' => 'required|in:Tela,Hilo,Botón,Cierre,Etiqueta,Otro',
            'unidad_medida' => 'required|string|max:20',
            'costo_unitario' => 'required|numeric|min:0',
            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',
            'proveedor_id' => 'nullable|exists:proveedor,id',
        ]);

        $insumo = Insumo::findOrFail($id);
        $insumo->update($request->all());

        return response()->json(['success' => 'Insumo actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $insumo = Insumo::findOrFail($id);
        $insumo->delete();
        return response()->json(['success' => 'Insumo eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        $insumos = Insumo::with('proveedor')->get();
        $pdf = \PDF::loadView('admin.insumos.reporte_pdf', compact('insumos'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('insumos_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
