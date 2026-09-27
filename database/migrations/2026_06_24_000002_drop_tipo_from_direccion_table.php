<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Limpieza de `direccion` — eliminar la columna `tipo`.
 *
 * Nunca la elegía el usuario: el servicio la fijaba por rol (cliente/empleado =
 * 'casa', proveedor = 'trabajo') y no se leía en ninguna parte. Columna muerta.
 * Se conservan `es_principal` (modelo 1:N) y los SoftDeletes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('direccion', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }

    public function down(): void
    {
        Schema::table('direccion', function (Blueprint $table) {
            $table->enum('tipo', ['casa', 'trabajo', 'envio'])->default('casa')->after('municipio_id');
        });
    }
};
