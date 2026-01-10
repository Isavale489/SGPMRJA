<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdminRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado y tiene el rol de Administrativa
        if (Auth::check() && Auth::user()->role === 'Administrativa') {
            return $next($request);
        }
        
        // Si no tiene el rol adecuado, redirigir al dashboard con un mensaje de error
        return redirect()->route('dashboard')->with('error', 'No tienes permisos para acceder a esta sección.');
    }
}
