<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->renameColumn('codigo_prefijo', 'prefijo');
        });
    }

    public function down(): void
    {
        Schema::table('tipo_producto', function (Blueprint $table) {
            $table->renameColumn('prefijo', 'codigo_prefijo');
        });
    }
};
