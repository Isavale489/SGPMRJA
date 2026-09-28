<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRecoveryQuestionsConfigured
{
    /**
     * Aplica reglas de seguridad post-login en este orden:
     *
     *  1. password_reset_by_admin → forzar cambio de contraseña.
     *  2. recovery_must_reset_questions o usuario sin las 3 preguntas
     *     configuradas (Opción A) → forzar configuración en /profile.
     *
     * Permite acceso a las rutas mínimas necesarias para resolver cada
     * estado y al logout, evitando bucles de redirección.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // 1) Cambio forzoso de contraseña (admin reseteó)
        if ($user->password_reset_by_admin) {
            $allowed = [
                'auth.force-password-change.show',
                'auth.force-password-change.process',
                'logout',
            ];
            if (in_array($routeName, $allowed, true)) {
                return $next($request);
            }
            return redirect()->route('auth.force-password-change.show')
                ->with('warning_force_password', 'Por seguridad, debes cambiar la contraseña temporal antes de continuar.');
        }

        // 2) Configuración forzosa de preguntas de seguridad
        $needsQuestions = $user->recovery_must_reset_questions
                       || !$user->hasRecoveryQuestionsConfigured();

        if ($needsQuestions) {
            $allowed = [
                'profile.edit',
                'profile.update',
                'profile.recovery-questions.update',
                'profile.avatar.update', // la página la ofrece: sin esto la subida se perdía en silencio
                'logout',
            ];
            if (in_array($routeName, $allowed, true)) {
                return $next($request);
            }
            $msg = $user->recovery_must_reset_questions
                ? 'Por seguridad, debes reconfigurar tus preguntas de seguridad antes de continuar.'
                : 'Antes de continuar, debes configurar tus preguntas de seguridad para poder recuperar tu contraseña sin internet.';
            return redirect()->route('profile.edit')->with('warning_recovery', $msg);
        }

        return $next($request);
    }
}
