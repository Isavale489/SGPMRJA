<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\TasaCambio;
use App\Models\OrdenProduccion;
use App\Observers\OrdenProduccionObserver;
use App\Support\TasaBcvVigente;

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

        // Compartir tasa BCV con todas las vistas del admin
        View::composer('admin.*', function ($view) {
            $view->with('tasaBcv', TasaBcvVigente::obtener());
        });

        // Compartir el catálogo geográfico (estados + municipios) con el admin.
        // Fuente de verdad: tablas estado/municipio. Cacheado porque es estático.
        View::composer('admin.*', function ($view) {
            try {
                [$estadosVe, $mapaMunicipiosVe] = Cache::remember('catalogo_geografico_ve', now()->addDay(), function () {
                    $estados = \App\Models\Estado::with('municipios:id,estado_id,nombre')
                        ->orderBy('nombre')->get();
                    $mapa = [];
                    foreach ($estados as $e) {
                        $mapa[$e->nombre] = $e->municipios->pluck('nombre')->values()->all();
                    }
                    return [$estados->pluck('nombre')->values()->all(), $mapa];
                });
            } catch (\Exception $e) {
                $estadosVe = [];
                $mapaMunicipiosVe = [];
            }

            $view->with('estadosVe', $estadosVe)->with('mapaMunicipiosVe', $mapaMunicipiosVe);
        });

        // Compartir el catálogo de género de prenda (Dama/Caballero/Unisex) con
        // el admin. Set estable → cacheado. Lo consumen los wizards de
        // cotización/pedido para el cruce talla × género del configurador.
        View::composer('admin.*', function ($view) {
            try {
                $generosCatalogo = Cache::remember('catalogo_genero', now()->addDay(), function () {
                    return \App\Models\Genero::activo()
                        ->orderBy('orden')
                        ->get(['id', 'nombre', 'etiqueta', 'icono'])
                        ->toArray();
                });
            } catch (\Exception $e) {
                $generosCatalogo = [];
            }

            $view->with('generosCatalogo', $generosCatalogo);
        });
    }
}
