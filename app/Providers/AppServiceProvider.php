<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Http\Middleware\CerrarSesionesAlCambiarClave;
use Illuminate\Support\Facades\Schema;
use App\Models\OrdenProduccion;
use App\Observers\OrdenProduccionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Herencia de estatus Pedido → OP y bloqueo de OP bajo pedido cancelado.
        OrdenProduccion::observe(OrdenProduccionObserver::class);

        // Sesión cerrada porque la contraseña cambió en otro lado: al login (sin esto, un 401 vacío).
        CerrarSesionesAlCambiarClave::redirectUsing(fn () => route('login'));
    }
}
