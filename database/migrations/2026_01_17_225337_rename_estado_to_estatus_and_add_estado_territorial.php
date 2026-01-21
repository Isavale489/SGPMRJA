<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Renombrar columna 'estado' a 'estatus' en tabla 'cliente'
        Schema::table('cliente', function (Blueprint $table) {
            $table->renameColumn('estado', 'estatus');
        });

        // 2. Agregar columna 'estado' (territorio) en tabla 'direccion'
        Schema::table('direccion', function (Blueprint $table) {
            $table->string('estado', 50)->nullable()->after('ciudad')->comment('Estado/Territorio geográfico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir: eliminar columna 'estado' de 'direccion'
        Schema::table('direccion', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        // Revertir: renombrar 'estatus' a 'estado' en 'cliente'
        Schema::table('cliente', function (Blueprint $table) {
            $table->renameColumn('estatus', 'estado');
        });
    }
};
