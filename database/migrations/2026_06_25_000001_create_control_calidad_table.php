<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FEAT-006 — Control de Calidad.
 *
 * Cada fila es una inspección de una Orden de Producción finalizada
 * (1 orden : N inspecciones, por los ciclos de reproceso). Registra cuántas
 * unidades se inspeccionaron, cuántas conformes (aprobadas) y cuántas
 * defectuosas (rechazadas), el veredicto y el inspector (usuario/Supervisor).
 *
 * No toca stock: el consumo extra por reproceso ocurre por la vía normal de
 * producción (DetalleOrdenInsumo). Ver docs/conventions/business-flows.md.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('control_calidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')->constrained('orden_produccion')->cascadeOnDelete();
            $table->foreignId('inspector_id')->constrained('user'); // usuario que inspecciona (Supervisor)
            $table->unsignedInteger('cantidad_inspeccionada');
            $table->unsignedInteger('cantidad_aprobada');   // conformes
            $table->unsignedInteger('cantidad_rechazada');  // defectuosas
            $table->enum('resultado', ['aprobado', 'rechazado', 'observado']);
            $table->text('observaciones')->nullable();      // motivo (obligatorio si rechazado/observado)
            $table->timestamp('fecha_inspeccion');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('control_calidad');
    }
};
