<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Bypass total del Administrador (anti-lockout estructural, FEAT-005 spec 7).
        // Devuelve true SOLO para admin; en cualquier otro caso null (NUNCA false),
        // para dejar seguir la cadena de autorizacion (Gates/policies/permiso_rol).
        Gate::before(function ($user) {
            return esUsuarioAdministrador($user) ? true : null;
        });
    }
}
