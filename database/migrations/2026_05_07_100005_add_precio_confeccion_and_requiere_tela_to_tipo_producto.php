<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->decimal('precio_confeccion', 10, 2)->default(0)->after('descripcion');
            $table->boolean('requiere_tela')->default(true)->after('precio_confeccion');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->dropColumn(['precio_confeccion', 'requiere_tela']);
        });
    }
};
