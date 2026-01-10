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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('rif')->nullable()->unique(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            // Revertir el cambio si es necesario, lo que implicaría volver a añadir la restricción unique
            // En este caso, simplemente no hacemos nada o la volvemos a poner si la lógica lo requiere
            // Pero para propósitos de este request, solo se pide quitarla.
            // $table->string('rif')->nullable()->unique()->change();
        });
    }
};
