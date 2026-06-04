<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            $table->foreignId('origen_compra_id')
                ->nullable()
                ->after('estado')
                ->constrained('compra')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('compra', function (Blueprint $table) {
            $table->dropForeign(['origen_compra_id']);
            $table->dropColumn('origen_compra_id');
        });
    }
};
