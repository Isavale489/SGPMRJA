<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /** Errores que se muestran con la página Inertia `Error` (el resto, como siempre). */
    private const CON_PAGINA = [403, 404, 419, 429, 500, 503];

    /**
     * Páginas de error en React (antes vistas Blade con el layout del tema).
     * JSON sigue igual. Con APP_DEBUG, los 500/503 muestran el detalle de Laravel.
     * La sesión vencida (419) vuelve atrás con un aviso en vez de una página.
     */
    public function render($request, Throwable $e): Response
    {
        $respuesta = parent::render($request, $e);
        $status = $respuesta->getStatusCode();

        if (($request->expectsJson() && ! $request->header('X-Inertia')) || ! in_array($status, self::CON_PAGINA, true)) {
            return $respuesta;
        }
        if ($status >= 500 && config('app.debug')) {
            return $respuesta;
        }
        // Sesión vencida. 303 para que un PUT/PATCH/DELETE vuelva como GET (el 419 del
        // CSRF salta antes de HandleInertiaRequests, que haría esa conversión).
        //  - Con usuario: vuelve atrás con el aviso (el panel lo muestra como aviso).
        //  - Formulario Blade sin usuario (login o recuperación abiertos mucho rato):
        //    vuelve con un token nuevo, el correo escrito (lista blanca: nunca
        //    contraseñas ni respuestas de seguridad) y el aviso en `aviso`, que
        //    muestra el layout de acceso.
        //  - Visita Inertia sin usuario: back() llevaría al login y el aviso se perdería.
        if ($status === 419 && $request->hasSession()) {
            if ($request->user()) {
                return back(303)->with('error', 'La sesión expiró. Vuelve a intentarlo.');
            }
            if (! $request->header('X-Inertia')) {
                return back(303)->withInput($request->only(['email', 'name', 'remember']))->with('aviso', 'La sesión expiró. Vuelve a intentarlo.');
            }
        }

        // Una ruta que no existe no pasa por HandleInertiaRequests: fijar la plantilla raíz aquí.
        Inertia::setRootView('inertia');

        // Si la propia página falla (BD caída en las props compartidas, manifest de
        // Vite), queda la respuesta estándar de Laravel en vez de un 500 en blanco.
        try {
            return Inertia::render('Error', ['status' => $status])->toResponse($request)->setStatusCode($status);
        } catch (Throwable $fallo) {
            report($fallo);

            return $respuesta;
        }
    }
}
