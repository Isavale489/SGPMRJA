<?php

namespace App\Http\Controllers;

use App\Models\Atributo;
use App\Models\AtributoValor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AtributoValorController extends Controller
{
    /**
     * Listado de valores de un atributo, ordenados.
     */
    public function index(Atributo $atributo): JsonResponse
    {
        $valores = $atributo->valores()
            ->withCount('productos')
            ->get();

        return response()->json($valores);
    }

    public function store(Request $request, Atributo $atributo): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|min:1|max:80',
            'codigo' => 'required|string|min:1|max:8|regex:/^[A-Z0-9]+$/',
            'orden'  => 'nullable|integer|min:0|max:9999',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.regex'    => 'El código solo admite letras mayúsculas y números.',
        ]);

        $nombre = trim($request->nombre);
        $codigo = strtoupper(trim($request->codigo));

        // Únicos dentro del atributo
        if ($atributo->valores()->where('nombre', $nombre)->exists()) {
            return response()->json([
                'success' => false,
                'errors'  => ['nombre' => ['Ya existe un valor con este nombre en el atributo.']],
            ], 422);
        }
        if ($atributo->valores()->where('codigo', $codigo)->exists()) {
            return response()->json([
                'success' => false,
                'errors'  => ['codigo' => ['Ya existe un valor con este código en el atributo.']],
            ], 422);
        }

        $orden = $request->filled('orden')
            ? (int) $request->orden
            : ((int) $atributo->valores()->max('orden') + 1);

        $valor = $atributo->valores()->create([
            'nombre' => $nombre,
            'codigo' => $codigo,
            'orden'  => $orden,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Valor agregado correctamente.',
            'valor'   => $valor->loadCount('productos'),
        ]);
    }

    public function update(Request $request, Atributo $atributo, AtributoValor $valor): JsonResponse
    {
        if ($valor->atributo_id !== $atributo->id) {
            abort(404);
        }

        $request->validate([
            'nombre' => 'required|string|min:1|max:80',
            // codigo es inmutable: cambiarlo rompería SKUs históricos.
            'orden'  => 'nullable|integer|min:0|max:9999',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
        ]);

        $nombre = trim($request->nombre);

        if ($atributo->valores()->where('nombre', $nombre)->where('id', '!=', $valor->id)->exists()) {
            return response()->json([
                'success' => false,
                'errors'  => ['nombre' => ['Ya existe otro valor con este nombre en el atributo.']],
            ], 422);
        }

        $payload = ['nombre' => $nombre];
        if ($request->filled('orden')) {
            $payload['orden'] = (int) $request->orden;
        }

        $valor->update($payload);

        return response()->json([
            'success' => true,
            'message' => 'Valor actualizado correctamente.',
            'valor'   => $valor->loadCount('productos'),
        ]);
    }

    public function destroy(Atributo $atributo, AtributoValor $valor): JsonResponse
    {
        if ($valor->atributo_id !== $atributo->id) {
            abort(404);
        }

        $usos = $valor->productos()->count();
        if ($usos > 0) {
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar: {$usos} producto(s) usan este valor.",
            ], 422);
        }

        $valor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Valor eliminado correctamente.',
        ]);
    }

    /**
     * Reordena valores en bloque. Recibe { ids: [v1, v2, v3] } y los reasigna en ese orden.
     */
    public function reorder(Request $request, Atributo $atributo): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:atributo_valor,id',
        ]);

        $idsValidos = $atributo->valores()->whereIn('id', $request->ids)->pluck('id')->all();
        if (count($idsValidos) !== count($request->ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Algún valor no pertenece a este atributo.',
            ], 422);
        }

        \DB::transaction(function () use ($request) {
            foreach ($request->ids as $idx => $id) {
                AtributoValor::where('id', $id)->update(['orden' => $idx + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Orden actualizado.',
        ]);
    }
}
