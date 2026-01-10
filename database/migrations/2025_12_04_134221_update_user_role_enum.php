<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // PASO 1: Migrar usuarios Operario y Almacenero a Supervisor
        DB::table('users')
            ->whereIn('role', ['Operario', 'Almacenero'])
            ->update(['role' => 'Supervisor']);

        // PASO 2: Modificar ENUM para tener solo Administrador y Supervisor
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('Administrador', 'Supervisor') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir ENUM a los 4 roles originales
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('Administrador', 'Supervisor', 'Operario', 'Almacenero') NOT NULL");
    }
};
