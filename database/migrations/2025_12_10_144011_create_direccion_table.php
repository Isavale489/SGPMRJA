<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Crea la tabla direccion para normalizar la relación con persona.
     * Una persona puede tener múltiples direcciones.
     */
    public function up(): void
    {
        Schema::create('direccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('persona')->onDelete('cascade');
            $table->string('direccion', 255);
            $table->string('ciudad', 100)->nullable();
            $table->enum('tipo', ['casa', 'trabajo', 'envio'])->default('casa');
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
        Schema::dropIfExists('direccion');
    }
};
