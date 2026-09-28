<?php

namespace App\Http\Controllers\Auth;

use App\Rules\ContrasenaSegura;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ForcePasswordChangeController extends Controller
{
    /**
     * Pantalla para que el usuario cambie la contraseña temporal
     * que le proporcionó el admin tras un reset.
     */
    public function show(Request $request): View|RedirectResponse
    {
        if (!$request->user() || !$request->user()->password_reset_by_admin) {
            return redirect()->route('dashboard');
        }
        return view('auth.force-password-change');
    }

    /**
     * Procesa el cambio. La contraseña temporal sigue siendo válida hasta
     * que el usuario establezca una nueva.
     */
    public function process(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (!$user || !$user->password_reset_by_admin) {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', 'different:current_password', new ContrasenaSegura],
        ], [
            'current_password.current_password' => 'La contraseña temporal no es correcta.',
            'password.different'                => 'La nueva contraseña debe ser distinta a la temporal.',
        ]);

        $user->forceFill([
            'password'                => Hash::make($request->password),
            'remember_token'          => Str::random(60),
            'password_reset_by_admin' => false,
        ])->save();

        return redirect()->route('profile.edit')
            ->with('status', 'password-updated')
            ->with('warning_recovery', 'Contraseña actualizada. Ahora configura tus preguntas de seguridad.');
    }
}
