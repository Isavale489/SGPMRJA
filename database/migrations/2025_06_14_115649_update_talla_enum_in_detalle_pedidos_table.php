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
            $table->enum('talla', ['Talla Unica', 'XS', 'S', 'M', 'L', 'XL', 'XXL', '2', '4', '6', '8', '10', '12', '14', '16'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            // Revertir a las tallas originales si es necesario, o manejar la lógica de rollback
            $table->enum('talla', ['XS', 'S', 'M', 'L', 'XL', 'XXL'])->nullable()->change();
        });
    }
};
