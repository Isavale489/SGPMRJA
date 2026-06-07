<?php

namespace App\Http\Controllers;

use App\Models\TipoInsumo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TipoInsumoController extends Controller
{
    /**
     * Listar tipos de insumo (activos o historial) con conteo de insumos. JSON para el modal.
     */
    public function index(Request $request): JsonResponse
    {
        $query = TipoInsumo::withCount('insumos')->orderBy('nombre');

        if ($request->boolean('historial')) {
            $query->onlyTrashed();
        }

        return response()->json($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|min:2|max:100|unique:tipo_insumo,nombre',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.unique'   => 'Ya existe un tipo de insumo con este nombre.',
        ]);

        $tipo = TipoInsumo::create([
            'nombre' => trim($request->nombre),
            'activo' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tipo de insumo creado correctamente.',
            'tipo'    => $tipo,
        ]);
    }

    public function show(TipoInsumo $tipoInsumo): JsonResponse
    {
        return response()->json($tipoInsumo);
    }

    public function update(Request $request, TipoInsumo $tipoInsumo): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|min:2|max:100|unique:tipo_insumo,nombre,' . $tipoInsumo->id,
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.unique'   => 'Ya existe un tipo de insumo con este nombre.',
        ]);

        $nombreAnterior = $tipoInsumo->nombre;
        $nombreNuevo    = trim($request->nombre);

        $tipoInsumo->update(['nombre' => $nombreNuevo]);

        // Propagar el renombrado a los insumos que usan este tipo (tipo es texto).
        if ($nombreAnterior !== $nombreNuevo) {
            \App\Models\Insumo::where('tipo', $nombreAnterior)->update(['tipo' => $nombreNuevo]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tipo de insumo actualizado correctamente.',
            'tipo'    => $tipoInsumo,
        ]);
    }

    public function destroy(TipoInsumo $tipoInsumo): JsonResponse
    {
        if ($tipoInsumo->insumos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede inhabilitar: hay insumos que usan este tipo.',
            ], 422);
        }

        $tipoInsumo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de insumo inhabilitado correctamente.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $tipoInsumo = TipoInsumo::onlyTrashed()->find($id);

        if (!$tipoInsumo) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de insumo no encontrado en historial.',
            ], 404);
        }

        $tipoInsumo->restore();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de insumo restaurado correctamente.',
            'tipo'    => $tipoInsumo,
        ]);
    }

    public function checkNombre(Request $request): JsonResponse
    {
        $nombre    = trim((string) $request->input('nombre'));
        $excludeId = $request->input('exclude_id');

        if ($nombre === '') {
            return response()->json(['exists' => false]);
        }

        $query = TipoInsumo::whereRaw('LOWER(nombre) = ?', [strtolower($nombre)]);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }
}
