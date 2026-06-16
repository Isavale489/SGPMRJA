<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('permiso_rol')) {
            return;
        }

        // Permisos otorgados a cada rol (FEAT-005). Cada fila es un permiso
        // 'modulo.accion' concedido. Sin fila = denegado. El rol Administrador
        // NO consulta esta tabla (bypass por Gate::before en TASK-037).
        Schema::create('permiso_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('rol');
            $table->string('permiso');                   // 'compras.procesar', 'cotizaciones.ver'...
            $table->unique(['rol_id', 'permiso']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_rol');
    }
};
