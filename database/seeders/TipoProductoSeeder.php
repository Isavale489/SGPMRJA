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
            ['nombre' => 'Chemise', 'prefijo' => 'CHM', 'descripcion' => 'Camisas tipo polo con cuello'],
            ['nombre' => 'Franela', 'prefijo' => 'FRN', 'descripcion' => 'Franelas cuello redondo o V'],
            ['nombre' => 'Camisa', 'prefijo' => 'CAM', 'descripcion' => 'Camisas formales manga larga/corta'],
            ['nombre' => 'Pantalón', 'prefijo' => 'PNT', 'descripcion' => 'Pantalones de trabajo o formales'],
            ['nombre' => 'Chaqueta', 'prefijo' => 'CHQ', 'descripcion' => 'Chaquetas industriales o formales'],
            ['nombre' => 'Overol', 'prefijo' => 'OVR', 'descripcion' => 'Overoles y monos de trabajo'],
            ['nombre' => 'Uniforme Escolar', 'prefijo' => 'ESC', 'descripcion' => 'Prendas para uniformes escolares'],
            ['nombre' => 'Accesorio', 'prefijo' => 'ACC', 'descripcion' => 'Gorras, delantales, chalecos, etc.'],
        ];

        foreach ($tipos as $tipo) {
            TipoProducto::firstOrCreate(
                ['prefijo' => $tipo['prefijo']],
                $tipo
            );
        }
    }
}
