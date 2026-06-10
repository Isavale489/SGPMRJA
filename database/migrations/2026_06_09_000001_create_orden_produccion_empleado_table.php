<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orden_produccion_empleado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_produccion_id');
            $table->unsignedBigInteger('empleado_id');
            $table->timestamps();

            $table->unique(
                ['orden_produccion_id', 'empleado_id'],
                'op_emp_unique'
            );
            $table->foreign('orden_produccion_id')
                ->references('id')->on('orden_produccion')
                ->cascadeOnDelete();
            $table->foreign('empleado_id')
                ->references('id')->on('empleado')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orden_produccion_empleado');
    }
};
