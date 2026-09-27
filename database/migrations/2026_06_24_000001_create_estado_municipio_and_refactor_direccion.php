<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo geográfico en BD (estado + municipio) y normalización de la ubicación.
 *
 * - Crea las tablas `estado` y `municipio` (división político-territorial de
 *   Venezuela) y las siembra desde database/data/municipios_venezuela.php.
 * - `direccion` pasa a referenciar el catálogo con FKs `estado_id` y
 *   `municipio_id` (antes texto libre en `estado` y `ciudad`). Se hace backfill
 *   por coincidencia exacta de nombre; lo que no matchea queda NULL.
 * - Se elimina `persona.estado_geografico` (redundante: la ubicación vive en
 *   `direccion`, y solo lo escribía Empleados).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estado', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
        });

        Schema::create('municipio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estado_id')->constrained('estado')->cascadeOnDelete();
            $table->string('nombre', 120);
            $table->unique(['estado_id', 'nombre']);
        });

        // Seed del catálogo
        $catalogo = require database_path('data/municipios_venezuela.php');
        foreach ($catalogo as $estadoNombre => $municipios) {
            $estadoId = DB::table('estado')->insertGetId(['nombre' => $estadoNombre]);
            $filas = array_map(
                fn ($m) => ['estado_id' => $estadoId, 'nombre' => $m],
                $municipios
            );
            DB::table('municipio')->insert($filas);
        }

        // direccion: nuevas FKs
        Schema::table('direccion', function (Blueprint $table) {
            $table->foreignId('estado_id')->nullable()->after('direccion')->constrained('estado')->nullOnDelete();
            $table->foreignId('municipio_id')->nullable()->after('estado_id')->constrained('municipio')->nullOnDelete();
        });

        // Backfill por nombre exacto (lo no coincidente —p.ej. data de prueba— queda NULL)
        DB::statement(
            "UPDATE `direccion` d " .
            "JOIN `estado` e ON e.`nombre` = d.`estado` " .
            "SET d.`estado_id` = e.`id`"
        );
        DB::statement(
            "UPDATE `direccion` d " .
            "JOIN `municipio` m ON m.`nombre` = d.`ciudad` AND m.`estado_id` = d.`estado_id` " .
            "SET d.`municipio_id` = m.`id`"
        );

        // Quitar columnas de texto ya migradas
        Schema::table('direccion', function (Blueprint $table) {
            $table->dropColumn(['estado', 'ciudad']);
        });

        // persona: quitar campo redundante
        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn('estado_geografico');
        });
    }

    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->string('estado_geografico', 100)->nullable()->after('email');
        });

        Schema::table('direccion', function (Blueprint $table) {
            $table->string('estado', 100)->nullable()->after('direccion');
            $table->string('ciudad', 100)->nullable()->after('estado');
        });

        // Restaurar texto desde el catálogo
        DB::statement(
            "UPDATE `direccion` d " .
            "LEFT JOIN `estado` e ON e.`id` = d.`estado_id` " .
            "LEFT JOIN `municipio` m ON m.`id` = d.`municipio_id` " .
            "SET d.`estado` = e.`nombre`, d.`ciudad` = m.`nombre`"
        );

        Schema::table('direccion', function (Blueprint $table) {
            $table->dropConstrainedForeignId('municipio_id');
            $table->dropConstrainedForeignId('estado_id');
        });

        Schema::dropIfExists('municipio');
        Schema::dropIfExists('estado');
    }
};
