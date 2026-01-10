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
        Schema::create('tipo_producto', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100); // Chemise, Franela, Pantalón, etc.
            $table->string('codigo_prefijo', 5)->unique(); // CHM, FRN, PNT, etc.
            $table->text('descripcion')->nullable();
            $table->integer('contador')->default(0); // Para generar códigos secuenciales
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_producto');
    }
};
