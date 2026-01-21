<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo crear las columnas nuevas
        if (!Schema::hasColumn('pedido', 'banco_transferencia_id')) {
            Schema::table('pedido', function (Blueprint $table) {
                $table->foreignId('banco_transferencia_id')->nullable()->after('referencia_transferencia')->constrained('banco')->onDelete('set null');
                $table->foreignId('banco_pago_movil_id')->nullable()->after('referencia_pago_movil')->constrained('banco')->onDelete('set null');
            });
        }
        
        // No borramos banco_id por ahora para evitar errores de FK desconocidas
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('banco_id')->nullable()->after('referencia_pago_movil')->constrained('bancos')->onDelete('set null');
        });

        // Restaurar datos (aproximado, se pierde si había dos diferentes)
        // DB::statement('UPDATE pedidos SET banco_id = COALESCE(banco_transferencia_id, banco_pago_movil_id)');

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['banco_transferencia_id']);
            $table->dropColumn('banco_transferencia_id');
            $table->dropForeign(['banco_pago_movil_id']);
            $table->dropColumn('banco_pago_movil_id');
        });
    }
};
