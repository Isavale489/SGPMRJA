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
        // Actualizar tasa BCV a las 5:00 PM (hora Venezuela) según requerimiento
        $schedule->command('bcv:actualizar')->dailyAt('17:00')->timezone('America/Caracas');

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
