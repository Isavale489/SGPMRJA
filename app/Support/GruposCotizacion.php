<?php

namespace App\Support;

use App\Models\Cotizacion;
use App\Models\DetalleCotizacion;
use Illuminate\Support\Collection;

/**
 * Agrupa las líneas de una cotización como las muestra la interfaz: una fila
 * por variante (tipo + tela + atributos, o producto legacy) + color + precio
 * + bordados, con sus tallas × género. Cada talla × género es una línea
 * (`detalle_cotizacion`) en la BD.
 *
 * La vista Blade agrupaba por `producto_id` + color: las líneas dinámicas
 * (sin `producto_id`) de variantes distintas del mismo color se mezclaban.
 *
 * Relaciones que deben venir cargadas: producto.tipoProducto, tipoProducto
 * (con atributos.valores), color, talla, genero, bordados.logo.
 */
class GruposCotizacion
{
    /** @param  Collection<int, DetalleCotizacion|\App\Models\DetallePedido>  $detalles  (las líneas del pedido tienen las mismas relaciones) */
    public static function desde(Collection $detalles): array
    {
        $grupos = [];
        foreach ($detalles as $d) {
            $bordados = $d->bordados->map(fn ($b) => [
                'ubicacion_bordado_id' => $b->ubicacion_bordado_id,
                'nombre_aplicado' => $b->nombre_aplicado,
                'logo_id' => $b->logo_id,
                'logo' => $b->logo?->name ?? ($b->nombre_logo_aplicado ?: null),
                'es_personalizada' => (bool) $b->es_personalizada,
                'precio_aplicado' => (float) $b->precio_aplicado,
                'cantidad' => max(1, (int) $b->cantidad),
            ])->values()->all();
            $recargo = round(array_sum(array_map(fn ($b) => $b['precio_aplicado'] * $b['cantidad'], $bordados)), 2);
            $precio = (float) $d->precio_unitario;

            $tela = is_array($d->tela_snapshot) ? $d->tela_snapshot : null;
            $atributos = is_array($d->atributos_snapshot) ? $d->atributos_snapshot : [];
            ksort($atributos);

            $clave = implode('|', [
                $d->producto_id ? 'p'.$d->producto_id : 't'.$d->tipo_producto_id,
                $tela['id'] ?? '',
                json_encode($atributos),
                $d->color_id ?? '',
                number_format($precio, 2, '.', ''),
                md5(json_encode(array_map(fn ($b) => array_diff_key($b, ['logo' => 1]), $bordados))),
            ]);

            if (! isset($grupos[$clave])) {
                $producto = $d->producto;
                $tipo = $d->tipoProducto ?? $producto?->tipoProducto;
                $variante = array_values(array_filter([
                    $tela['nombre'] ?? $producto?->tela?->nombre,
                    ...array_map(fn ($a, $v) => "{$a}: {$v}", array_keys($atributos), $atributos),
                ]));
                $grupos[$clave] = [
                    'clave' => md5($clave),
                    'producto_id' => $d->producto_id,
                    'tipo_producto_id' => $d->tipo_producto_id ?? $producto?->tipo_producto_id,
                    'insumo_tela_id' => $tela['id'] ?? null,
                    'atributo_valor_ids' => ! $d->producto_id && $d->tipoProducto ? $d->tipoProducto->valorIdsDesdeSnapshot($d->atributos_snapshot) : [],
                    'nombre' => $tipo?->nombre ?? ($producto?->nombre_completo ?: 'Producto'),
                    'codigo' => $producto?->codigo ?? $d->sku_snapshot,
                    'variante' => implode(' · ', $variante),
                    'imagen' => $producto?->imagen ? asset($producto->imagen) : $tipo?->imagen_url,
                    'color' => $d->color ? ['id' => $d->color->id, 'nombre' => $d->color->nombre, 'hex' => $d->color->hex_referencial] : null,
                    'precio_base' => round(max(0, $precio - $recargo), 2),
                    // Precio de catálogo de la variante materializada (el del resolver); en las
                    // dinámicas lo calcula el resolver con la tela y los atributos (null aquí).
                    'precio_catalogo' => $producto ? round((float) $producto->precio_base, 2) : null,
                    'recargo' => $recargo,
                    'precio_unitario' => $precio,
                    'bordados' => $bordados,
                    'tallas' => [],
                    'unidades' => 0,
                    'subtotal' => 0.0,
                ];
            }

            $grupos[$clave]['tallas'][] = [
                'talla_id' => $d->talla_id,
                'talla' => $d->talla?->etiqueta ?: ($d->talla?->nombre ?? '—'),
                'genero_id' => $d->genero_id,
                'genero' => $d->genero?->etiqueta ?: $d->genero?->nombre,
                'cantidad' => (int) $d->cantidad,
                'descripcion' => $d->descripcion,
            ];
            $grupos[$clave]['unidades'] += (int) $d->cantidad;
            $grupos[$clave]['subtotal'] = round($grupos[$clave]['subtotal'] + $precio * (int) $d->cantidad, 2);
        }

        return array_values($grupos);
    }

    /**
     * Carga lo que necesita desde() (productos legacy inhabilitados incluidos),
     * en una cotización o en un pedido.
     *
     * @template T of Cotizacion|\App\Models\Pedido
     * @param  T  $documento
     * @return T
     */
    public static function cargar(Cotizacion|\App\Models\Pedido $documento): Cotizacion|\App\Models\Pedido
    {
        return $documento->load([
            'productos.producto' => fn ($q) => $q->withTrashed()->with(['tipoProducto', 'tela']),
            'productos.tipoProducto' => fn ($q) => $q->withTrashed()->with('atributos.valores'),
            'productos.color' => fn ($q) => $q->withTrashed(),
            'productos.talla' => fn ($q) => $q->withTrashed(),
            'productos.genero' => fn ($q) => $q->withTrashed(),
            'productos.bordados.logo' => fn ($q) => $q->withTrashed(),
        ]);
    }
}
