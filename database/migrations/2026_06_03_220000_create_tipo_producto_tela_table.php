<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Telas permitidas por tipo de producto (FEAT-003).
 *
 * Define qué telas (insumos con tipo='Tela') puede usar cada tipo de producto.
 * Reemplaza la deducción implícita "telas de los productos existentes": ahora
 * el catálogo es el Tipo y el selector de variante de la cotización ofrece
 * estas telas, sin requerir una fila `producto` por combinación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_producto_tela', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_producto_id');
            $table->unsignedBigInteger('insumo_id'); // insumo con tipo='Tela'
            $table->timestamps();

            $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->cascadeOnDelete();
            $table->foreign('insumo_id')->references('id')->on('insumo')->cascadeOnDelete();
            $table->unique(['tipo_producto_id', 'insumo_id'], 'tipo_producto_tela_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_producto_tela');
    }
};
