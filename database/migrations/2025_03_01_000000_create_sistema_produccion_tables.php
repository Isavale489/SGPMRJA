<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Productos (Simplificado)
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->text('descripcion')->nullable();
            $table->string('modelo', 50);
            $table->string('color', 50);
            $table->enum('talla', ['XS', 'S', 'M', 'L', 'XL', 'XXL']);
            $table->string('material', 50);
            $table->decimal('precio_base', 10, 2);
            $table->string('imagen')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Proveedores (Simplificado)
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social', 100);
            $table->string('ruc', 11)->unique();
            $table->string('direccion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('contacto', 100)->nullable();
            $table->string('telefono_contacto', 20)->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Insumos (Simplificado)
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['Tela', 'Hilo', 'Botón', 'Cierre', 'Etiqueta', 'Otro']);
            $table->string('unidad_medida', 20);
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('stock_actual', 10, 2);
            $table->decimal('stock_minimo', 10, 2);
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->boolean('estado')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Órdenes de Producción (Simplificado)
        Schema::create('ordenes_produccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->integer('cantidad_solicitada');
            $table->integer('cantidad_producida')->default(0);
            $table->date('fecha_inicio');
            $table->date('fecha_fin_estimada');
            $table->enum('estado', ['Pendiente', 'En Proceso', 'Finalizado', 'Cancelado'])->default('Pendiente');
            $table->decimal('costo_estimado', 12, 2)->default(0);
            $table->text('notas')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        // Detalle de Insumos por Orden (Simplificado)
        Schema::create('detalle_orden_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_produccion_id')->constrained('ordenes_produccion')->cascadeOnDelete();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->decimal('cantidad_estimada', 10, 2);
            $table->decimal('cantidad_utilizada', 10, 2)->default(0);
            $table->timestamps();
        });

        // Producción Diaria (Simplificado)
        Schema::create('produccion_diaria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_id')->constrained('ordenes_produccion')->cascadeOnDelete();
            $table->foreignId('operario_id')->constrained('users')->cascadeOnDelete();
            $table->integer('cantidad_producida');
            $table->integer('cantidad_defectuosa')->default(0);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Movimientos de Insumos (Simplificado)
        Schema::create('movimientos_insumos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insumo_id')->constrained()->cascadeOnDelete();
            $table->enum('tipo_movimiento', ['Entrada', 'Salida']);
            $table->decimal('cantidad', 10, 2);
            $table->decimal('stock_anterior', 10, 2);
            $table->decimal('stock_nuevo', 10, 2);
            $table->text('motivo')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimientos_insumos');
        Schema::dropIfExists('produccion_diaria');
        Schema::dropIfExists('detalle_orden_insumos');
        Schema::dropIfExists('ordenes_produccion');
        Schema::dropIfExists('insumos');
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('productos');
    }
};
