<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido', 'cotizacion_id')) {
                $table->foreignId('cotizacion_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('cotizacion')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (Schema::hasColumn('pedido', 'cotizacion_id')) {
                $table->dropForeign(['cotizacion_id']);
                $table->dropColumn('cotizacion_id');
            }
        });
    }
};
