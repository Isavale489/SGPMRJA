<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Agrega cliente_id como FK a la tabla pedido para normalizar la relación.
     * La columna es nullable para permitir pedidos existentes sin cliente asociado.
     */
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            // Agregar cliente_id después del id
            $table->foreignId('cliente_id')
                ->nullable()
                ->after('id')
                ->constrained('cliente')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropColumn('cliente_id');
        });
    }
};
