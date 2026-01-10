<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProveedorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Proveedor::create([
            'razon_social' => 'Textiles Caracas ',
            'ruc' => 'J-1231321',
            'direccion' => 'Av. Industrial 123, Venezuela',
            'telefono' => '0412-555666',
            'email' => 'ventas@textilesvenezuela.com',
            'contacto' => 'Juan Pérez',
            'telefono_contacto' => '0412-5231234',
            'estado' => true
        ]);

        \App\Models\Proveedor::create([
            'razon_social' => 'Insumos Textiles C.C.S',
            'ruc' => 'J-11112222',
            'direccion' => 'Torre Jalisco, Las Mercedes',
            'telefono' => '01-3214567',
            'email' => 'ventas@insumostextiles.com',
            'contacto' => 'María García',
            'telefono_contacto' => '0424890457',
            'estado' => true
        ]);
    }
}
