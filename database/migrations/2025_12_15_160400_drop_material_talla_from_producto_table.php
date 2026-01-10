<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Elimina las columnas material y talla de producto ya que son atributos
     * del detalle de pedido/cotización, no del catálogo de productos.
     */
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn(['material', 'talla']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('material')->nullable()->after('modelo');
            $table->string('talla')->nullable()->after('material');
        });
    }
};
