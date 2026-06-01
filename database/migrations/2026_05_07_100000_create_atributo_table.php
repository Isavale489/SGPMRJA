<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atributo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80)->unique();
            $table->string('codigo', 8)->unique();
            $table->string('descripcion', 191)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atributo');
    }
};
