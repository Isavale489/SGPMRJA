<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo gestionable de tipos de insumo (reemplaza el ENUM hardcodeado).
        Schema::create('tipo_insumo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // insumo.tipo: ENUM -> VARCHAR para admitir tipos dinámicos del catálogo.
        // Se conservan los valores existentes ('Tela', 'Hilo', ...).
        DB::statement("ALTER TABLE insumo MODIFY tipo VARCHAR(50) NOT NULL");

        // Seed: tipos base + cualquiera ya presente en datos. Se EXCLUYE 'Otro'
        // (el profesor no permite un tipo de insumo llamado "Otro").
        $base   = ['Tela', 'Hilo', 'Boton', 'Cierre', 'Etiqueta'];
        $usados = DB::table('insumo')->select('tipo')->distinct()->pluck('tipo')->all();
        $nombres = array_values(array_diff(
            array_unique(array_filter(array_merge($base, $usados))),
            ['Otro']
        ));
        $now = now();
        foreach ($nombres as $n) {
            DB::table('tipo_insumo')->insert([
                'nombre'     => $n,
                'activo'     => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE insumo MODIFY tipo ENUM('Tela','Hilo','Boton','Cierre','Etiqueta','Otro') NOT NULL");
        Schema::dropIfExists('tipo_insumo');
    }
};
