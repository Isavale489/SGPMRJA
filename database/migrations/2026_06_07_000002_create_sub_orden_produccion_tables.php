<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sub-órdenes de producción: una OrdenProduccion (principal) puede dividirse
     * en varias sub-órdenes (etapas/tareas), y cada sub-orden admite varios
     * empleados asignados (relación many-to-many con rol opcional).
     */
    public function up(): void
    {
        Schema::create('sub_orden_produccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')
                ->constrained('orden_produccion')
                ->cascadeOnDelete();
            $table->string('nombre', 120);                    // etapa/tarea: Corte, Costura, Bordado...
            $table->unsignedInteger('cantidad_asignada')->nullable();
            $table->enum('estado', ['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado'])
                ->default('Pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sub_orden_empleado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_orden_produccion_id')
                ->constrained('sub_orden_produccion')
                ->cascadeOnDelete();
            $table->foreignId('empleado_id')
                ->constrained('empleado')
                ->cascadeOnDelete();
            $table->string('rol', 80)->nullable();            // rol del empleado en la sub-orden
            $table->timestamps();
            $table->unique(['sub_orden_produccion_id', 'empleado_id'], 'sub_orden_empleado_unico');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_orden_empleado');
        Schema::dropIfExists('sub_orden_produccion');
    }
};
