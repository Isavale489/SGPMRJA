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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->decimal('abono', 10, 2)->default(0)->after('total');
            $table->boolean('efectivo_pagado')->default(false)->after('abono');
            $table->boolean('transferencia_pagado')->default(false)->after('efectivo_pagado');
            $table->boolean('pago_movil_pagado')->default(false)->after('transferencia_pagado');
            $table->string('referencia_transferencia')->nullable()->after('pago_movil_pagado');
            $table->string('referencia_pago_movil')->nullable()->after('referencia_transferencia');
            $table->foreignId('banco_id')->nullable()->constrained('bancos')->onDelete('set null')->after('referencia_pago_movil');
            $table->string('prioridad')->default('Normal')->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['abono', 'efectivo_pagado', 'transferencia_pagado', 'pago_movil_pagado', 'referencia_transferencia', 'referencia_pago_movil', 'prioridad']);
            $table->dropConstrainedForeignId('banco_id');
        });
    }
};
