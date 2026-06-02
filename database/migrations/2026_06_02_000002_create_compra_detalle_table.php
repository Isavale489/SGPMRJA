<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compra')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained('insumo')->restrictOnDelete();
            $table->decimal('cantidad', 10, 2);
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_detalle');
    }
};
