<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Hace nullable las columnas material y talla ya que son opcionales
     * en el catálogo de productos base.
     */
    public function up(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('material')->nullable()->change();
            $table->string('talla')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('producto', function (Blueprint $table) {
            $table->string('material')->nullable(false)->change();
            $table->string('talla')->nullable(false)->change();
        });
    }
};
