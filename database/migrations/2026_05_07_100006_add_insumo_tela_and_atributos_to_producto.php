<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->foreignId('insumo_tela_id')
                  ->nullable()
                  ->after('tipo_producto_id')
                  ->constrained('insumo')
                  ->nullOnDelete();
            $table->json('atributos_snapshot')->nullable()->after('precio_base');
        });
    }

    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->dropForeign(['insumo_tela_id']);
            $table->dropColumn(['insumo_tela_id', 'atributos_snapshot']);
        });
    }
};
