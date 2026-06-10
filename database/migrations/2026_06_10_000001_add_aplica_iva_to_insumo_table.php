<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            if (!Schema::hasColumn('insumo', 'aplica_iva')) {
                // Gravable (true) vs exento (false). La mayoría de los insumos pagan IVA.
                $table->boolean('aplica_iva')->default(true)->after('costo_unitario');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            if (Schema::hasColumn('insumo', 'aplica_iva')) {
                $table->dropColumn('aplica_iva');
            }
        });
    }
};
