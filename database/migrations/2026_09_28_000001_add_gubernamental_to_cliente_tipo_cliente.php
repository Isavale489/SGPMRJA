<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El prefijo G- define un cliente gubernamental (regla del sistema: el tipo se
 * deriva del prefijo del documento), pero el ENUM solo admitía natural/jurídico:
 * el alta desde el maestro se rechazaba y clientes.from-persona fallaba en la BD.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `cliente` MODIFY COLUMN `tipo_cliente`
            ENUM('natural','juridico','gubernamental') NOT NULL DEFAULT 'natural'");
    }

    public function down(): void
    {
        DB::statement("UPDATE `cliente` SET `tipo_cliente` = 'juridico' WHERE `tipo_cliente` = 'gubernamental'");
        DB::statement("ALTER TABLE `cliente` MODIFY COLUMN `tipo_cliente`
            ENUM('natural','juridico') NOT NULL DEFAULT 'natural'");
    }
};
