<?php

namespace App\Console\Commands;

use App\Services\TasaBcvService;
use Illuminate\Console\Command;

class ActualizarTasaBcv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bcv:actualizar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza la tasa del dólar BCV desde las APIs externas';

    /**
     * Execute the console command.
     */
    public function handle(TasaBcvService $service): int
    {
        $this->info('Consultando tasas BCV...');

        $resultado = $service->actualizarTasas();

        if ($resultado['success']) {
            $tasa = $resultado['tasa'];
            $this->info('✓ Tasa actualizada correctamente');
            $this->table(
                ['Moneda', 'Valor', 'Fecha BCV', 'Fuente'],
                [[$tasa->moneda, $tasa->valor, $tasa->fecha_bcv->format('d/m/Y'), $tasa->fuente]]
            );
            return Command::SUCCESS;
        }

        $this->error('✗ Error: ' . $resultado['message']);
        return Command::FAILURE;
    }
}
