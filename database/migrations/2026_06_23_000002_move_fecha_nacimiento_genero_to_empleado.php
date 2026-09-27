<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalización persona — mover `fecha_nacimiento` y `genero` a `empleado`.
 *
 * Estos atributos solo aplican a personas naturales y, en la práctica, solo
 * los captura/usa el módulo de Empleados (validación "mayor de edad para
 * contratar"). En `persona` generaban NULLs no-aplicables para clientes y
 * personas jurídicas. Se trasladan a `empleado`, que es su verdadero dueño.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleado', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('fecha_ingreso');
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable()->after('fecha_nacimiento');
        });

        // Backfill: copiar los valores existentes desde persona a su empleado.
        DB::statement(
            "UPDATE `empleado` e " .
            "JOIN `persona` p ON p.`id` = e.`persona_id` " .
            "SET e.`fecha_nacimiento` = p.`fecha_nacimiento`, e.`genero` = p.`genero`"
        );

        Schema::table('persona', function (Blueprint $table) {
            $table->dropColumn(['fecha_nacimiento', 'genero']);
        });
    }

    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('estado_geografico');
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable()->after('fecha_nacimiento');
        });

        DB::statement(
            "UPDATE `persona` p " .
            "JOIN `empleado` e ON e.`persona_id` = p.`id` " .
            "SET p.`fecha_nacimiento` = e.`fecha_nacimiento`, p.`genero` = e.`genero`"
        );

        Schema::table('empleado', function (Blueprint $table) {
            $table->dropColumn(['fecha_nacimiento', 'genero']);
        });
    }
};
