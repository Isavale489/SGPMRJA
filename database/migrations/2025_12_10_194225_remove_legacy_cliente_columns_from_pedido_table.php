<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Elimina columnas redundantes de cliente de la tabla pedido.
     * Los datos ahora se obtienen de la relación cliente.persona
     */
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            // Eliminar columnas legacy de cliente
            $columnsToRemove = ['cliente_nombre', 'cliente_email', 'cliente_telefono', 'ci_rif'];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('pedido', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            if (!Schema::hasColumn('pedido', 'cliente_nombre')) {
                $table->string('cliente_nombre')->nullable()->after('cliente_id');
            }
            if (!Schema::hasColumn('pedido', 'cliente_email')) {
                $table->string('cliente_email')->nullable()->after('cliente_nombre');
            }
            if (!Schema::hasColumn('pedido', 'cliente_telefono')) {
                $table->string('cliente_telefono')->nullable()->after('cliente_email');
            }
            if (!Schema::hasColumn('pedido', 'ci_rif')) {
                $table->string('ci_rif')->nullable()->after('cliente_telefono');
            }
        });
    }
};
