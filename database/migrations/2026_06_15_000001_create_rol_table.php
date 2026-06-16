<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('rol')) {
            return;
        }

        // Roles administrables (FEAT-005). 'Administrador' y 'Supervisor' se
        // siembran como roles de sistema (es_sistema = 1) en la migración
        // 2026_06_15_000003: no se renombran ni eliminan. Los demás roles los
        // crea el administrador desde el panel de seguridad.
        Schema::create('rol', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();          // 'Administrador', 'Supervisor', 'Vendedor'...
            $table->string('descripcion')->nullable();
            $table->boolean('es_sistema')->default(false); // sembrados: no renombrar/eliminar
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
