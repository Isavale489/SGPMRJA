<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as RespuestaHttp;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'urls' => ['login' => route('login', absolute: false), 'recuperar' => route('recovery.method', absolute: false)],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse|RespuestaHttp
    {
        $request->authenticate();

        $request->session()->regenerate();

        $destino = redirect()->intended(RouteServiceProvider::HOME);

        // Desde la página Inertia del login: recarga completa. El destino guardado puede
        // no ser Inertia (un PDF, la web pública) y se vería dentro de un diálogo.
        return $request->header('X-Inertia') ? Inertia::location($destino->getTargetUrl()) : $destino;
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
