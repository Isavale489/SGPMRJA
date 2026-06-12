<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->decimal('tasa_cambio_valor', 10, 4)->nullable()->after('total')
                ->comment('Tasa BCV USD→VES vigente al momento de crear/actualizar la cotización');
            $table->text('condiciones_terminos')->nullable()->after('notas')
                ->comment('Cláusulas legales y condiciones de la cotización');
        });
    }

    public function down(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropColumn(['tasa_cambio_valor', 'condiciones_terminos']);
        });
    }
};
