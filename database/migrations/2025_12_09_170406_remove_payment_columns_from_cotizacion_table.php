<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Elimina las columnas de pago que no corresponden a cotizaciones.
     * Las cotizaciones son solo presupuestos, los pagos se registran en pedidos.
     */
    public function up(): void
    {
        // Buscar y eliminar cualquier FK asociada a banco_id
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'cotizacion' 
            AND COLUMN_NAME = 'banco_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            DB::statement("ALTER TABLE cotizacion DROP FOREIGN KEY `{$fk->CONSTRAINT_NAME}`");
        }

        // Eliminar columnas una por una
        $columnsToRemove = [
            'banco_id',
            'abono',
            'efectivo_pagado',
            'transferencia_pagado',
            'pago_movil_pagado',
            'referencia_transferencia',
            'referencia_pago_movil',
        ];

        foreach ($columnsToRemove as $column) {
            if (Schema::hasColumn('cotizacion', $column)) {
                Schema::table('cotizacion', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     * Restaura las columnas de pago en caso de rollback.
     */
    public function down(): void
    {
        Schema::table('cotizacion', function (Blueprint $table) {
            if (!Schema::hasColumn('cotizacion', 'abono')) {
                $table->decimal('abono', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('cotizacion', 'efectivo_pagado')) {
                $table->boolean('efectivo_pagado')->default(false);
            }
            if (!Schema::hasColumn('cotizacion', 'transferencia_pagado')) {
                $table->boolean('transferencia_pagado')->default(false);
            }
            if (!Schema::hasColumn('cotizacion', 'pago_movil_pagado')) {
                $table->boolean('pago_movil_pagado')->default(false);
            }
            if (!Schema::hasColumn('cotizacion', 'referencia_transferencia')) {
                $table->string('referencia_transferencia')->nullable();
            }
            if (!Schema::hasColumn('cotizacion', 'referencia_pago_movil')) {
                $table->string('referencia_pago_movil')->nullable();
            }
            if (!Schema::hasColumn('cotizacion', 'banco_id')) {
                $table->unsignedBigInteger('banco_id')->nullable();
            }
        });
    }
};
