<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la gestión de "Tipo de Pago" del módulo de compras.
     * Con ella desaparece la fecha de vencimiento, cuyo único disparador
     * era el pago a crédito.
     */
    public function up(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            if (Schema::hasColumn('compra', 'tipo_pago')) {
                $table->dropColumn('tipo_pago');
            }
            if (Schema::hasColumn('compra', 'fecha_vencimiento')) {
                $table->dropColumn('fecha_vencimiento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            if (!Schema::hasColumn('compra', 'fecha_vencimiento')) {
                $table->date('fecha_vencimiento')->nullable()->after('fecha_compra');
            }
            if (!Schema::hasColumn('compra', 'tipo_pago')) {
                $table->enum('tipo_pago', ['contado', 'credito'])->default('contado')->after('fecha_vencimiento');
            }
        });
    }
};
