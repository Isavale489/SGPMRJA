<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Templates de insumos por tipo de producto.
 *
 * Al crear una orden de producción para un producto, se prellenan los insumos
 * requeridos del tipo (Tela X, Hilo Y, Botón Z) con sus cantidades por defecto.
 * El operador puede ajustar antes de guardar. Quita el paso más tedioso del
 * form de orden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_producto_insumo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_producto_id');
            $table->unsignedBigInteger('insumo_id');
            $table->decimal('cantidad_estimada', 10, 2);
            $table->timestamps();

            $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->cascadeOnDelete();
            $table->foreign('insumo_id')->references('id')->on('insumo')->cascadeOnDelete();
            $table->unique(['tipo_producto_id', 'insumo_id'], 'tipo_producto_insumo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_producto_insumo');
    }
};
