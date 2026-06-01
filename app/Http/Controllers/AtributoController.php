<?php

namespace App\Http\Controllers;

use App\Models\Atributo;
use App\Models\TipoProducto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AtributoController extends Controller
{
    /**
     * Listado de atributos con conteos de valores y tipos asociados.
     */
    public function index(Request $request)
    {
        if ($request->wantsJson() || $request->ajax()) {
            $atributos = Atributo::withCount(['valores', 'tiposProducto'])
                ->with(['tiposProducto:id'])
                ->orderBy('nombre')
                ->get();

            return response()->json($atributos->map(function ($a) {
                $data = $a->only(['id', 'nombre', 'codigo', 'descripcion', 'valores_count', 'tipos_producto_count']);
                $data['tipos_producto_ids'] = $a->tiposProducto->pluck('id')->values();
                return $data;
            }));
        }

        $tiposProducto = TipoProducto::orderBy('nombre')->get(['id', 'nombre']);
        return view('admin.atributos.index', compact('tiposProducto'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'          => 'required|string|min:3|max:80|unique:atributo,nombre',
            'codigo'          => 'required|string|min:2|max:8|unique:atributo,codigo|regex:/^[A-Z0-9]+$/',
            'descripcion'     => 'nullable|string|max:191',
            'tipos_producto'  => 'nullable|array',
            'tipos_producto.*'=> 'integer|exists:tipo_producto,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe un atributo con este nombre.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.unique'   => 'Ya existe un atributo con este código.',
            'codigo.regex'    => 'El código solo admite letras mayúsculas y números.',
        ]);

        $atributo = Atributo::create([
            'nombre'      => trim($request->nombre),
            'codigo'      => strtoupper(trim($request->codigo)),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
        ]);

        $atributo->tiposProducto()->sync($request->input('tipos_producto', []));

        return response()->json([
            'success'  => true,
            'message'  => 'Atributo creado correctamente.',
            'atributo' => $atributo->loadCount(['valores', 'tiposProducto']),
        ]);
    }

    public function show(Atributo $atributo): JsonResponse
    {
        return response()->json(
            $atributo->load(['valores' => fn($q) => $q->orderBy('orden')->orderBy('nombre')])
        );
    }

    public function update(Request $request, Atributo $atributo): JsonResponse
    {
        $request->validate([
            'nombre'          => 'required|string|min:3|max:80|unique:atributo,nombre,' . $atributo->id,
            'descripcion'     => 'nullable|string|max:191',
            'tipos_producto'  => 'nullable|array',
            'tipos_producto.*'=> 'integer|exists:tipo_producto,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique'   => 'Ya existe otro atributo con este nombre.',
        ]);

        $atributo->update([
            'nombre'      => trim($request->nombre),
            'descripcion' => $request->descripcion ? trim($request->descripcion) : null,
        ]);

        $atributo->tiposProducto()->sync($request->input('tipos_producto', []));

        return response()->json([
            'success'  => true,
            'message'  => 'Atributo actualizado correctamente.',
            'atributo' => $atributo->loadCount(['valores', 'tiposProducto']),
        ]);
    }

    public function destroy(Atributo $atributo): JsonResponse
    {
        if ($atributo->tiposProducto()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar: el atributo está asignado a uno o más tipos de producto.',
            ], 422);
        }

        $productosAfectados = \DB::table('producto_atributo_valor')
            ->join('atributo_valor', 'atributo_valor.id', '=', 'producto_atributo_valor.atributo_valor_id')
            ->where('atributo_valor.atributo_id', $atributo->id)
            ->count();

        if ($productosAfectados > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar: {$productosAfectados} producto(s) usan valores de este atributo.",
            ], 422);
        }

        $atributo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Atributo eliminado correctamente.',
        ]);
    }

    public function checkNombre(Request $request): JsonResponse
    {
        $nombre    = trim((string) $request->input('nombre'));
        $excludeId = $request->input('exclude_id');

        if ($nombre === '') {
            return response()->json(['exists' => false]);
        }

        $query = Atributo::whereRaw('LOWER(nombre) = ?', [strtolower($nombre)]);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    public function checkCodigo(Request $request): JsonResponse
    {
        $codigo    = strtoupper(trim((string) $request->input('codigo')));
        $excludeId = $request->input('exclude_id');

        if ($codigo === '') {
            return response()->json(['exists' => false]);
        }

        $query = Atributo::where('codigo', $codigo);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }
}
