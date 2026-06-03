<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Actualizar tasa BCV: a partir de las 5:00 PM (hora Venezuela), cada hora
        // hasta las 10:00 PM. El BCV publica en la tarde; reintentar asegura tomar
        // la tasa del día aunque la API aún no la haya publicado a las 5:00 en punto.
        // El comando es idempotente (updateOrCreate por fecha), repetir no causa daño.
        $schedule->command('bcv:actualizar')
            ->hourlyAt(0)
            ->between('17:00', '22:00')
            ->timezone('America/Caracas')
            ->withoutOverlapping();

        // Marcar cotizaciones vencidas diariamente a medianoche
        $schedule->call(fn() => \App\Models\Cotizacion::actualizarCotizacionesVencidas())
            ->dailyAt('00:05')
            ->timezone('America/Caracas')
            ->name('cotizaciones:marcar-vencidas');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
