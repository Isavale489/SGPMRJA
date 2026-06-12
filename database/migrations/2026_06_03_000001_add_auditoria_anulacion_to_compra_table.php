<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            $table->foreignId('anulado_por_id')
                ->nullable()
                ->after('estado')
                ->constrained('user')
                ->nullOnDelete();
            $table->timestamp('fecha_anulacion')->nullable()->after('anulado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            $table->dropForeign(['anulado_por_id']);
            $table->dropColumn(['anulado_por_id', 'fecha_anulacion']);
        });
    }
};
