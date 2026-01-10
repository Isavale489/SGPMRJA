<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\TipoProducto;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use PDF;

class ProductoController extends Controller
{
    public function index()
    {
        $tiposProducto = TipoProducto::orderBy('nombre')->get();
        return view('admin.productos.index', compact('tiposProducto'));
    }

    public function getProductos()
    {
        $productos = Producto::with('tipoProducto')->get();
        return DataTables::of($productos)
            ->addColumn('tipo_nombre', function ($producto) {
                return $producto->tipoProducto ? $producto->tipoProducto->nombre : 'Sin tipo';
            })
            ->addColumn('nombre_completo', function ($producto) {
                return $producto->nombre_completo;
            })
            ->make(true);
    }

    private function handleFileUpload($file, $oldPath, $directory)
    {
        if ($oldPath && file_exists(public_path($oldPath))) {
            unlink(public_path($oldPath));
        }
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);
        return $directory . '/' . $filename;
    }

    public function store(Request $request)
    {
        $request->validate([
            'tipo_producto_id' => 'required|exists:tipo_producto,id',
            'modelo' => 'required|string|max:100',
            'precio_base' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'tipo_producto_id.required' => 'Debe seleccionar un tipo de producto',
            'tipo_producto_id.exists' => 'El tipo de producto no existe',
            'modelo.required' => 'El modelo es obligatorio',
            'precio_base.required' => 'El precio base es obligatorio',
            'precio_base.numeric' => 'El precio debe ser un número',
        ]);

        // Obtener tipo de producto y generar código con modelo
        $tipoProducto = TipoProducto::findOrFail($request->tipo_producto_id);
        $codigo = $tipoProducto->generarCodigo($request->modelo);

        $producto = new Producto();
        $producto->tipo_producto_id = $request->tipo_producto_id;
        $producto->codigo = $codigo;
        $producto->modelo = $request->modelo;
        $producto->descripcion = $request->descripcion;
        $producto->precio_base = $request->precio_base;
        $producto->estado = $request->estado ?? true;

        if ($request->hasFile('imagen')) {
            $producto->imagen = $this->handleFileUpload(
                $request->file('imagen'),
                null,
                'productoimg/imagenes'
            );
        }

        $producto->save();

        return response()->json([
            'success' => 'Producto creado exitosamente.',
            'codigo' => $codigo,
        ]);
    }

    public function show($id)
    {
        $producto = Producto::with('tipoProducto')->findOrFail($id);
        return response()->json([
            'id' => $producto->id,
            'tipo_producto_id' => $producto->tipo_producto_id,
            'tipo_nombre' => $producto->tipoProducto ? $producto->tipoProducto->nombre : null,
            'codigo' => $producto->codigo,
            'nombre' => $producto->nombre_completo,
            'descripcion' => $producto->descripcion,
            'modelo' => $producto->modelo,
            'precio_base' => $producto->precio_base,
            'imagen' => $producto->imagen ? asset($producto->imagen) : null,
            'estado' => $producto->estado,
            'created_at' => $producto->created_at->format('d/m/Y H:i:s'),
            'updated_at' => $producto->updated_at->format('d/m/Y H:i:s')
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tipo_producto_id' => 'required|exists:tipo_producto,id',
            'modelo' => 'required|string|max:100',
            'precio_base' => 'required|numeric|min:0',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $producto = Producto::findOrFail($id);

        // Si cambia el tipo o modelo, regenerar código
        $modeloCambiado = $producto->modelo !== $request->modelo;
        $tipoCambiado = $producto->tipo_producto_id != $request->tipo_producto_id;

        if ($tipoCambiado || $modeloCambiado) {
            $tipoProducto = TipoProducto::findOrFail($request->tipo_producto_id);
            $producto->codigo = $tipoProducto->generarCodigo($request->modelo);
            $producto->tipo_producto_id = $request->tipo_producto_id;
        }

        $producto->modelo = $request->modelo;
        $producto->descripcion = $request->descripcion;
        $producto->precio_base = $request->precio_base;
        $producto->estado = $request->estado ?? $producto->estado;

        if ($request->hasFile('imagen')) {
            $producto->imagen = $this->handleFileUpload(
                $request->file('imagen'),
                $producto->imagen,
                'productoimg/imagenes'
            );
        }

        $producto->save();

        return response()->json(['success' => 'Producto actualizado exitosamente.']);
    }

    public function destroy($id)
    {
        $producto = Producto::findOrFail($id);
        if ($producto->imagen && file_exists(public_path($producto->imagen))) {
            unlink(public_path($producto->imagen));
        }
        $producto->delete();
        return response()->json(['success' => 'Producto eliminado exitosamente.']);
    }

    public function reportePdf()
    {
        $productos = Producto::with('tipoProducto')->get();
        $data = [
            'title' => 'Reporte de Productos',
            'date' => date('m/d/Y'),
            'productos' => $productos
        ];

        $pdf = PDF::loadView('admin.productos.reporte_pdf', $data);
        return $pdf->download('productos-reporte-' . time() . '.pdf');
    }
}
