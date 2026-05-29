<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Cierra la sesión de un usuario que fue inhabilitado (estado=0) mientras
     * tenía sesión activa. El login ya bloquea a los inactivos (LoginRequest);
     * esto cubre la inhabilitación "en caliente" en el siguiente request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->estado) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta ha sido inhabilitada. Contacta al administrador.']);
        }

        return $next($request);
    }
}
