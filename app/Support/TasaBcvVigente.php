<?php

namespace App\Support;

use App\Models\TasaCambio;
use App\Services\TasaBcvService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Tasa BCV (USD) vigente hoy, intentando capturar la publicación reciente si
 * la guardada quedó en un día anterior. Compartida por el layout Blade
 * (view composer 'admin.*') y por la plataforma Inertia (HandleInertiaRequests).
 */
class TasaBcvVigente
{
    public static function obtener(): ?TasaCambio
    {
        try {
            // Tasa VIGENTE hoy (techo fecha_bcv <= hoy; ignora tasas futuras).
            $tasaBcv = TasaCambio::obtenerTasaActual('USD');

            // Está desactualizada si no hay tasa vigente para hoy (la vigente
            // quedó en un día anterior), lo que indica que falta capturar la
            // publicación reciente del BCV.
            $hoy = Carbon::today()->toDateString();
            $necesitaActualizar = !$tasaBcv || Carbon::parse($tasaBcv->fecha_bcv)->toDateString() !== $hoy;

            // Usar cache para evitar múltiples llamadas a la API en la misma sesión
            if ($necesitaActualizar && !Cache::has('bcv_actualizado_hoy')) {
                try {
                    app(TasaBcvService::class)->actualizarTasas();

                    // Re-leer la VIGENTE: la tasa recién guardada puede estar
                    // fechada a mañana (vigencia = publicación + 1), así que no
                    // se usa directo para no mostrar una tasa futura antes de tiempo.
                    $tasaBcv = TasaCambio::obtenerTasaActual('USD');

                    // Marcar como actualizado por 1 hora para evitar múltiples intentos
                    Cache::put('bcv_actualizado_hoy', true, now()->addHour());
                } catch (\Exception $e) {
                    // Si falla la actualización, usar la tasa anterior
                }
            }

            return $tasaBcv;
        } catch (\Exception $e) {
            return null;
        }
    }
}
