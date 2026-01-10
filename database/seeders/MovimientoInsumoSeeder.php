<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MovimientoInsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\MovimientoInsumo::create([
            'insumo_id' => 1,
            'tipo_movimiento' => 'Entrada',
            'cantidad' => 1000,
            'stock_anterior' => 0,
            'stock_nuevo' => 1000,
            'motivo' => 'Inventario inicial',
            'created_by' => 2 // ID del Supervisor
        ]);

        \App\Models\MovimientoInsumo::create([
            'insumo_id' => 2,
            'tipo_movimiento' => 'Entrada',
            'cantidad' => 5000,
            'stock_anterior' => 0,
            'stock_nuevo' => 5000,
            'motivo' => 'Compra inicial',
            'created_by' => 2 // ID del Supervisor
        ]);
    }
}
