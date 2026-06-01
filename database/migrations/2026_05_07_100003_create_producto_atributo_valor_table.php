<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_atributo_valor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')
                  ->constrained('producto')
                  ->cascadeOnDelete();
            $table->foreignId('atributo_valor_id')
                  ->constrained('atributo_valor')
                  ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['producto_id', 'atributo_valor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_atributo_valor');
    }
};
