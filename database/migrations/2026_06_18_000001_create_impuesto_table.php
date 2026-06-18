<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla `impuesto` — catálogo central de impuestos del sistema.
 *
 * Centraliza el IVA (y, a futuro, cualquier otro impuesto venezolano) en una
 * sola entidad gestionable desde el panel /configuracion. La tasa viva del IVA
 * se resuelve con Impuesto::tasaIva(); cada compra conserva su snapshot
 * (compra.iva_porcentaje), así que cambiar la tasa no altera comprobantes previos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('impuesto', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();   // p.ej. IVA (mayúsculas, inmutable)
            $table->string('nombre', 100);
            $table->decimal('porcentaje', 5, 2)->default(0);
            $table->string('descripcion', 255)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impuesto');
    }
};
