<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra_detalle', function (Blueprint $table) {
            if (!Schema::hasColumn('compra_detalle', 'aplica_iva')) {
                // Snapshot por línea: hereda de insumo.aplica_iva al agregar el ítem,
                // pero puede destildarse para esta compra puntual.
                $table->boolean('aplica_iva')->default(true)->after('costo_unitario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra_detalle', function (Blueprint $table) {
            if (Schema::hasColumn('compra_detalle', 'aplica_iva')) {
                $table->dropColumn('aplica_iva');
            }
        });
    }
};
