<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Crea la tabla telefono para normalizar la relación con persona.
     * Una persona puede tener múltiples teléfonos.
     */
    public function up(): void
    {
        Schema::create('telefono', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('persona')->onDelete('cascade');
            $table->string('numero', 20);
            $table->enum('tipo', ['movil', 'casa', 'trabajo'])->default('movil');
            $table->boolean('es_principal')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // Índice para búsquedas por persona
            $table->index('persona_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telefono');
    }
};
