<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondeSegunCliente;
use App\Http\Requests\GuardarTipoProductoRequest;
use App\Models\TipoProducto;
use App\Models\Insumo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TipoProductoController extends Controller
{
    use RespondeSegunCliente;

    /**
     * Guardar nuevo tipo de producto
     */
    public function store(GuardarTipoProductoRequest $request)
    {
        $datos = $request->validated();
        $tipo = TipoProducto::create([
            'nombre' => $datos['nombre'],
            'prefijo' => $datos['prefijo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'imagen' => $request->hasFile('imagen') ? $this->handleFileUpload($request->file('imagen'), null) : null,
            'precio_confeccion' => $datos['precio_confeccion'] ?? 0,
            'requiere_tela' => $request->boolean('requiere_tela', true),
            'requiere_produccion' => $request->boolean('requiere_produccion', true),
            'consumo_tela_por_unidad' => $datos['consumo_tela_por_unidad'] ?? 0,
        ]);

        $this->syncAtributos($tipo, $datos['atributos'] ?? []);
        $this->syncInsumosDefault($tipo, $datos['insumos_default'] ?? []);
        $this->syncTelas($tipo, $datos['telas'] ?? []);

        return $this->responder($request, 'Tipo de producto creado correctamente.', [
            'success' => true, 'message' => 'Tipo de producto creado correctamente.', 'tipo' => $tipo->load(['atributos', 'insumosDefault', 'telas']),
        ]);
    }

    /**
     * Mostrar un tipo de producto
     */
    public function show($id): JsonResponse
    {
        // withTrashed: permite ver el detalle de un tipo inhabilitado desde el historial.
        $tipoProducto = TipoProducto::withTrashed()->findOrFail($id);
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
    public function update(GuardarTipoProductoRequest $request, TipoProducto $tipoProducto)
    {
        $datos = $request->validated();
        // Sin 'prefijo': inmutable tras crear el tipo (forma parte del SKU).
        $data = [
            'nombre' => $datos['nombre'],
            'descripcion' => $datos['descripcion'] ?? null,
            'precio_confeccion' => $datos['precio_confeccion'] ?? $tipoProducto->precio_confeccion,
            'requiere_tela' => $request->boolean('requiere_tela', $tipoProducto->requiere_tela),
            'requiere_produccion' => $request->boolean('requiere_produccion', $tipoProducto->requiere_produccion),
            'consumo_tela_por_unidad' => $datos['consumo_tela_por_unidad'] ?? $tipoProducto->consumo_tela_por_unidad,
        ];
        if ($request->hasFile('imagen')) {
            $data['imagen'] = $this->handleFileUpload($request->file('imagen'), $tipoProducto->imagen);
        }

        $tipoProducto->update($data);
        $this->syncAtributos($tipoProducto, $datos['atributos'] ?? []);
        $this->syncInsumosDefault($tipoProducto, $datos['insumos_default'] ?? []);
        $this->syncTelas($tipoProducto, $datos['telas'] ?? []);

        return $this->responder($request, 'Tipo de producto actualizado correctamente.', [
            'success' => true, 'message' => 'Tipo de producto actualizado correctamente.', 'tipo' => $tipoProducto->load(['atributos', 'insumosDefault', 'telas']),
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

    public function destroy(Request $request, TipoProducto $tipoProducto)
    {
        if ($tipoProducto->productos()->exists()) {
            return $this->rechazar($request, 'No se puede inhabilitar. Hay productos asociados a este tipo.');
        }

        $tipoProducto->delete();

        return $this->responder($request, 'Tipo de producto inhabilitado correctamente.');
    }

    public function restore(Request $request, int $id)
    {
        TipoProducto::onlyTrashed()->findOrFail($id)->restore();

        return $this->responder($request, 'Tipo de producto restaurado correctamente.');
    }
}
