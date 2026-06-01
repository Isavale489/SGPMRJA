<?php

namespace App\Services;

use App\Models\AtributoValor;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\TipoProducto;
use Illuminate\Support\Facades\DB;

/**
 * Lógica de creación, actualización y composición de productos (variantes).
 *
 * Centraliza la generación del SKU determinístico, el cálculo del precio
 * sugerido y la persistencia coherente de la asociación tipo↔tela↔atributos.
 */
class ProductoService
{
    /**
     * Genera el SKU determinístico para una combinación tipo + tela + valores de atributos.
     *
     * Formato: PREFIJO[-TELA][-ATR1[-ATR2...]]-NNN
     *   - PREFIJO: tipo_producto.prefijo
     *   - TELA: insumo.codigo (omitido si tipo no requiere tela y no se asignó)
     *   - ATRn: atributo_valor.codigo en el orden definido por tipo_producto_atributo.orden
     *   - NNN: secuencial 001+ por combinación, con withTrashed() y loop defensivo.
     */
    public function generarCodigo(TipoProducto $tipo, ?Insumo $tela, array $valoresOrdenados): string
    {
        $partes = [$tipo->prefijo];

        if ($tela && $tela->codigo) {
            $partes[] = $tela->codigo;
        }

        foreach ($valoresOrdenados as $valor) {
            $partes[] = $valor->codigo;
        }

        $base = implode('-', $partes);

        // Loop defensivo: contar y verificar para tolerar race conditions.
        $intentos = 0;
        do {
            $count = Producto::withTrashed()
                ->where('codigo', 'LIKE', $base . '-%')
                ->count();
            $candidato = $base . '-' . str_pad($count + 1 + $intentos, 3, '0', STR_PAD_LEFT);
            $intentos++;
        } while (
            $intentos < 100 &&
            Producto::withTrashed()->where('codigo', $candidato)->exists()
        );

        return $candidato;
    }

    /**
     * Suma el costo de la tela y el precio de confección del tipo. El admin
     * recibe esto como sugerencia y puede sobreescribirlo libremente.
     */
    public function sugerirPrecio(TipoProducto $tipo, ?Insumo $tela): float
    {
        $costoTela = $tela ? (float) $tela->costo_unitario : 0;
        $confeccion = (float) ($tipo->precio_confeccion ?? 0);
        return round($costoTela + $confeccion, 2);
    }

    /**
     * Carga los AtributoValor seleccionados, ordenados según el orden que
     * tipo_producto_atributo define para cada atributo padre.
     *
     * @param  TipoProducto $tipo
     * @param  array<int>   $valoresIds  IDs de atributo_valor seleccionados
     * @return \Illuminate\Support\Collection<int, AtributoValor>
     */
    public function ordenarValoresParaTipo(TipoProducto $tipo, array $valoresIds)
    {
        if (empty($valoresIds)) {
            return collect();
        }

        $valores = AtributoValor::with('atributo')
            ->whereIn('id', $valoresIds)
            ->get();

        // Mapa atributo_id => orden definido por el tipo
        $ordenPorAtributo = $tipo->atributos()
            ->pluck('tipo_producto_atributo.orden', 'atributo.id');

        return $valores->sortBy(function ($valor) use ($ordenPorAtributo) {
            return $ordenPorAtributo[$valor->atributo_id] ?? 999;
        })->values();
    }

    /**
     * Construye el snapshot de atributos para guardar en producto.atributos_snapshot.
     * Estructura: ["Manga" => "Larga", "Cuello" => "V"] (formato display-friendly).
     */
    public function buildAtributosSnapshot($valoresOrdenados): array
    {
        $snapshot = [];
        foreach ($valoresOrdenados as $valor) {
            $snapshot[$valor->atributo->nombre] = $valor->nombre;
        }
        return $snapshot;
    }

    /**
     * Crea un producto completo (variante) con tela y atributos.
     *
     * @param  array $data  ['tipo_producto_id', 'insumo_tela_id'?, 'atributo_valor_ids' => [],
     *                       'descripcion'?, 'precio_base', 'estado'?]
     * @return Producto
     */
    public function crear(array $data): Producto
    {
        return DB::transaction(function () use ($data) {
            $tipo = TipoProducto::findOrFail($data['tipo_producto_id']);
            $tela = !empty($data['insumo_tela_id']) ? Insumo::find($data['insumo_tela_id']) : null;

            $valoresOrdenados = $this->ordenarValoresParaTipo(
                $tipo,
                $data['atributo_valor_ids'] ?? []
            );

            $producto = new Producto();
            $producto->tipo_producto_id   = $tipo->id;
            $producto->insumo_tela_id     = $tela?->id;
            $producto->codigo             = $this->generarCodigo($tipo, $tela, $valoresOrdenados->all());
            $producto->descripcion        = $data['descripcion'] ?? null;
            $producto->precio_base        = $data['precio_base'];
            $producto->atributos_snapshot = $this->buildAtributosSnapshot($valoresOrdenados);
            $producto->imagen             = $data['imagen'] ?? null;
            $producto->estado             = $data['estado'] ?? true;
            $producto->save();

            if ($valoresOrdenados->isNotEmpty()) {
                $producto->atributoValores()->sync($valoresOrdenados->pluck('id')->all());
            }

            return $producto;
        });
    }

    /**
     * Actualiza un producto existente. Si cambia el tipo, la tela o los atributos,
     * se regenera el SKU para mantener consistencia con la combinación nueva.
     */
    public function actualizar(Producto $producto, array $data): Producto
    {
        return DB::transaction(function () use ($producto, $data) {
            $tipoNuevo = TipoProducto::findOrFail($data['tipo_producto_id']);
            $telaNueva = !empty($data['insumo_tela_id']) ? Insumo::find($data['insumo_tela_id']) : null;

            $valoresIdsActuales = $producto->atributoValores->pluck('id')->sort()->values()->all();
            $valoresIdsNuevos   = collect($data['atributo_valor_ids'] ?? [])->sort()->values()->all();

            $cambioCombinacion =
                $producto->tipo_producto_id != $tipoNuevo->id ||
                $producto->insumo_tela_id != ($telaNueva?->id) ||
                $valoresIdsActuales != $valoresIdsNuevos;

            $valoresOrdenados = $this->ordenarValoresParaTipo($tipoNuevo, $data['atributo_valor_ids'] ?? []);

            $producto->tipo_producto_id   = $tipoNuevo->id;
            $producto->insumo_tela_id     = $telaNueva?->id;
            $producto->descripcion        = $data['descripcion'] ?? null;
            $producto->precio_base        = $data['precio_base'];
            $producto->atributos_snapshot = $this->buildAtributosSnapshot($valoresOrdenados);

            if (array_key_exists('imagen', $data) && $data['imagen']) {
                $producto->imagen = $data['imagen'];
            }
            if (array_key_exists('estado', $data)) {
                $producto->estado = $data['estado'];
            }

            if ($cambioCombinacion) {
                $producto->codigo = $this->generarCodigo($tipoNuevo, $telaNueva, $valoresOrdenados->all());
            }

            $producto->save();

            $producto->atributoValores()->sync($valoresOrdenados->pluck('id')->all());

            return $producto;
        });
    }

    /**
     * Vista previa del próximo SKU sin persistir nada.
     */
    public function previsualizarCodigo(TipoProducto $tipo, ?Insumo $tela, array $valoresIds): string
    {
        $valoresOrdenados = $this->ordenarValoresParaTipo($tipo, $valoresIds);
        return $this->generarCodigo($tipo, $tela, $valoresOrdenados->all());
    }

    /**
     * Construye los snapshots inmutables (tela + atributos) que se almacenan
     * en cada renglón de cotización/pedido para preservar el estado del
     * catálogo en el momento exacto del registro.
     *
     * @return array{tela_snapshot: ?array, atributos_snapshot: ?array}
     */
    public function buildSnapshotsParaDetalle(Producto $producto): array
    {
        if (!$producto->relationLoaded('tela')) {
            $producto->load('tela');
        }

        $telaSnapshot = null;
        if ($producto->tela) {
            $telaSnapshot = [
                'id'             => $producto->tela->id,
                'nombre'         => $producto->tela->nombre,
                'codigo'         => $producto->tela->codigo,
                'costo_unitario' => (float) $producto->tela->costo_unitario,
                'unidad_medida'  => $producto->tela->unidad_medida,
                'snapshot_at'    => now()->toDateString(),
            ];
        }

        return [
            'tela_snapshot'      => $telaSnapshot,
            'atributos_snapshot' => $producto->atributos_snapshot ?: null,
        ];
    }
}
