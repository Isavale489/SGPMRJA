<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            // Agregar columna pedido_id si no existe
            if (!Schema::hasColumn('orden_produccion', 'pedido_id')) {
                $table->foreignId('pedido_id')->nullable()->after('id')->constrained('pedido')->nullOnDelete();
            }

            // Agregar columna logo si no existe
            if (!Schema::hasColumn('orden_produccion', 'logo')) {
                $table->string('logo')->nullable()->after('costo_estimado');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            if (Schema::hasColumn('orden_produccion', 'pedido_id')) {
                $table->dropForeign(['pedido_id']);
                $table->dropColumn('pedido_id');
            }

            if (Schema::hasColumn('orden_produccion', 'logo')) {
                $table->dropColumn('logo');
            }
        });
    }
};
