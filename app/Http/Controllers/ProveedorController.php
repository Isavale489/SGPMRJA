<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProveedorController extends Controller
{
    public function index()
    {
        return view('admin.proveedores.index');
    }

    public function getProveedores()
    {
        $proveedores = Proveedor::all();
        return DataTables::of($proveedores)->make(true);
    }

    public function store(Request $request)
    {
        $request->validate([
            'razon_social' => 'required|string|max:100',
            'rif' => 'required|string|max:15|unique:proveedor',
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'contacto' => 'nullable|string|max:100',
            'telefono_contacto' => 'nullable|string|max:20',
        ]);

        $proveedor = Proveedor::create($request->all());

        return response()->json(['success' => 'Proveedor creado exitosamente.']);
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return response()->json([
            'id' => $proveedor->id,
            'razon_social' => $proveedor->razon_social,
            'rif' => $proveedor->rif,
            'direccion' => $proveedor->direccion,
            'telefono' => $proveedor->telefono,
            'email' => $proveedor->email,
            'contacto' => $proveedor->contacto,
            'telefono_contacto' => $proveedor->telefono_contacto,
            'estado' => $proveedor->estado,
            'created_at' => $proveedor->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $proveedor->updated_at->format('d/m/Y H:i:s')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'razon_social' => 'required|string|max:100',
            'rif' => 'required|string|max:15|unique:proveedor,rif,' . $id,
            'direccion' => 'nullable|string|max:200',
            'telefono' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'contacto' => 'nullable|string|max:100',
            'telefono_contacto' => 'nullable|string|max:20',
        ]);

        $proveedor = Proveedor::findOrFail($id);
        $proveedor->update($request->all());

        return response()->json(['success' => 'Proveedor actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();
        return response()->json(['success' => 'Proveedor eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        $proveedores = Proveedor::all();
        $pdf = \PDF::loadView('admin.proveedores.reporte_pdf', compact('proveedores'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('proveedores_' . now()->format('Y-m-d_H-i-s') . '.pdf');
    }
}
