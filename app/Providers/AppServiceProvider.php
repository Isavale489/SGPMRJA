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

        try {
            Type::addType('enum', 'Doctrine\DBAL\Types\StringType');
            DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
        } catch (DoctrineException $e) {
            // Manejar la excepción si el tipo ya está registrado
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
    }
}
