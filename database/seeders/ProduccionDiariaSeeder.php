<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProduccionDiariaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\ProduccionDiaria::create([
            'orden_id' => 1,
            'operario_id' => 2, // ID del Supervisor
            'cantidad_producida' => 30,
            'cantidad_defectuosa' => 2,
            'observaciones' => 'Producción normal'
        ]);

        \App\Models\ProduccionDiaria::create([
            'orden_id' => 1,
            'operario_id' => 2, // ID del Supervisor
            'cantidad_producida' => 20,
            'cantidad_defectuosa' => 1,
            'observaciones' => 'Retraso por mantenimiento de máquinas'
        ]);
    }
}
