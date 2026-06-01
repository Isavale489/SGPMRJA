<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina el módulo Producción Diaria.
 *
 * El avance ahora se registra directo sobre la orden: cada orden acumula
 * `cantidad_producida` y `cantidad_defectuosa`. El empleado ya vive en la
 * orden (empleado_id), así que el histórico por registro de produccion_diaria
 * deja de ser necesario. Las métricas (eficiencia, rendimiento por empleado)
 * se derivan de orden_produccion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->integer('cantidad_defectuosa')->default(0)->after('cantidad_producida');
        });

        Schema::dropIfExists('produccion_diaria');
    }

    public function down(): void
    {
        Schema::create('produccion_diaria', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_id');
            $table->date('fecha_produccion')->nullable();
            $table->unsignedBigInteger('empleado_id');
            $table->integer('cantidad_producida');
            $table->integer('cantidad_defectuosa')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('orden_id')->references('id')->on('orden_produccion')->cascadeOnDelete();
            $table->foreign('empleado_id')->references('id')->on('empleado')->restrictOnDelete();
            $table->index(['orden_id', 'fecha_produccion'], 'idx_prod_diaria_orden_fecha');
        });

        Schema::table('orden_produccion', function (Blueprint $table) {
            $table->dropColumn('cantidad_defectuosa');
        });
    }
};
