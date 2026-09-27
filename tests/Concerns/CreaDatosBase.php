<?php

namespace Tests\Concerns;

use App\Models\Cliente;
use App\Models\Genero;
use App\Models\Insumo;
use App\Models\Talla;
use App\Models\TipoProducto;
use App\Models\Persona;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\User;

/**
 * Fixtures mínimas para los tests de flujos de negocio.
 *
 * Los tests de flujo verifican EFECTOS EN LA BD (estados, stock, movimientos),
 * no el formato de la respuesta: así siguen siendo válidos cuando un controller
 * pase de `response()->json()` a `Inertia::render()` / redirect.
 */
trait CreaDatosBase
{
    protected function admin(): User
    {
        return User::factory()->create();
    }

    /** Usuario con un rol administrable SIN permisos en permiso_rol. */
    protected function usuarioSinPermisos(): User
    {
        $rol = Rol::create(['nombre' => 'Rol Sin Permisos', 'es_sistema' => false]);

        return User::factory()->create(['role_id' => $rol->id]);
    }

    protected function proveedor(): Proveedor
    {
        $persona = Persona::create([
            'nombre' => 'Textiles del Llano C.A.',
            'tipo_documento' => 'J-',
            'documento_identidad' => (string) fake()->unique()->numberBetween(10000000, 99999999),
        ]);

        return Proveedor::create([
            'tipo_proveedor' => 'juridico',
            'persona_id' => $persona->id,
            'estado' => 1,
        ]);
    }

    protected function cliente(): Cliente
    {
        $persona = Persona::create([
            'nombre' => 'María González',
            'tipo_documento' => 'V-',
            'documento_identidad' => (string) fake()->unique()->numberBetween(1000000, 30000000),
        ]);

        return Cliente::forceCreate(['persona_id' => $persona->id, 'tipo_cliente' => 'natural', 'estatus' => 1]);
    }

    /**
     * Payload mínimo válido para POST /cotizaciones: una línea de variante
     * dinámica (tipo de producto, sin fila `producto` materializada).
     */
    protected function payloadCotizacion(int $clienteId, int $cantidad = 12): array
    {
        $tipo = TipoProducto::forceCreate(['nombre' => 'Chemise', 'prefijo' => 'CHE']);
        $talla = Talla::forceCreate(['nombre' => 'M']);

        return [
            'cliente_id' => $clienteId,
            'fecha_cotizacion' => now()->toDateString(),
            'fecha_validez' => now()->addDays(15)->toDateString(),
            'productos' => [[
                'tipo_producto_id' => $tipo->id,
                'talla_id' => $talla->id,
                'genero_id' => Genero::query()->value('id'),
                'cantidad' => $cantidad,
                'lleva_bordado' => false,
            ]],
        ];
    }

    protected function insumo(array $attrs = []): Insumo
    {
        return Insumo::create(array_merge([
            'nombre' => 'Hilo ' . fake()->unique()->word(),
            'codigo' => strtoupper(fake()->unique()->bothify('INS-####')),
            'tipo' => 'Hilo',
            'unidad_medida' => 'unidad',
            'is_inventoriable' => 1,
            'costo_unitario' => 1,
            'stock_actual' => 0,
            'stock_minimo' => 0,
            'estado' => 1,
        ], $attrs));
    }

    /**
     * Éxito sin acoplarse al formato: vale 2xx (JSON hoy) o 3xx (redirect
     * de Inertia mañana), y nunca un 4xx/5xx.
     */
    protected function assertExito($response): void
    {
        $status = $response->getStatusCode();
        $this->assertTrue($status >= 200 && $status < 400, "Se esperaba éxito y llegó HTTP {$status}: " . mb_substr((string) $response->getContent(), 0, 400));
    }
}
