<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Pobla la infraestructura de variantes/atributos:
 *  - Códigos cortos en telas existentes y altas de telas faltantes (insumo tipo='Tela').
 *  - precio_confeccion + requiere_tela en tipos de producto.
 *  - Catálogo de atributos de confección con sus valores.
 *  - Asociación tipo_producto ↔ atributo.
 *
 * Idempotente: re-ejecutarlo no duplica filas.
 * Conservador: no toca nombres ni prefijos de tipos existentes.
 */
class ProductoVariantesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedTelas();
            $this->seedTiposProducto();
            $this->seedAtributos();
            $this->seedTipoProductoAtributo();
        });
    }

    private function seedTelas(): void
    {
        // Telas estándar con sus códigos definitivos. 'aliases' captura nombres ya existentes en DB.
        $telas = [
            ['nombre' => 'Dacron',           'codigo' => 'DAC', 'costo' => 12.00, 'aliases' => []],
            ['nombre' => 'Oxford',           'codigo' => 'OXF', 'costo' => 18.00, 'aliases' => []],
            ['nombre' => 'Algodón Jersey',   'codigo' => 'AJR', 'costo' => 10.00, 'aliases' => ['Jersey']],
            ['nombre' => 'Piqué',            'codigo' => 'PIQ', 'costo' => 15.00, 'aliases' => ['Pique']],
            ['nombre' => 'Microfibra',       'codigo' => 'MFB', 'costo' => 14.00, 'aliases' => []],
            ['nombre' => 'Gabardina / Dril', 'codigo' => 'GBD', 'costo' => 22.00, 'aliases' => []],
        ];

        foreach ($telas as $t) {
            $candidatos = array_merge([$t['nombre']], $t['aliases']);
            $existente  = DB::table('insumo')
                ->where('tipo', 'Tela')
                ->whereIn('nombre', $candidatos)
                ->first();

            if ($existente) {
                // Solo asigna código si no tiene; respeta nombre y costo si ya están registrados.
                if (empty($existente->codigo)) {
                    DB::table('insumo')->where('id', $existente->id)->update([
                        'codigo'     => $t['codigo'],
                        'updated_at' => now(),
                    ]);
                }
            } else {
                DB::table('insumo')->insert([
                    'nombre'         => $t['nombre'],
                    'codigo'         => $t['codigo'],
                    'tipo'           => 'Tela',
                    'unidad_medida'  => 'Metro',
                    'costo_unitario' => $t['costo'],
                    'stock_actual'   => 0,
                    'stock_minimo'   => 0,
                    'estado'         => 1,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }
        }
    }

    private function seedTiposProducto(): void
    {
        // Solo actualiza precio_confeccion y requiere_tela. NO altera nombre ni prefijo.
        $precios = [
            'Camisa'   => 15.00,
            'Franela'  => 8.00,
            'Chemise'  => 12.00,
            'Pantalón' => 18.00,
            'Chaqueta' => 25.00,
        ];

        foreach ($precios as $nombre => $precio) {
            DB::table('tipo_producto')
                ->where('nombre', $nombre)
                ->update([
                    'precio_confeccion' => $precio,
                    'requiere_tela'     => 1,
                    'updated_at'        => now(),
                ]);
        }
    }

    private function seedAtributos(): void
    {
        $atributos = [
            ['nombre' => 'Manga',   'codigo' => 'MNG', 'valores' => [
                ['Larga', 'L', 1],
                ['Corta', 'C', 2],
            ]],
            ['nombre' => 'Cuello',  'codigo' => 'CLL', 'valores' => [
                ['Clásico',          'CLA', 1],
                ['Mao',              'MAO', 2],
                ['Con Tapa Botones', 'CTB', 3],
                ['Redondo',          'R',   4],
                ['V',                'V',   5],
            ]],
            ['nombre' => 'Corte',   'codigo' => 'CRT', 'valores' => [
                ['Rígido',  'RIG', 1],
                ['Stretch', 'STR', 2],
            ]],
            ['nombre' => 'Cierre',  'codigo' => 'CRR', 'valores' => [
                ['Cremallera', 'CRE', 1],
                ['Botones',    'BOT', 2],
                ['Cerrado',    'CER', 3],
            ]],
            ['nombre' => 'Capucha', 'codigo' => 'CPC', 'valores' => [
                ['Con capucha', 'CCH', 1],
                ['Sin capucha', 'SCH', 2],
            ]],
        ];

        foreach ($atributos as $a) {
            $atrId = DB::table('atributo')->where('nombre', $a['nombre'])->value('id');

            if (!$atrId) {
                $atrId = DB::table('atributo')->insertGetId([
                    'nombre'     => $a['nombre'],
                    'codigo'     => $a['codigo'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            foreach ($a['valores'] as [$nombre, $codigo, $orden]) {
                $existe = DB::table('atributo_valor')
                    ->where('atributo_id', $atrId)
                    ->where('nombre', $nombre)
                    ->exists();

                if (!$existe) {
                    DB::table('atributo_valor')->insert([
                        'atributo_id' => $atrId,
                        'nombre'      => $nombre,
                        'codigo'      => $codigo,
                        'orden'       => $orden,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);
                }
            }
        }
    }

    private function seedTipoProductoAtributo(): void
    {
        // Mapeo: tipo → [atributo => orden_en_sku]
        $mapeo = [
            'Camisa'   => ['Manga' => 1, 'Cuello' => 2],
            'Franela'  => ['Manga' => 1, 'Cuello' => 2],
            'Chemise'  => ['Manga' => 1, 'Cuello' => 2],
            'Pantalón' => ['Corte' => 1],
            'Chaqueta' => ['Cierre' => 1, 'Capucha' => 2],
        ];

        foreach ($mapeo as $tipoNombre => $atributos) {
            $tipoId = DB::table('tipo_producto')->where('nombre', $tipoNombre)->value('id');
            if (!$tipoId) {
                continue;
            }

            foreach ($atributos as $atrNombre => $orden) {
                $atrId = DB::table('atributo')->where('nombre', $atrNombre)->value('id');
                if (!$atrId) {
                    continue;
                }

                $existe = DB::table('tipo_producto_atributo')
                    ->where('tipo_producto_id', $tipoId)
                    ->where('atributo_id', $atrId)
                    ->exists();

                if (!$existe) {
                    DB::table('tipo_producto_atributo')->insert([
                        'tipo_producto_id' => $tipoId,
                        'atributo_id'      => $atrId,
                        'es_obligatorio'   => 1,
                        'orden'            => $orden,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            }
        }
    }
}
