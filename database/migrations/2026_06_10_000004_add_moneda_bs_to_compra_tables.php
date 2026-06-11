<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Compras en bolívares: el usuario carga el costo en Bs (lo que paga al
     * proveedor) y el sistema lo convierte a USD con la tasa BCV del día.
     *
     * - compra.tasa_cambio: snapshot de la tasa USD/VES aplicada a la compra.
     * - compra_detalle.costo_unitario_bs: costo unitario en Bs tal como se tecleó
     *   (fiel a la factura); el costo_unitario en USD se deriva de éste / tasa.
     */
    public function up(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            $table->decimal('tasa_cambio', 12, 4)->nullable()->after('iva_porcentaje');
        });

        Schema::table('compra_detalle', function (Blueprint $table) {
            $table->decimal('costo_unitario_bs', 14, 2)->nullable()->after('costo_unitario');
        });

        $this->backfillBolivares();
    }

    /**
     * Compras anteriores a esta feature se registraron pensando en USD. Para que
     * el módulo sea uniforme (todo en Bs con su equivalente USD), reconstruimos
     * el costo en Bs y la tasa con la tasa BCV vigente a la fecha de cada compra.
     * Es una aproximación histórica razonable; afecta solo a filas ya existentes.
     */
    private function backfillBolivares(): void
    {
        // Tasa más antigua registrada, como último recurso para fechas previas
        // al histórico de tasas.
        $tasaFallback = DB::table('tasa_cambio')->where('moneda', 'USD')
            ->orderBy('fecha_bcv')->value('valor');

        foreach (DB::table('compra')->get(['id', 'fecha_compra']) as $compra) {
            $tasa = DB::table('tasa_cambio')->where('moneda', 'USD')
                ->whereDate('fecha_bcv', '<=', $compra->fecha_compra)
                ->orderByDesc('fecha_bcv')
                ->value('valor') ?? $tasaFallback;

            if (!$tasa) {
                continue; // sin ninguna tasa registrada: se deja en null
            }

            DB::table('compra')->where('id', $compra->id)->update(['tasa_cambio' => $tasa]);
            DB::table('compra_detalle')->where('compra_id', $compra->id)
                ->update(['costo_unitario_bs' => DB::raw("ROUND(costo_unitario * {$tasa}, 2)")]);
        }
    }

    public function down(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            $table->dropColumn('tasa_cambio');
        });

        Schema::table('compra_detalle', function (Blueprint $table) {
            $table->dropColumn('costo_unitario_bs');
        });
    }
};
