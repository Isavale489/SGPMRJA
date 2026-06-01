<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            $table->string('codigo', 8)->nullable()->unique()->after('nombre');
        });
    }

    public function down(): void
    {
        Schema::table('insumo', function (Blueprint $table) {
            $table->dropUnique(['codigo']);
            $table->dropColumn('codigo');
        });
    }
};
