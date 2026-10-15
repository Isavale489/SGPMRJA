<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\AuthenticateSession;

/**
 * Si la contraseña cambia (recuperación, reset del administrador, perfil), las
 * demás sesiones abiertas del usuario se cierran en su siguiente petición; la de
 * quien la cambia se actualiza y sigue abierta. Es AuthenticateSession de Laravel,
 * pero solo con el guard de sesión: con otro (p. ej. Sanctum en /api) no aplica.
 */
class CerrarSesionesAlCambiarClave extends AuthenticateSession
{
    public function handle($request, Closure $next)
    {
        if (! $this->auth->guard() instanceof SessionGuard) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }
}
