<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Renombrar todas las tablas de plural a singular
        DB::statement("RENAME TABLE 
            users TO user,
            productos TO producto,
            proveedores TO proveedor,
            insumos TO insumo,
            pedidos TO pedido,
            cotizaciones TO cotizacion,
            clientes TO cliente,
            ordenes_produccion TO orden_produccion,
            detalle_orden_insumos TO detalle_orden_insumo,
            movimientos_insumos TO movimiento_insumo,
            detalle_pedidos TO detalle_pedido,
            detalle_cotizaciones TO detalle_cotizacion,
            bancos TO banco
        ");
    }

    public function down(): void
    {
        // Revertir a plural
        DB::statement("RENAME TABLE 
            user TO users,
            producto TO productos,
            proveedor TO proveedores,
            insumo TO insumos,
            pedido TO pedidos,
            cotizacion TO cotizaciones,
            cliente TO clientes,
            orden_produccion TO ordenes_produccion,
            detalle_orden_insumo TO detalle_orden_insumos,
            movimiento_insumo TO movimientos_insumos,
            detalle_pedido TO detalle_pedidos,
            detalle_cotizacion TO detalle_cotizaciones,
            banco TO bancos
        ");
    }
};
