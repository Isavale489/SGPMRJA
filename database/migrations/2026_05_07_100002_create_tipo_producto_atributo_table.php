<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_producto_atributo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_producto_id')
                  ->constrained('tipo_producto')
                  ->cascadeOnDelete();
            $table->foreignId('atributo_id')
                  ->constrained('atributo')
                  ->cascadeOnDelete();
            $table->boolean('es_obligatorio')->default(true);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['tipo_producto_id', 'atributo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_producto_atributo');
    }
};
