<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            // Agregar relación con tipo_producto
            $table->foreignId('tipo_producto_id')->nullable()->after('id')->constrained('tipo_producto')->nullOnDelete();

            // Agregar código único autogenerado
            $table->string('codigo', 20)->unique()->nullable()->after('tipo_producto_id');
        });

        // Eliminar columna nombre (se construye desde tipo + modelo)
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('nombre')->after('id');
        });

        Schema::table('producto', function (Blueprint $table) {
            $table->dropForeign(['tipo_producto_id']);
            $table->dropColumn(['tipo_producto_id', 'codigo']);
        });
    }
};
