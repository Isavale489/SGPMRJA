<?php

namespace Database\Seeders;

use App\Models\Impuesto;
use Illuminate\Database\Seeder;

/**
 * Siembra el catálogo de impuestos. Por ahora solo el IVA, con el valor
 * por defecto de config/impuestos.php (env IVA_TASA, 16%). Idempotente.
 */
class ImpuestoSeeder extends Seeder
{
    public function run(): void
    {
        Impuesto::updateOrCreate(
            ['codigo' => Impuesto::CODIGO_IVA],
            [
                'nombre'      => 'IVA (Impuesto al Valor Agregado)',
                'porcentaje'  => (float) config('impuestos.iva', 16),
                'descripcion' => 'Impuesto general aplicado a las líneas gravables de las compras.',
                'estado'      => 'activo',
            ]
        );
    }
}
