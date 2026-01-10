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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->string('cliente_nombre');
            $table->string('cliente_email')->nullable();
            $table->string('cliente_telefono')->nullable();
            $table->string('ci_rif')->nullable();
            $table->date('fecha_cotizacion');
            $table->date('fecha_validez')->nullable();
            $table->string('estado')->default('Pendiente');
            $table->decimal('total', 10, 2)->default(0.00);
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('abono', 10, 2)->default(0);
            $table->boolean('efectivo_pagado')->default(false);
            $table->boolean('transferencia_pagado')->default(false);
            $table->boolean('pago_movil_pagado')->default(false);
            $table->string('referencia_transferencia')->nullable();
            $table->string('referencia_pago_movil')->nullable();
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->nullOnDelete();
            $table->string('prioridad')->default('Normal');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};
