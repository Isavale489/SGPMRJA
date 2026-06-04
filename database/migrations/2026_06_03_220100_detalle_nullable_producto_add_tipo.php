<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * FEAT-003 — la línea de cotización/pedido se autodescribe por snapshots.
 *
 * `producto_id` deja de ser obligatorio (las variantes ahora se configuran al
 * vuelo desde tipo+tela+atributos) y se agrega `tipo_producto_id` para
 * referenciar el tipo directamente sin pasar por una fila `producto`.
 *
 * Nota: `producto_id` tiene FK; en MariaDB cambiar la nullability vía
 * `ALTER ... MODIFY` no requiere soltar la FK (la FK no depende del NULL).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE detalle_cotizacion MODIFY producto_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE detalle_pedido MODIFY producto_id BIGINT UNSIGNED NULL');

        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_producto_id')->nullable()->after('producto_id');
            $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->nullOnDelete();
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_producto_id')->nullable()->after('producto_id');
            $table->foreign('tipo_producto_id')->references('id')->on('tipo_producto')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('detalle_cotizacion', function (Blueprint $table) {
            $table->dropForeign(['tipo_producto_id']);
            $table->dropColumn('tipo_producto_id');
        });

        Schema::table('detalle_pedido', function (Blueprint $table) {
            $table->dropForeign(['tipo_producto_id']);
            $table->dropColumn('tipo_producto_id');
        });

        DB::statement('ALTER TABLE detalle_cotizacion MODIFY producto_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE detalle_pedido MODIFY producto_id BIGINT UNSIGNED NOT NULL');
    }
};
