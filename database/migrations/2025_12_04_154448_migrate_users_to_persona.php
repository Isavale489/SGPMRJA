<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Migrar usuarios existentes a tabla persona
        $users = DB::table('user')->get();

        foreach ($users as $user) {
            // Separar nombre en nombre y apellido
            $nameParts = explode(' ', $user->name, 2);
            $nombre = $nameParts[0];
            $apellido = $nameParts[1] ?? 'Sistema';

            // Generar documento de identidad temporal basado en el ID
            $documento = 'V-' . str_pad($user->id, 8, '0', STR_PAD_LEFT);

            // Crear registro en persona
            $personaId = DB::table('persona')->insertGetId([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'documento_identidad' => $documento,
                'tipo_documento' => 'V-',
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]);

            // Actualizar user con persona_id
            DB::table('user')
                ->where('id', $user->id)
                ->update(['persona_id' => $personaId]);
        }
    }

    public function down(): void
    {
        // Limpiar relaciones
        DB::table('user')->update(['persona_id' => null]);

        // Eliminar personas creadas
        DB::table('persona')->truncate();
    }
};
