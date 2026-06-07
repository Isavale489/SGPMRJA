<?php

namespace App\Http\Controllers;

use App\Models\TipoProducto;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TipoProductoController extends Controller
{
    /**
     * Listar todos los tipos de producto
     */
    public function index(Request $request): JsonResponse
    {
        $query = TipoProducto::withCount(['productos', 'atributos', 'telas'])->orderBy('nombre');

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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,avif|max:10240',
            'atributos' => 'nullable|array',
            'atributos.*.id' => 'required_with:atributos|integer|exists:atributo,id',
            'atributos.*.orden' => 'required_with:atributos|integer|min:1|max:99',
            'insumos_default' => 'nullable|array',
            'insumos_default.*.id' => 'required_with:insumos_default|integer|exists:insumo,id',
            'insumos_default.*.cantidad_estimada' => 'required_with:insumos_default|numeric|min:0.01',
            'telas' => 'nullable|array',
            'telas.*' => 'integer|exists:insumo,id',
        ], [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.unique' => 'Ya existe un tipo con este nombre',
            'prefijo.required' => 'El prefijo de código es obligatorio',
            'prefijo.unique' => 'Ya existe un tipo con este prefijo',
            'prefijo.alpha' => 'El prefijo solo puede contener letras',
            'prefijo.max' => 'El prefijo no puede tener más de 5 caracteres',
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'Formato no permitido. Use JPG, PNG, GIF, WEBP, BMP o AVIF.',
            'imagen.max' => 'La imagen no puede superar 10MB.',
        ]);

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = $this->handleFileUpload($request->file('imagen'), null);
        }

        $tipo = TipoProducto::create([
            'nombre' => $request->nombre,
            'prefijo' => strtoupper($request->prefijo),
            'descripcion' => $request->descripcion,
            'imagen' => $imagenPath,
            'precio_confeccion' => $request->input('precio_confeccion', 0),
            'requiere_tela' => $request->boolean('requiere_tela', true),
            'consumo_tela_por_unidad' => $request->input('consumo_tela_por_unidad', 0),
        ]);

        $this->syncAtributos($tipo, $request->input('atributos', []));
        $this->syncInsumosDefault($tipo, $request->input('insumos_default', []));
        $this->syncTelas($tipo, $request->input('telas', []));

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto creado correctamente',
            'tipo' => $tipo->load(['atributos', 'insumosDefault', 'telas']),
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
            'telas',
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
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,bmp,avif|max:10240',
            'atributos' => 'nullable|array',
            'atributos.*.id' => 'required_with:atributos|integer|exists:atributo,id',
            'atributos.*.orden' => 'required_with:atributos|integer|min:1|max:99',
            'insumos_default' => 'nullable|array',
            'insumos_default.*.id' => 'required_with:insumos_default|integer|exists:insumo,id',
            'insumos_default.*.cantidad_estimada' => 'required_with:insumos_default|numeric|min:0.01',
        ], [
            'imagen.image' => 'El archivo debe ser una imagen válida.',
            'imagen.mimes' => 'Formato no permitido. Use JPG, PNG, GIF, WEBP, BMP o AVIF.',
            'imagen.max' => 'La imagen no puede superar 10MB.',
        ]);

        $data = [
            'nombre' => $request->nombre,
            'prefijo' => strtoupper($request->prefijo),
            'descripcion' => $request->descripcion,
            'precio_confeccion' => $request->input('precio_confeccion', $tipoProducto->precio_confeccion),
            'requiere_tela' => $request->boolean('requiere_tela', $tipoProducto->requiere_tela),
            'consumo_tela_por_unidad' => $request->input('consumo_tela_por_unidad', $tipoProducto->consumo_tela_por_unidad),
        ];

        if ($request->hasFile('imagen')) {
            $data['imagen'] = $this->handleFileUpload($request->file('imagen'), $tipoProducto->imagen);
        }

        $tipoProducto->update($data);

        $this->syncAtributos($tipoProducto, $request->input('atributos', []));
        $this->syncInsumosDefault($tipoProducto, $request->input('insumos_default', []));
        $this->syncTelas($tipoProducto, $request->input('telas', []));

        return response()->json([
            'success' => true,
            'message' => 'Tipo de producto actualizado correctamente',
            'tipo' => $tipoProducto->load(['atributos', 'insumosDefault', 'telas']),
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
     * Sincroniza las telas permitidas del tipo (FEAT-003).
     * @param array<int> $telaIds  IDs de insumo con tipo='Tela'
     */
    private function syncTelas(TipoProducto $tipo, array $telaIds): void
    {
        $tipo->telas()->sync(array_map('intval', $telaIds));
    }

    /**
     * Sube la imagen del catálogo del tipo a public/productoimg/tipos y
     * elimina la anterior si existía. Devuelve la ruta relativa guardada.
     */
    private function handleFileUpload($file, ?string $oldPath): string
    {
        if ($oldPath && file_exists(public_path($oldPath))) {
            @unlink(public_path($oldPath));
        }
        $directory = 'productoimg/tipos';
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($directory), $filename);
        return $directory . '/' . $filename;
    }

    /**
     * Crea una tela (Insumo tipo='Tela') inline desde el selector de variante
     * de la cotización y la asigna a este tipo (FEAT-003). Réplica del alta de
     * insumo del maestro, con tipo fijado en 'Tela'.
     */
    public function storeTela(Request $request, TipoProducto $tipoProducto): JsonResponse
    {
        $request->validate([
            'nombre'           => 'required|string|max:100',
            'codigo'           => 'nullable|string|min:2|max:8|regex:/^[A-Z0-9]+$/|unique:insumo,codigo',
            'unidad_medida'    => 'required|in:Metro,Kg,Gramo,Unidad,Rollo,Cono,Docena',
            'is_inventoriable' => 'nullable|boolean',
            'costo_unitario'   => 'required|numeric|min:0.01',
            'stock_actual'     => 'nullable|numeric|min:0',
            'stock_minimo'     => 'nullable|numeric|min:0',
            'stock_maximo'     => 'nullable|numeric|min:0|gte:stock_minimo',
            'estado'           => 'nullable|boolean',
        ], [
            'codigo.regex'     => 'El código solo admite letras mayúsculas y números.',
            'codigo.unique'    => 'Ya existe un insumo con este código.',
            'stock_maximo.gte' => 'La existencia máxima no puede ser menor que la mínima.',
        ]);

        $inventoriable = $request->boolean('is_inventoriable', true);

        $insumo = Insumo::create([
            'nombre'           => $request->nombre,
            'codigo'           => $request->filled('codigo') ? strtoupper(trim($request->codigo)) : null,
            'tipo'             => 'Tela',
            'unidad_medida'    => $request->unidad_medida,
            'is_inventoriable' => $inventoriable,
            'costo_unitario'   => $request->costo_unitario,
            'stock_actual'     => $inventoriable ? $request->input('stock_actual', 0) : 0,
            'stock_minimo'     => $inventoriable ? $request->input('stock_minimo', 0) : 0,
            'stock_maximo'     => $inventoriable ? $request->input('stock_maximo', 0) : 0,
            'estado'           => $request->boolean('estado', true),
        ]);

        $tipoProducto->telas()->syncWithoutDetaching([$insumo->id]);

        return response()->json([
            'success' => true,
            'message' => 'Tela creada y asignada al tipo.',
            'tela'    => [
                'id'             => $insumo->id,
                'nombre'         => $insumo->nombre,
                'codigo'         => $insumo->codigo,
                'costo_unitario' => (float) $insumo->costo_unitario,
                'unidad_medida'  => $insumo->unidad_medida,
            ],
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
