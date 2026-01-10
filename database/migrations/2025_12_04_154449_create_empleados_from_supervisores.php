<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Crear empleados para usuarios con rol Supervisor
        $supervisores = DB::table('user')->where('role', 'Supervisor')->get();

        $codigoCounter = 1;

        foreach ($supervisores as $supervisor) {
            if ($supervisor->persona_id) {
                DB::table('empleado')->insert([
                    'persona_id' => $supervisor->persona_id,
                    'codigo_empleado' => 'EMP-' . str_pad($codigoCounter, 3, '0', STR_PAD_LEFT),
                    'fecha_ingreso' => $supervisor->created_at ?? now(),
                    'cargo' => 'Supervisor de Producción',
                    'departamento' => 'Producción',
                    'estado' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $codigoCounter++;
            }
        }
    }

    public function down(): void
    {
        DB::table('empleado')->truncate();
    }
};
