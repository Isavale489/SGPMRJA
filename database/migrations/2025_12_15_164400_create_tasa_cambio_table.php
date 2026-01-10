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
        Schema::create('tasa_cambio', function (Blueprint $table) {
            $table->id();
            $table->string('moneda', 10); // USD, EUR, etc.
            $table->decimal('valor', 12, 4); // Valor de la tasa (ej: 50.3500)
            $table->date('fecha_bcv'); // Fecha según el BCV
            $table->string('fuente')->default('BCV'); // Fuente de la tasa
            $table->timestamps();

            $table->unique(['moneda', 'fecha_bcv']); // Una tasa por moneda por día
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasa_cambio');
    }
};
