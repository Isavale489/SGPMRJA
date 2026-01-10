<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DetalleOrdenInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\DetalleOrdenInsumo::create([
            'orden_produccion_id' => 1,
            'insumo_id' => 1,
            'cantidad_estimada' => 200,
            'cantidad_utilizada' => 100
        ]);

        \App\Models\DetalleOrdenInsumo::create([
            'orden_produccion_id' => 2,
            'insumo_id' => 2,
            'cantidad_estimada' => 300,
            'cantidad_utilizada' => 0
        ]);
    }
}
