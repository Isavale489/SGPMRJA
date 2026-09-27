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

        // Acceso al panel de seguridad (FEAT-005 / TASK-039): SOLO Administrador.
        // Deliberadamente NO se gobierna por la matriz dinámica (config/modulos.php)
        // para que nadie pueda otorgarse el panel a sí mismo (anti-escalada). El
        // admin pasa por Gate::before; cualquier otro rol cae aquí y se deniega.
        Gate::define('acceso-seguridad', fn ($user) => esUsuarioAdministrador($user));
    }
}
