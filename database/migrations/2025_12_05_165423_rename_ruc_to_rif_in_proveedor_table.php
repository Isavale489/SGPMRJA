<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proveedor', function (Blueprint $table) {
            $table->renameColumn('ruc', 'rif');
        });
    }

    public function down(): void
    {
        Schema::table('proveedor', function (Blueprint $table) {
            $table->renameColumn('rif', 'ruc');
        });
    }
};
