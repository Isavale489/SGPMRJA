<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Migra los datos existentes de telefono y direccion de la tabla persona
     * a las nuevas tablas normalizadas.
     */
    public function up(): void
    {
        // Migrar teléfonos existentes
        $personas = DB::table('persona')
            ->whereNotNull('telefono')
            ->where('telefono', '!=', '')
            ->get(['id', 'telefono']);

        foreach ($personas as $persona) {
            DB::table('telefono')->insert([
                'persona_id' => $persona->id,
                'numero' => $persona->telefono,
                'tipo' => 'movil',
                'es_principal' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Migrar direcciones existentes
        $personasConDireccion = DB::table('persona')
            ->where(function ($query) {
                $query->whereNotNull('direccion')
                    ->where('direccion', '!=', '');
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('ciudad')
                    ->where('ciudad', '!=', '');
            })
            ->get(['id', 'direccion', 'ciudad']);

        foreach ($personasConDireccion as $persona) {
            if (!empty($persona->direccion) || !empty($persona->ciudad)) {
                DB::table('direccion')->insert([
                    'persona_id' => $persona->id,
                    'direccion' => $persona->direccion ?? '',
                    'ciudad' => $persona->ciudad,
                    'tipo' => 'casa',
                    'es_principal' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     * No se pueden restaurar los datos eliminados de las nuevas tablas.
     */
    public function down(): void
    {
        // Vaciar las tablas (los datos se perderán en rollback)
        DB::table('telefono')->truncate();
        DB::table('direccion')->truncate();
    }
};
