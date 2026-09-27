<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('configuracion')) {
            return;
        }

        // Overrides de parámetros del sistema (FEAT-004). Solo guarda los valores
        // que el administrador cambió desde el panel: la definición de cada
        // parámetro (tipo, reglas, default) vive en config/parametros.php.
        // Sin fila = el sistema usa el default del registry.
        Schema::create('configuracion', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();   // ej: 'impuestos.iva'
            $table->string('valor');             // texto; el helper castea según el tipo del registry
            $table->foreignId('updated_by_id')->nullable()->constrained('user');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion');
    }
};
