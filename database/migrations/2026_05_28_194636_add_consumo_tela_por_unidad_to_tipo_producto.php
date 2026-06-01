<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consumo de tela por unidad producida (metros por pieza).
 *
 * Junto con tipo.insumosDefault (insumos constantes al tipo), permite
 * auto-prellenar el form de orden de producción: al crear una orden de
 * 10 camisas, se calcula tela = consumo_tela_por_unidad × 10, usando la
 * tela específica de la variante (producto.insumo_tela_id).
 *
 * Default 0 → tipos preexistentes no autollenan tela hasta que el usuario
 * configure el valor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->decimal('consumo_tela_por_unidad', 8, 2)->default(0)->after('requiere_tela');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->dropColumn('consumo_tela_por_unidad');
        });
    }
};
