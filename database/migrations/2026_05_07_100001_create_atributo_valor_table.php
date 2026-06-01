<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atributo_valor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atributo_id')
                  ->constrained('atributo')
                  ->cascadeOnDelete();
            $table->string('nombre', 80);
            $table->string('codigo', 8);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['atributo_id', 'codigo']);
            $table->unique(['atributo_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atributo_valor');
    }
};
