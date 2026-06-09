<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    /**
     * Búsqueda unificada de personas por documento.
     * Retorna todos los roles activos que tiene cada persona en el sistema
     * (cliente, empleado, proveedor) para autocompletar formularios sin
     * duplicar registros.
     *
     * Solo considera registros activos:
     * - Cliente: estatus = 1 y no soft-deleted
     * - Empleado: no soft-deleted
     * - Proveedor: estado = 1 y no soft-deleted
     */
    public function search(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (mb_strlen($query) < 6) {
            return response()->json([]);
        }

        $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);

        $personas = Persona::with([
                'cliente'   => fn ($q) => $q->where('estatus', 1)
                    ->withCount('cotizaciones')
                    ->withMax('cotizaciones', 'fecha_cotizacion'),
                'empleado',
                'proveedor' => fn ($q) => $q->where('estado', 1)
                    ->withCount('compras')
                    ->withMax('compras', 'fecha_compra'),
                'telefonos',
                'direcciones',
            ])
            ->where('documento_identidad', 'LIKE', "{$escaped}%")
            ->limit(10)
            ->get();

        $resultado = $personas->map(function (Persona $persona) {
            $roles = [];
            if ($persona->cliente)   $roles[] = 'cliente';
            if ($persona->empleado)  $roles[] = 'empleado';
            if ($persona->proveedor) {
                $roles[] = $persona->proveedor->tipo_proveedor === 'natural'
                    ? 'proveedor_natural'
                    : 'proveedor_juridico';
            }

            // Si no tiene ningún rol activo (todos inhabilitados), no la mostramos
            if (empty($roles)) {
                return null;
            }

            $direccion = $persona->direccionPrincipal;

            return [
                'persona_id'      => $persona->id,
                'cliente_id'      => $persona->cliente?->id,
                'tipo_cliente'    => $persona->cliente?->tipo_cliente,
                'proveedor_id'    => $persona->proveedor?->id,
                'proveedor_tipo'  => $persona->proveedor?->tipo_proveedor,
                'documento'       => $persona->documento_completo,
                'tipo_documento'  => $persona->tipo_documento,
                'documento_num'   => $persona->documento_identidad,
                'nombre'          => $persona->nombre,
                'apellido'        => $persona->apellido ?? '',
                'razon_social'    => $persona->proveedor?->tipo_proveedor === 'juridico'
                    ? ($persona->proveedor->razon_social ?? null)
                    : null,
                'email'           => $persona->email ?? '',
                'telefono'        => $persona->telefonoPrincipal ?? '',
                'direccion'       => $direccion?->direccion ?? '',
                'estado'          => $direccion?->estado ?? ($persona->estado_geografico ?? ''),
                'ciudad'          => $direccion?->ciudad ?? '',
                'roles'           => $roles,
                // Mini-stats del cliente (null si la persona aún no es cliente)
                'cotizaciones_count'    => $persona->cliente?->cotizaciones_count ?? null,
                'cotizaciones_last_date' => $persona->cliente?->cotizaciones_max_fecha_cotizacion ?? null,
                // Mini-stats del proveedor (null si la persona aún no es proveedor)
                'compras_count'    => $persona->proveedor?->compras_count ?? null,
                'compras_last_date' => $persona->proveedor?->compras_max_fecha_compra ?? null,
            ];
        })->filter()->values();

        return response()->json($resultado);
    }
}
