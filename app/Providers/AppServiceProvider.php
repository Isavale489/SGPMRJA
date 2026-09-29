<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
    }
}
