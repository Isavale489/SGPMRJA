<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proveedor', function (Blueprint $table) {
            // Hacer campos nullable para proveedores naturales
            $table->string('razon_social', 100)->nullable()->change();
            $table->string('rif', 15)->nullable()->change();
            $table->string('contacto', 100)->nullable()->change();
            $table->string('telefono_contacto', 20)->nullable()->change();
            $table->string('telefono', 20)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedor', function (Blueprint $table) {
            $table->string('razon_social', 100)->nullable(false)->change();
            $table->string('rif', 15)->nullable(false)->change();
        });
    }
};
