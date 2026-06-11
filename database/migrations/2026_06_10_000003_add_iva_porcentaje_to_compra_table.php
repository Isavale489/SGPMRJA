<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            if (!Schema::hasColumn('compra', 'iva_porcentaje')) {
                // Snapshot del % de IVA aplicado a la base gravable al registrar la
                // compra. Persistirlo evita el drift de inferirlo desde (iva/subtotal)
                // y mantiene correctos los comprobantes si la tasa general cambia.
                $table->decimal('iva_porcentaje', 5, 2)->default(0)->after('iva');
            }
        });

        // Backfill de las compras existentes: reconstruye el % desde el monto guardado.
        DB::table('compra')->where('subtotal', '>', 0)->orderBy('id')->each(function ($c) {
            $pct = round(($c->iva / $c->subtotal) * 100, 2);
            DB::table('compra')->where('id', $c->id)->update(['iva_porcentaje' => $pct]);
        });
    }

    public function down(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            if (Schema::hasColumn('compra', 'iva_porcentaje')) {
                $table->dropColumn('iva_porcentaje');
            }
        });
    }
};
