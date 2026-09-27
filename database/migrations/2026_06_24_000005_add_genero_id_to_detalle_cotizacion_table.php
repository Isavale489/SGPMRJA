<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `genero_id` (FK NOT NULL) en detalle_cotizacion. El cliente elige el género
 * por línea al cotizar (obligatorio). Filas previas se rellenan con 'Unisex'.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Columna nullable para poder hacer backfill
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->unsignedBigInteger('genero_id')->nullable()->after('talla_id');
        });

        // 2. Backfill de filas existentes → Unisex
        $unisexId = DB::table('genero')->where('nombre', 'Unisex')->value('id');
        DB::table('detalle_cotizacion')->whereNull('genero_id')->update(['genero_id' => $unisexId]);

        // 3. NOT NULL + FK (restrict: no se borra un género en uso)
        DB::statement('ALTER TABLE `detalle_cotizacion` MODIFY `genero_id` BIGINT UNSIGNED NOT NULL');
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->foreign('genero_id')->references('id')->on('genero')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->dropForeign(['genero_id']);
            $table->dropColumn('genero_id');
        });
    }
};
