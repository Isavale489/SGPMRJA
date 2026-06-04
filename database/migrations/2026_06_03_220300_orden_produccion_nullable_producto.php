<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FEAT-003 — la orden de producción puede fabricar variantes dinámicas.
 *
 * `producto_id` deja de ser obligatorio: la orden ya referencia `detalle_pedido_id`
 * (redesign 2026-05-27) y lee tipo+tela+atributos del snapshot de la línea cuando
 * no hay un Producto concreto. Se conserva la FK (MariaDB permite cambiar la
 * nullability sin soltarla).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE orden_produccion MODIFY producto_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE orden_produccion MODIFY producto_id BIGINT UNSIGNED NOT NULL');
    }
};
