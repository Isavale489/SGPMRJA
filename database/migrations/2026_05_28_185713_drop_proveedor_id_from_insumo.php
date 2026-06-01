<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop insumo.proveedor_id — columna huérfana.
 *
 * Cierra el refactor de Santiago (e607f64) que eliminó toda la lógica de
 * proveedor del módulo de insumos pero dejó la columna y su FK en el
 * esquema. Ningún código del repo la lee/escribe.
 *
 * Datos: 6 de 10 filas tenían valor; se pierde el dato (ya muerto).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            $table->dropForeign('insumos_proveedor_id_foreign');
            $table->dropColumn('proveedor_id');
        });
    }

    public function down(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            $table->unsignedBigInteger('proveedor_id')->nullable()->after('stock_minimo');
            $table->foreign('proveedor_id', 'insumos_proveedor_id_foreign')
                ->references('id')->on('proveedor')->nullOnDelete();
        });
    }
};
