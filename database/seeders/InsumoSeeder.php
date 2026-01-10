<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsumoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Insumo::create([
            'nombre' => 'Tela Algodón Pima',
            'tipo' => 'Tela',
            'unidad_medida' => 'Metro',
            'costo_unitario' => 15.50,
            'stock_actual' => 1000,
            'stock_minimo' => 200,
            'proveedor_id' => 1,
            'estado' => true
        ]);

        \App\Models\Insumo::create([
            'nombre' => 'Botón Nacar 18mm',
            'tipo' => 'Botón',
            'unidad_medida' => 'Unidad',
            'costo_unitario' => 0.50,
            'stock_actual' => 5000,
            'stock_minimo' => 1000,
            'proveedor_id' => 2,
            'estado' => true
        ]);
    }
}
