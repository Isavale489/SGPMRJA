<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedor')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('user')->restrictOnDelete();
            $table->string('numero_factura', 30)->nullable();
            $table->date('fecha_compra');
            $table->date('fecha_vencimiento')->nullable();
            $table->enum('tipo_pago', ['contado', 'credito'])->default('contado');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('iva', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['borrador', 'recibida', 'anulada'])->default('recibida');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra');
    }
};
