<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoProducto;

class TipoProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tipos = [
            ['nombre' => 'Chemise', 'codigo_prefijo' => 'CHM', 'descripcion' => 'Camisas tipo polo con cuello'],
            ['nombre' => 'Franela', 'codigo_prefijo' => 'FRN', 'descripcion' => 'Franelas cuello redondo o V'],
            ['nombre' => 'Camisa', 'codigo_prefijo' => 'CAM', 'descripcion' => 'Camisas formales manga larga/corta'],
            ['nombre' => 'Pantalón', 'codigo_prefijo' => 'PNT', 'descripcion' => 'Pantalones de trabajo o formales'],
            ['nombre' => 'Chaqueta', 'codigo_prefijo' => 'CHQ', 'descripcion' => 'Chaquetas industriales o formales'],
            ['nombre' => 'Overol', 'codigo_prefijo' => 'OVR', 'descripcion' => 'Overoles y monos de trabajo'],
            ['nombre' => 'Uniforme Escolar', 'codigo_prefijo' => 'ESC', 'descripcion' => 'Prendas para uniformes escolares'],
            ['nombre' => 'Accesorio', 'codigo_prefijo' => 'ACC', 'descripcion' => 'Gorras, delantales, chalecos, etc.'],
        ];

        foreach ($tipos as $tipo) {
            TipoProducto::firstOrCreate(
                ['codigo_prefijo' => $tipo['codigo_prefijo']],
                $tipo
            );
        }
    }
}
