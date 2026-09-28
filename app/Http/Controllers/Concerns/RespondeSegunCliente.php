<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Respuesta de las mutaciones cuando un mismo endpoint lo usan una página
 * Inertia y un módulo Blade (jQuery) que todavía no se migró.
 *
 *   Inertia → redirect back con mensaje flash (success / error).
 *   jQuery  → el JSON de siempre (su contrato lo fija un test de caracterización).
 *
 * OJO: las peticiones de Inertia también llevan X-Requested-With, así que
 * `$request->ajax()` NO distingue: hay que mirar `esInertia()`.
 */
trait RespondeSegunCliente
{
    protected function esInertia(Request $request): bool
    {
        return (bool) $request->header('X-Inertia');
    }

    /**
     * Éxito. `$json` es el cuerpo completo que espera el cliente jQuery.
     * `$creado` viaja a Inertia como flash de un solo uso (`page.flash`): lo lee
     * un alta rápida que necesita el registro nuevo (p. ej. el proveedor recién
     * creado desde el formulario de Compras).
     */
    protected function responder(Request $request, string $mensaje, array $json = [], array $creado = []): JsonResponse|RedirectResponse
    {
        if (! $this->esInertia($request)) {
            return response()->json($json ?: ['success' => true, 'message' => $mensaje]);
        }
        if ($creado) {
            Inertia::flash($creado);
        }

        return back()->with('success', $mensaje);
    }

    /** Regla de negocio que impide la acción (p. ej. inhabilitar con dependientes). */
    protected function rechazar(Request $request, string $mensaje, int $status = 422): JsonResponse|RedirectResponse
    {
        return $this->esInertia($request)
            ? back()->with('error', $mensaje)
            : response()->json(['success' => false, 'message' => $mensaje], $status);
    }
}
