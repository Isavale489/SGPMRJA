<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fecha de formalización del pedido: día en que el abono alcanzó por primera
     * vez el mínimo configurado (70%). A partir de ella se cuenta el plazo de
     * entrega (formalización + 30 días hábiles) y se congelan las líneas
     * (tallas/cantidades/diseño). Nula mientras el pedido no esté formalizado.
     */
    public function up(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->date('fecha_formalizacion')->nullable()->after('fecha_entrega_estimada');
        });
    }

    public function down(): void
    {
        Schema::table('pedido', function (Blueprint $table) {
            $table->dropColumn('fecha_formalizacion');
        });
    }
};
