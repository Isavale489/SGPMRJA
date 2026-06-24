<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * Normalización persona — eliminar la columna `apellido`.
 *
 * El sistema pasa a resolver la identidad de la persona en un único atributo
 * `nombre`, que el usuario ve como "Nombre / Razón Social":
 *   - Persona natural  → nombre + apellido concatenados (el servicio los une).
 *   - Persona jurídica → razón social.
 *
 * Antes de eliminar la columna se hace backfill concatenando el apellido
 * existente dentro de `nombre`, para no perder los apellidos ya cargados.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Backfill: fusionar apellido dentro de nombre (sin duplicar espacios)
        DB::statement(
            "UPDATE `persona` " .
            "SET `nombre` = TRIM(CONCAT(COALESCE(`nombre`, ''), ' ', COALESCE(`apellido`, ''))) " .
            "WHERE `apellido` IS NOT NULL AND `apellido` <> ''"
        );

        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn('apellido');
        });
    }

    public function down(): void
    {
        // Recrea la columna vacía; el split nombre→apellido no es reversible.
        Schema::table('persona', function (Blueprint $table) {
            $table->string('apellido', 100)->default('')->after('nombre');
        });
    }
};
