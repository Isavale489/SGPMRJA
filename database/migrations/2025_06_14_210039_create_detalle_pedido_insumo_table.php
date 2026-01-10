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
        Schema::create('detalle_pedido_insumo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detalle_pedido_id')->constrained('detalle_pedidos')->onDelete('cascade');
            $table->foreignId('insumo_id')->constrained('insumos')->onDelete('cascade');
            $table->decimal('cantidad_estimada', 8, 2); // Ajusta la precisión y escala según tus necesidades
            $table->timestamps();

            // Asegura que no haya duplicados para la misma combinación de detalle_pedido e insumo
            $table->unique(['detalle_pedido_id', 'insumo_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_pedido_insumo');
    }
};
