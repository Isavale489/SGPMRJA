<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Elimina la columna color de producto ya que el color es un atributo
     * del detalle de pedido/cotización (asociado a la tela), no del producto base.
     */
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('color')->nullable()->after('modelo');
        });
    }
};
