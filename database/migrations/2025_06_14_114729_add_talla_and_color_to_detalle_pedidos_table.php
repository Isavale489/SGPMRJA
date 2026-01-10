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
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->string('color', 50)->nullable()->after('nombre_logo');
            $table->enum('talla', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropColumn(['talla', 'color']);
        });
    }
};
