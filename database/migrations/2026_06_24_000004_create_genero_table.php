<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de género de prenda (Dama / Caballero / Unisex).
 *
 * Mismo patrón que `color` y `talla`: catálogo con FK desde las líneas de
 * detalle. El cliente elige el género por línea al cotizar. Se siembra aquí
 * mismo (set estable, sin CRUD de administración) para que las migraciones de
 * FK posteriores puedan hacer backfill de las filas existentes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('genero', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique()->comment('Dama, Caballero, Unisex');
            $table->string('etiqueta', 50)->nullable()->comment('Etiqueta visual para UI');
            $table->string('icono', 40)->nullable()->comment('Clase de ícono remixicon para el chip');
            $table->unsignedInteger('orden')->default(0)->comment('Orden de despliegue en UI');
            $table->boolean('activo')->default(true)->comment('Permite desactivar sin borrar');
            $table->timestamps();
            $table->softDeletes();
        });

        $now = now();
        DB::table('genero')->insert([
            ['nombre' => 'Dama',      'etiqueta' => 'Dama',      'icono' => 'ri-women-line', 'orden' => 1, 'activo' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Caballero', 'etiqueta' => 'Caballero', 'icono' => 'ri-men-line',   'orden' => 2, 'activo' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre' => 'Unisex',    'etiqueta' => 'Unisex',    'icono' => 'ri-group-line', 'orden' => 3, 'activo' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('genero');
    }
};
