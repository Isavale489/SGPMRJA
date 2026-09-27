<?php

namespace App\Services;

use App\Models\Logo;

class BordadoPricingService
{
    /**
     * Normaliza el arreglo de bordados de una línea de producto.
     */
    public function normalizeBordados(array $item): array
    {
        $llevaBordado = !empty($item['lleva_bordado']) && (int) $item['lleva_bordado'] === 1;
        if (!$llevaBordado || empty($item['bordados']) || !is_array($item['bordados'])) {
            return [];
        }

        $bordados = [];
        foreach ($item['bordados'] as $index => $bordado) {
            $logoId = $bordado['logo_id'] ?? $item['logo_id'] ?? null;

            $bordados[] = [
                'ubicacion_bordado_id' => $bordado['ubicacion_bordado_id'] ?? null,
                'nombre_aplicado' => trim((string) ($bordado['nombre_aplicado'] ?? '')),
                'logo_id' => $logoId ? (int) $logoId : null,
                'es_personalizada' => (bool) ($bordado['es_personalizada'] ?? false),
                'cantidad' => max(1, (int) ($bordado['cantidad'] ?? 1)),
                'precio_aplicado' => (float) ($bordado['precio_aplicado'] ?? 0),
                'orden' => (int) $index,
            ];
        }

        return $bordados;
    }

    /**
     * Índices de los productos cuya cantidad total de bordados excede el máximo.
     *
     * La unidad de negocio es "bordados por prenda" = suma de `cantidad` de cada
     * línea (una ubicación con cantidad 10 son 10 bordados, no 1). Devuelve los
     * índices del arreglo `productos` que violan el tope, para construir errores
     * de validación apuntando al producto correcto.
     */
    public static function indicesQueExcedenMaximo(array $productos, int $max): array
    {
        $violan = [];

        foreach ($productos as $i => $prod) {
            $bordados = $prod['bordados'] ?? null;
            if (!is_array($bordados) || empty($bordados)) {
                continue;
            }

            $total = 0;
            foreach ($bordados as $bordado) {
                $total += max(1, (int) ($bordado['cantidad'] ?? 1));
            }

            if ($total > $max) {
                $violan[] = $i;
            }
        }

        return $violan;
    }

    /**
     * Calcula el recargo unitario total por bordado.
     */
    public function calcularRecargoBordadoUnitario(array $bordados): float
    {
        $recargo = 0;
        foreach ($bordados as $bordado) {
            $precio = (float) ($bordado['precio_aplicado'] ?? 0);
            $cantidad = max(1, (int) ($bordado['cantidad'] ?? 1));
            $recargo += ($precio * $cantidad);
        }

        return $recargo;
    }

    /**
     * Calcula el precio unitario final (base + recargo bordado).
     */
    public function calcularPrecioUnitarioFinal(float $precioBase, array $bordados): float
    {
        return $precioBase + $this->calcularRecargoBordadoUnitario($bordados);
    }

    /**
     * Resuelve el nombre del logo para snapshot (nombre_logo_aplicado).
     * Busca el nombre en el catálogo por logo_id.
     */
    public function resolverNombreLogoSnapshot(?int $logoId): string
    {
        if (!$logoId) {
            return '';
        }

        return Logo::where('id', $logoId)->value('name') ?? '';
    }
}
