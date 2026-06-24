<?php

namespace Database\Seeders;

use App\Models\Genero;
use Illuminate\Database\Seeder;

class GeneroSeeder extends Seeder
{
    public function run(): void
    {
        $generos = [
            ['nombre' => 'Dama',      'etiqueta' => 'Dama',      'icono' => 'ri-women-line', 'orden' => 1],
            ['nombre' => 'Caballero', 'etiqueta' => 'Caballero', 'icono' => 'ri-men-line',   'orden' => 2],
            ['nombre' => 'Unisex',    'etiqueta' => 'Unisex',    'icono' => 'ri-group-line', 'orden' => 3],
        ];

        foreach ($generos as $genero) {
            Genero::updateOrCreate(
                ['nombre' => $genero['nombre']],
                [
                    'etiqueta' => $genero['etiqueta'],
                    'icono' => $genero['icono'],
                    'orden' => $genero['orden'],
                    'activo' => true,
                ]
            );
        }
    }
}
