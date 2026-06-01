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
    public function index(Request $request): JsonResponse
    {
        $query = TipoProducto::withCount(['productos', 'atributos'])->orderBy('nombre');

        if ($request->boolean('historial')) {
            $query->onlyTrashed();
        }

        $tipos = $query->get();
        return response()->json($tipos);
    }

    /**
     * Guardar nuevo tipo de producto
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tipo_producto,nombre',
            'prefijo' => 'required|string|max:5|unique:tipo_producto,prefijo|alpha',
            'descripcion' => 'nullable|string|max:500',
            'precio_confeccion' => 'nullable|numeric|min:0|max:99999.99',
            'requiere_tela' => 'nullable|boolean',
            'consumo_tela_por_unidad' => 'nullable|numeric|min:0|max:9999.99',
            'atributos' => 'nullable|array',
            'atributos.*.id' => 'required_with:atributos|integer|exists:atributo,id',
            'atributos.*.orden' => 'required_with:atributos|integer|min:1|max:99',
            'insumos_default' => 'nullable|array',
            'insumos_default.*.id' => 'required_with:insumos_default|integer|exists:insumo,id',
            'insumos_default.*.cantidad_estimada' => 'required_with:insumos_default|numeric|min:0.01',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe un tipo con este nombre',
            'prefijo.required' => 'El prefijo de código es obligatorio',
            'prefijo.unique' => 'Ya existe un tipo con este prefijo',
            'prefijo.alpha' => 'El prefijo solo puede contener letras',
            'prefijo.max' => 'El prefijo no puede tener más de 5 caracteres',
        ]);

        $tipo = TipoProducto::create([
            'nombre' => $request->nombre,
            'prefijo' => strtoupper($request->prefijo),
            'descripcion' => $request->descripcion,
            'precio_confeccion' => $request->input('precio_confeccion', 0),
            'requiere_tela' => $request->boolean('requiere_tela', true),
            'consumo_tela_por_unidad' => $request->input('consumo_tela_por_unidad', 0),
        ]);

        $this->syncAtributos($tipo, $request->input('atributos', []));
        $this->syncInsumosDefault($tipo, $request->input('insumos_default', []));

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto creado correctamente',
            'tipo' => $tipo->load(['atributos', 'insumosDefault']),
        ]);
    }

    /**
     * Mostrar un tipo de producto
     */
    public function show(TipoProducto $tipoProducto): JsonResponse
    {
        $tipoProducto->load([
            'atributos' => function ($q) {
                $q->orderBy('tipo_producto_atributo.orden');
            },
            'atributos.valores',
            'insumosDefault',
        ]);

        return response()->json($tipoProducto);
    }

    /**
     * Actualizar tipo de producto
     */
    public function update(Request $request, TipoProducto $tipoProducto): JsonResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:tipo_producto,nombre,' . $tipoProducto->id,
            'prefijo' => 'required|string|max:5|unique:tipo_producto,prefijo,' . $tipoProducto->id . '|alpha',
            'descripcion' => 'nullable|string|max:500',
            'precio_confeccion' => 'nullable|numeric|min:0|max:99999.99',
            'requiere_tela' => 'nullable|boolean',
            'consumo_tela_por_unidad' => 'nullable|numeric|min:0|max:9999.99',
            'atributos' => 'nullable|array',
            'atributos.*.id' => 'required_with:atributos|integer|exists:atributo,id',
            'atributos.*.orden' => 'required_with:atributos|integer|min:1|max:99',
            'insumos_default' => 'nullable|array',
            'insumos_default.*.id' => 'required_with:insumos_default|integer|exists:insumo,id',
            'insumos_default.*.cantidad_estimada' => 'required_with:insumos_default|numeric|min:0.01',
        ]);

        $tipoProducto->update([
            'nombre' => $request->nombre,
            'prefijo' => strtoupper($request->prefijo),
            'descripcion' => $request->descripcion,
            'precio_confeccion' => $request->input('precio_confeccion', $tipoProducto->precio_confeccion),
            'requiere_tela' => $request->boolean('requiere_tela', $tipoProducto->requiere_tela),
            'consumo_tela_por_unidad' => $request->input('consumo_tela_por_unidad', $tipoProducto->consumo_tela_por_unidad),
        ]);

        $this->syncAtributos($tipoProducto, $request->input('atributos', []));
        $this->syncInsumosDefault($tipoProducto, $request->input('insumos_default', []));

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto actualizado correctamente',
            'tipo' => $tipoProducto->load(['atributos', 'insumosDefault']),
        ]);
    }

    /**
     * Sincroniza la asociación tipo↔atributo respetando el orden indicado.
     * Bloquea la remoción de atributos que estén siendo usados por productos del tipo.
     */
    private function syncAtributos(TipoProducto $tipo, array $atributos): void
    {
        $sync = [];
        foreach ($atributos as $a) {
            $sync[(int) $a['id']] = [
                'es_obligatorio' => true,
                'orden' => (int) $a['orden'],
            ];
        }

        $tipo->atributos()->sync($sync);
    }

    /**
     * Sincroniza los insumos default del tipo (templates de orden de producción).
     * Cada entrada: ['id' => insumo_id, 'cantidad_estimada' => decimal]
     */
    private function syncInsumosDefault(TipoProducto $tipo, array $insumos): void
    {
        $sync = [];
        foreach ($insumos as $i) {
            $sync[(int) $i['id']] = [
                'cantidad_estimada' => (float) $i['cantidad_estimada'],
            ];
        }

        $tipo->insumosDefault()->sync($sync);
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
                'message' => 'No se puede inhabilitar. Hay productos asociados a este tipo.',
            ], 422);
        }

        $tipoProducto->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto inhabilitado correctamente',
        ]);
    }

    /**
     * Restaurar tipo de producto inhabilitado
     */
    public function restore(int $id): JsonResponse
    {
        $tipoProducto = TipoProducto::onlyTrashed()->find($id);

        if (!$tipoProducto) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de producto no encontrado en historial.',
            ], 404);
        }

        $tipoProducto->restore();

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto restaurado correctamente',
            'tipo' => $tipoProducto,
        ]);
    }

    public function checkNombre(Request $request)
    {
        $nombre = $request->input('nombre');
        if (!$nombre)
            return response()->json(['exists' => false]);
        $exists = TipoProducto::where('nombre', $nombre)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function checkCodigoPrefijo(Request $request)
    {
        $codigo = $request->input('codigo');
        if (!$codigo)
            return response()->json(['exists' => false]);
        $exists = TipoProducto::where('prefijo', $codigo)->exists();
        return response()->json(['exists' => $exists]);
    }
}
