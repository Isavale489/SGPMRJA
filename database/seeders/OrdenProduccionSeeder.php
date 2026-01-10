<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrdenProduccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\OrdenProduccion::create([
            'producto_id' => 1,
            'cantidad_solicitada' => 100,
            'cantidad_producida' => 50,
            'fecha_inicio' => '2025-03-01',
            'fecha_fin_estimada' => '2025-03-15',
            'estado' => 'En Proceso',
            'costo_estimado' => 2000.00,
            'notas' => 'Producción regular de polos',
            'created_by' => 1
        ]);

        \App\Models\OrdenProduccion::create([
            'producto_id' => 2,
            'cantidad_solicitada' => 50,
            'cantidad_producida' => 0,
            'fecha_inicio' => '2025-03-05',
            'fecha_fin_estimada' => '2025-03-20',
            'estado' => 'Pendiente',
            'costo_estimado' => 1500.00,
            'notas' => 'Orden especial de camisas',
            'created_by' => 1
        ]);
    }
}
