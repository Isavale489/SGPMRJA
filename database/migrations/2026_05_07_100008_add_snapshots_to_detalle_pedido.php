<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->json('tela_snapshot')->nullable()->after('producto_id');
            $table->json('atributos_snapshot')->nullable()->after('tela_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropColumn(['tela_snapshot', 'atributos_snapshot']);
        });
    }
};
