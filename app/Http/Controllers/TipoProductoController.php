<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TipoProductoController extends Controller
{
    /**
     * Listar todos los tipos de producto
     */
    public function index(): JsonResponse
    {
        $tipos = TipoProducto::orderBy('nombre')->get();
        return response()->json($tipos);
    }

    /**
     * Guardar nuevo tipo de producto
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tipo_producto,nombre',
            'codigo_prefijo' => 'required|string|max:5|unique:tipo_producto,codigo_prefijo|alpha',
            'descripcion' => 'nullable|string|max:500',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe un tipo con este nombre',
            'codigo_prefijo.required' => 'El prefijo de código es obligatorio',
            'codigo_prefijo.unique' => 'Ya existe un tipo con este prefijo',
            'codigo_prefijo.alpha' => 'El prefijo solo puede contener letras',
            'codigo_prefijo.max' => 'El prefijo no puede tener más de 5 caracteres',
        ]);

        $tipo = TipoProducto::create([
            'nombre' => $request->nombre,
            'codigo_prefijo' => strtoupper($request->codigo_prefijo),
            'descripcion' => $request->descripcion,
            'contador' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto creado correctamente',
            'tipo' => $tipo,
        ]);
    }

    /**
     * Mostrar un tipo de producto
     */
    public function show(TipoProducto $tipoProducto): JsonResponse
    {
        return response()->json($tipoProducto);
    }

    /**
     * Actualizar tipo de producto
     */
    public function update(Request $request, TipoProducto $tipoProducto): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tipo_producto,nombre,' . $tipoProducto->id,
            'codigo_prefijo' => 'required|string|max:5|unique:tipo_producto,codigo_prefijo,' . $tipoProducto->id . '|alpha',
            'descripcion' => 'nullable|string|max:500',
        ]);

        $tipoProducto->update([
            'nombre' => $request->nombre,
            'codigo_prefijo' => strtoupper($request->codigo_prefijo),
            'descripcion' => $request->descripcion,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto actualizado correctamente',
            'tipo' => $tipoProducto,
        ]);
    }

    /**
     * Eliminar tipo de producto
     */
    public function destroy(TipoProducto $tipoProducto): JsonResponse
    {
        // Verificar si tiene productos asociados
        if ($tipoProducto->productos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar. Hay productos asociados a este tipo.',
            ], 422);
        }

        $tipoProducto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto eliminado correctamente',
        ]);
    }

    /**
     * Obtener el próximo código para un tipo (con preview del modelo)
     */
    public function proximoCodigo(Request $request, TipoProducto $tipoProducto): JsonResponse
    {
        $modelo = $request->query('modelo', '');

        return response()->json([
            'codigo' => $tipoProducto->proximoCodigo($modelo),
            'abreviatura' => $modelo ? TipoProducto::abreviarModelo($modelo) : 'XXX',
        ]);
    }
}
