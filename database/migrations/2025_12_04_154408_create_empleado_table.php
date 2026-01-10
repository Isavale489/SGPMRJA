<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->unique()->constrained('persona')->onDelete('cascade');
            $table->string('codigo_empleado', 50)->unique();
            $table->date('fecha_ingreso');
            $table->string('cargo', 100);
            $table->string('departamento', 100);
            $table->decimal('salario', 10, 2)->nullable();
            $table->boolean('estado')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleado');
    }
};
