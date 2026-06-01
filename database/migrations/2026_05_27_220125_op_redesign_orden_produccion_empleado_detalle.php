<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rediseño Órdenes de Producción.
 *
 * Modelo nuevo: 1 orden = 1 producto (línea) de un pedido, asignada a un
 * empleado. Un pedido con N líneas es apto para N órdenes; el progreso del
 * pedido se agrega desde sus órdenes.
 *
 *  + detalle_pedido_id  → línea exacta del pedido que produce la orden
 *                         (de ella salen producto, cantidad, color, talla, bordados)
 *  + empleado_id        → empleado asignado a la orden
 *  - logo_id            → los logos/bordados se definen por producto en la cotización,
 *                         no en la orden
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->unsignedBigInteger('detalle_pedido_id')->nullable()->after('pedido_id');
            $table->unsignedBigInteger('empleado_id')->nullable()->after('producto_id');

            $table->foreign('detalle_pedido_id')->references('id')->on('detalle_pedido')->nullOnDelete();
            $table->foreign('empleado_id')->references('id')->on('empleado')->nullOnDelete();
        });

        // Eliminar logo_id (FK + columna)
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->dropForeign('orden_produccion_logo_id_foreign');
            $table->dropColumn('logo_id');
        });
    }

    public function down(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->dropForeign(['detalle_pedido_id']);
            $table->dropForeign(['empleado_id']);
            $table->dropColumn(['detalle_pedido_id', 'empleado_id']);
        });

        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->unsignedBigInteger('logo_id')->nullable()->after('costo_estimado');
            $table->foreign('logo_id')->references('id')->on('logo')->nullOnDelete();
        });
    }
};
