<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop orden_produccion.costo_estimado.
 *
 * El campo era info duplicada del pedido (auto-rellenado desde el subtotal
 * de la línea = precio de venta) y ningún reporte lo consume. El precio de
 * venta vive en pedido.total. Si más adelante se necesita "costo real de
 * producción", se deriva de insumos × insumo.costo_unitario sin necesidad
 * de un campo aparte.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->dropColumn('costo_estimado');
        });
    }

    public function down(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->decimal('costo_estimado', 12, 2)->default(0)->after('estado');
        });
    }
};
