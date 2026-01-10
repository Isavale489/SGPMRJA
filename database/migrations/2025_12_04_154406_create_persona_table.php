<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('persona', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('documento_identidad', 50)->unique();
            $table->enum('tipo_documento', ['V-', 'E-', 'J-', 'G-'])->default('V-');
            $table->string('email', 255)->unique()->nullable();
            $table->string('telefono', 30)->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado_persona', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona');
    }
};
