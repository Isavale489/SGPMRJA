<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->after('cantidad');
            $table->boolean('lleva_bordado')->default(false)->after('descripcion');
            $table->string('nombre_logo')->nullable()->after('lleva_bordado');
            $table->string('ubicacion_logo')->nullable()->after('nombre_logo');
            $table->integer('cantidad_logo')->nullable()->after('ubicacion_logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_pedidos', function (Blueprint $table) {
            $table->dropColumn(['descripcion', 'lleva_bordado', 'nombre_logo', 'ubicacion_logo', 'cantidad_logo']);
        });
    }
};
