<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Exception as DoctrineException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\TasaCambio;
use App\Models\OrdenProduccion;
use App\Observers\OrdenProduccionObserver;
use App\Services\TasaBcvService;
use Carbon\Carbon;

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

        try {
            Type::addType('enum', 'Doctrine\DBAL\Types\StringType');
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        } catch (\Exception $e) {
            // Silenciar si Doctrine no está disponible o el tipo ya existe
        }

        // Compartir tasa BCV con todas las vistas del admin
        View::composer('admin.*', function ($view) {
            try {
                $tasaBcv = TasaCambio::obtenerTasaActual('USD');

                // Verificar si la tasa está desactualizada (no es de hoy)
                $hoy = Carbon::today()->toDateString();
                $necesitaActualizar = !$tasaBcv || Carbon::parse($tasaBcv->fecha_bcv)->toDateString() !== $hoy;

                // Usar cache para evitar múltiples llamadas a la API en la misma sesión
                if ($necesitaActualizar && !Cache::has('bcv_actualizado_hoy')) {
                    try {
                        $service = app(TasaBcvService::class);
                        $resultado = $service->actualizarTasas();

                        if ($resultado['success']) {
                            $tasaBcv = $resultado['tasa'];
                        }

                        // Marcar como actualizado por 1 hora para evitar múltiples intentos
                        Cache::put('bcv_actualizado_hoy', true, now()->addHour());
                    } catch (\Exception $e) {
                        // Si falla la actualización, usar la tasa anterior
                    }
                }

                $view->with('tasaBcv', $tasaBcv);
            } catch (\Exception $e) {
                $view->with('tasaBcv', null);
            }
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
