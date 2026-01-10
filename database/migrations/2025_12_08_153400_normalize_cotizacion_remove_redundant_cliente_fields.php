<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Normaliza la tabla cotizacion eliminando campos redundantes de cliente
     */
    public function up(): void
    {
        // Primero actualizamos las cotizaciones existentes para asegurar que tengan cliente_id
        // si tienen datos de cliente pero no cliente_id
        DB::statement("
            UPDATE cotizacion c
            SET cliente_id = (
                SELECT cl.id FROM cliente cl 
                WHERE cl.documento = c.ci_rif 
                OR cl.nombre = c.cliente_nombre
                LIMIT 1
            )
            WHERE c.cliente_id IS NULL 
            AND (c.ci_rif IS NOT NULL OR c.cliente_nombre IS NOT NULL)
        ");

        // Hacer cliente_id NOT NULL (después de la migración de datos)
        Schema::table('cotizacion', function (Blueprint $table) {
            // Primero eliminamos la foreign key existente
            $table->dropForeign(['cliente_id']);
        });

        // Modificar la columna para que no sea nullable
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->nullable(false)->change();
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('restrict');
        });

        // Eliminar columnas redundantes
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropColumn(['cliente_nombre', 'cliente_email', 'cliente_telefono', 'ci_rif']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->string('cliente_nombre')->nullable()->after('cliente_id');
            $table->string('cliente_email')->nullable()->after('cliente_nombre');
            $table->string('cliente_telefono', 20)->nullable()->after('cliente_email');
            $table->string('ci_rif', 20)->nullable()->after('cliente_telefono');
        });

        // Restaurar datos desde la relación cliente
        DB::statement("
            UPDATE cotizacion c
            INNER JOIN cliente cl ON c.cliente_id = cl.id
            SET c.cliente_nombre = cl.nombre,
                c.cliente_email = cl.email,
                c.cliente_telefono = cl.telefono,
                c.ci_rif = cl.documento
        ");

        // Hacer cliente_id nullable nuevamente
        Schema::table('cotizacion', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->unsignedBigInteger('cliente_id')->nullable()->change();
            $table->foreign('cliente_id')->references('id')->on('cliente')->onDelete('set null');
        });
    }
};
