<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distingue productos fabricados de productos de reventa.
     * Los de reventa (requiere_produccion = false) se venden pero no
     * entran al flujo de Órdenes de Producción (ej: gorras, accesorios).
     */
    public function up(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->boolean('requiere_produccion')->default(true)->after('requiere_tela');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->dropColumn('requiere_produccion');
        });
    }
};
