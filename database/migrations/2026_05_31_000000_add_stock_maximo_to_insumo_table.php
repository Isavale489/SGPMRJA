<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tercer indicador de stock (Existencia Máxima). Se suma a stock_actual
     * (Existencia Actual) y stock_minimo (Existencia Mínima) ya existentes.
     */
    public function up(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            $table->decimal('stock_maximo', 10, 2)->default(0)->after('stock_minimo');
        });
    }

    public function down(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            $table->dropColumn('stock_maximo');
        });
    }
};
