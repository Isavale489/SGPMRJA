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
        Schema::create('detalle_cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->integer('cantidad');
            $table->text('descripcion')->nullable();
            $table->boolean('lleva_bordado')->default(false);
            $table->string('nombre_logo')->nullable();
            $table->string('ubicacion_logo')->nullable();
            $table->integer('cantidad_logo')->nullable();
            $table->string('color', 50)->nullable();
            $table->string('talla')->nullable();
            $table->decimal('precio_unitario', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_cotizaciones');
    }
};
