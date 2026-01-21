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
            // Agregar tipo de proveedor (natural o jurídico)
            $table->enum('tipo_proveedor', ['natural', 'juridico'])->default('juridico')->after('id');

            // Agregar referencia a persona (solo para proveedores naturales)
            $table->unsignedBigInteger('persona_id')->nullable()->after('tipo_proveedor');
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('set null');
        });

        // Marcar todos los proveedores existentes como jurídicos
        DB::table('proveedor')->whereNull('tipo_proveedor')->update(['tipo_proveedor' => 'juridico']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedor', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn(['tipo_proveedor', 'persona_id']);
        });
    }
};
