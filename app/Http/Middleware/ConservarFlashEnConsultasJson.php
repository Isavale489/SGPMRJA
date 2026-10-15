<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Un mensaje flash (redirect()->with(...), errores de validación, Inertia::flash)
 * vale para «la siguiente petición». Si entre el redirect y la página que lo
 * muestra llega una consulta JSON en segundo plano (la proyección de insumos, la
 * tasa BCV, un buscador), Laravel la cuenta como «la siguiente» y el mensaje se
 * pierde. Estas consultas no muestran flash, así que lo dejan para la próxima.
 *
 * Límites conocidos: (1) un sondeo JSON repetido mantiene vivo un flash pendiente
 * hasta que se cargue una página (que lo muestra y lo consume); (2) Laravel no
 * bloquea la sesión, así que una consulta que termine DESPUÉS de la página que ya
 * mostró el aviso podría hacerlo reaparecer una vez en la navegación siguiente.
 * Ambos son preferibles a perder el aviso (lo que pasaba antes).
 */
class ConservarFlashEnConsultasJson
{
    public function handle(Request $request, Closure $next): Response
    {
        $respuesta = $next($request);

        if ($request->hasSession() && $request->expectsJson() && ! $request->header('X-Inertia')) {
            $request->session()->reflash();
        }

        return $respuesta;
    }
}
