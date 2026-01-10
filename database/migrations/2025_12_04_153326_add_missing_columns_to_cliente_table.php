<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente', 'nombre')) {
                $table->string('nombre');
            }
            if (!Schema::hasColumn('cliente', 'tipo_cliente')) {
                $table->enum('tipo_cliente', ['natural', 'juridico'])->default('natural');
            }
            if (!Schema::hasColumn('cliente', 'email')) {
                $table->string('email')->nullable();
            }
            if (!Schema::hasColumn('cliente', 'telefono')) {
                $table->string('telefono', 30)->nullable();
            }
            if (!Schema::hasColumn('cliente', 'documento')) {
                $table->string('documento', 50)->nullable();
            }
            if (!Schema::hasColumn('cliente', 'direccion')) {
                $table->text('direccion')->nullable();
            }
            if (!Schema::hasColumn('cliente', 'ciudad')) {
                $table->string('ciudad', 100)->nullable();
            }
            if (!Schema::hasColumn('cliente', 'estado')) {
                $table->boolean('estado')->default(1);
            }
            if (!Schema::hasColumn('cliente', 'deleted_at')) {
                $table->softDeletes();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropColumn([
                'nombre',
                'tipo_cliente',
                'email',
                'telefono',
                'documento',
                'direccion',
                'ciudad',
                'estado',
                'deleted_at'
            ]);
        });
    }
};
