<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Elimina las columnas telefono, direccion y ciudad de la tabla persona
     * ya que estos datos ahora están normalizados en las tablas telefono y direccion.
     */
    public function up(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            // Eliminar columnas redundantes (datos ya migrados a tablas normalizadas)
            $columnsToRemove = ['telefono', 'direccion', 'ciudad'];

            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('persona', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     * Restaura las columnas en caso de rollback (sin datos).
     */
    public function down(): void
    {
        Schema::table('persona', function (Blueprint $table) {
            if (!Schema::hasColumn('persona', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('email');
            }
            if (!Schema::hasColumn('persona', 'direccion')) {
                $table->string('direccion', 255)->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('persona', 'ciudad')) {
                $table->string('ciudad', 100)->nullable()->after('direccion');
            }
        });
    }
};
