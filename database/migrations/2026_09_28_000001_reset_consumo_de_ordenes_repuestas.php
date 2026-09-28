<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Órdenes canceladas en Pendiente (o eliminadas) repusieron su material al
 * inventario, pero su `cantidad_utilizada` seguía igual a lo estimado y el
 * reporte de Consumo de Insumos las contaba como consumo. Se pone en 0 donde
 * existe el movimiento de reposición de ese insumo para esa orden.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('detalle_orden_insumo as d')
            ->where('d.cantidad_utilizada', '>', 0)
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('movimiento_insumo as m')
                ->whereColumn('m.insumo_id', 'd.insumo_id')
                ->where('m.tipo_movimiento', 'Entrada')
                ->whereRaw("m.motivo = CONCAT('Reposición por cancelación OP #', d.orden_produccion_id)"))
            ->update(['d.cantidad_utilizada' => 0]);
    }

    public function down(): void
    {
        // Sin vuelta atrás: el dato anterior era incorrecto.
    }
};
