<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colapso de `direccion` a 1:1 — eliminar `es_principal`.
 *
 * En la práctica cada persona tiene exactamente una dirección y siempre estaba
 * marcada como principal. Sin la columna `tipo` (ya eliminada), una dirección
 * "no principal" no significaba nada. Se adopta el modelo 1:1: persona tiene
 * una dirección (Persona::direccion hasOne). Se conservan los SoftDeletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direccion', function (Blueprint $table) {
            $table->dropColumn('es_principal');
        });
    }

    public function down(): void
    {
        Schema::table('direccion', function (Blueprint $table) {
            $table->boolean('es_principal')->default(true)->after('municipio_id');
        });
    }
};
