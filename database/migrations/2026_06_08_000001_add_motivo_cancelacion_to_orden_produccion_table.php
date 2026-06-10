<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Motivo de cancelación de una orden de producción. Obligatorio (a nivel de
     * aplicación) cuando la OP se cancela estando 'En Proceso' o superior: en la
     * confección textil la tela ya está cortada y se considera merma, por lo que
     * el stock NO se repone y se exige justificar la pérdida del material.
     */
    public function up(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->text('motivo_cancelacion')->nullable()->after('notas');
        });
    }

    public function down(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->dropColumn('motivo_cancelacion');
        });
    }
};
