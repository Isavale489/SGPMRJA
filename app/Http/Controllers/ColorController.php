<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Retorna la lista de colores activos como JSON, agrupados por 'grupo'.
     * Usado por el modal de selección de color en cotizaciones y pedidos.
     */
    public function getColores(Request $request)
    {
        $colores = Color::activo()
            ->orderBy('grupo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'hex_referencial', 'grupo']);

        return response()->json($colores);
    }

    /**
     * Maestro de Colores: vista de catálogo (navegador) o JSON (AJAX/DataTable).
     */
    public function index(Request $request)
    {
        $historial = $request->boolean('historial');

        if ($request->wantsJson() || $request->ajax()) {
            $query = Color::orderBy('grupo')->orderBy('nombre');

            if ($historial) {
                $query->onlyTrashed();
            }

            return response()->json($query->get());
        }

        // Grupos existentes para alimentar el datalist del formulario.
        $grupos = Color::withTrashed()
            ->whereNotNull('grupo')
            ->where('grupo', '!=', '')
            ->distinct()
            ->orderBy('grupo')
            ->pluck('grupo');

        return view('admin.colores.index', [
            'historial' => $historial,
            'grupos'    => $grupos,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validar($request);

        $color = Color::create([
            'nombre'          => trim($data['nombre']),
            'hex_referencial' => strtoupper($data['hex_referencial']),
            'grupo'           => !empty($data['grupo']) ? trim($data['grupo']) : null,
            'activo'          => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color creado correctamente.',
            'color'   => $color,
        ]);
    }

    public function show(Color $color): JsonResponse
    {
        return response()->json($color);
    }

    public function update(Request $request, Color $color): JsonResponse
    {
        $data = $this->validar($request, $color->id);

        $color->update([
            'nombre'          => trim($data['nombre']),
            'hex_referencial' => strtoupper($data['hex_referencial']),
            'grupo'           => !empty($data['grupo']) ? trim($data['grupo']) : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Color actualizado correctamente.',
            'color'   => $color,
        ]);
    }

    public function destroy(Color $color): JsonResponse
    {
        $color->delete();

        return response()->json([
            'success' => true,
            'message' => 'Color inhabilitado correctamente.',
        ]);
    }

    public function restore(int $id): JsonResponse
    {
        $color = Color::onlyTrashed()->find($id);

        if (!$color) {
            return response()->json([
                'success' => false,
                'message' => 'Color no encontrado en historial.',
            ], 404);
        }

        $color->restore();

        return response()->json([
            'success' => true,
            'message' => 'Color restaurado correctamente.',
            'color'   => $color,
        ]);
    }

    /**
     * Validación AJAX onblur del nombre (unicidad case-insensitive).
     */
    public function checkNombre(Request $request): JsonResponse
    {
        $nombre    = trim((string) $request->input('nombre'));
        $excludeId = $request->input('exclude_id');

        if ($nombre === '') {
            return response()->json(['exists' => false]);
        }

        $query = Color::withTrashed()->whereRaw('LOWER(nombre) = ?', [strtolower($nombre)]);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return response()->json(['exists' => $query->exists()]);
    }

    /**
     * Reglas compartidas por store/update. El hex se valida con formato #RRGGBB.
     */
    private function validar(Request $request, ?int $id = null): array
    {
        $uniqueNombre = 'unique:color,nombre' . ($id ? ',' . $id : '');

        return $request->validate([
            'nombre'          => ['required', 'string', 'min:2', 'max:100', $uniqueNombre],
            'hex_referencial' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'grupo'           => ['nullable', 'string', 'max:100'],
        ], [
            'nombre.required'          => 'El nombre es obligatorio.',
            'nombre.min'               => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.unique'            => 'Ya existe un color con este nombre.',
            'hex_referencial.required' => 'El color HEX es obligatorio.',
            'hex_referencial.regex'    => 'El color HEX debe tener el formato #RRGGBB.',
        ]);
    }
}
