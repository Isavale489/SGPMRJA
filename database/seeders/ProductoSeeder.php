<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder histórico — quedó vacío después del refactor a variantes.
 * Los productos reales se cargan por el admin desde /productos con
 * el form que respeta tipo + tela + atributos. ProductoVariantesSeeder
 * cubre el catálogo de tipos, atributos y telas.
 */
class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // Sin operaciones. Mantenido como placeholder para compatibilidad
        // con el orden de DatabaseSeeder y futuras siembras opcionales.
    }
}
