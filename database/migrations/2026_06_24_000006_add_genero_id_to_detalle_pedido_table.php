<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `genero_id` (FK NOT NULL) en detalle_pedido. Se propaga desde
 * detalle_cotizacion al convertir a pedido. Filas previas → 'Unisex'.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('genero_id')->nullable()->after('talla_id');
        });

        $unisexId = DB::table('genero')->where('nombre', 'Unisex')->value('id');
        DB::table('detalle_pedido')->whereNull('genero_id')->update(['genero_id' => $unisexId]);

        DB::statement('ALTER TABLE `detalle_pedido` MODIFY `genero_id` BIGINT UNSIGNED NOT NULL');
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->foreign('genero_id')->references('id')->on('genero')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropForeign(['genero_id']);
            $table->dropColumn('genero_id');
        });
    }
};
