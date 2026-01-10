<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Producto::create([
            'nombre' => 'Polo Clásico',
            'descripcion' => 'Polo de algodón pima con cuello redondo',
            'modelo' => 'PC-001',
            'color' => 'Negro',
            'talla' => 'M',
            'material' => 'Algodón Pima',
            'precio_base' => 29.90,
            'estado' => true
        ]);

        \App\Models\Producto::create([
            'nombre' => 'Camisa Ejecutiva',
            'descripcion' => 'Camisa manga larga para oficina',
            'modelo' => 'CE-001',
            'color' => 'Blanco',
            'talla' => 'L',
            'material' => 'Algodón Oxford',
            'precio_base' => 59.90,
            'estado' => true
        ]);
    }
}
