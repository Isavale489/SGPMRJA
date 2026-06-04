<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FEAT-003 — SKU congelado por línea (decisión §8 del spec).
 *
 * Guarda el SKU resuelto al momento de crear la línea (legacy: `producto.codigo`;
 * dinámico: SKU calculado por `ProductoService::generarCodigo`). Aunque el SKU es
 * recomputable desde tipo+tela+atributos, persistirlo garantiza trazabilidad
 * estable en PDFs/órdenes aunque el catálogo (códigos de tela/atributos) cambie.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->string('sku_snapshot', 100)->nullable()->after('atributos_snapshot');
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->string('sku_snapshot', 100)->nullable()->after('atributos_snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->dropColumn('sku_snapshot');
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropColumn('sku_snapshot');
        });
    }
};
