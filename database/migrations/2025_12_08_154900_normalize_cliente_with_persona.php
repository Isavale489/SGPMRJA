<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     * Normaliza la tabla cliente para usar persona como tabla madre
     */
    public function up(): void
    {
        // Paso 1: Agregar columna persona_id a cliente
        Schema::table('cliente', function (Blueprint $table) {
            $table->unsignedBigInteger('persona_id')->nullable()->after('id');
        });

        // Paso 2: Migrar datos existentes de cliente a persona
        $clientes = DB::table('cliente')->get();

        foreach ($clientes as $cliente) {
            // Extraer prefijo y número del documento
            $documento = $cliente->documento ?? '';
            $tipoDocumento = 'V-';
            $numeroDocumento = $documento;

            if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $documento, $matches)) {
                $tipoDocumento = $matches[1];
                $numeroDocumento = $matches[2];
            }

            // Verificar si ya existe una persona con este documento O email
            $personaExistente = null;

            // Primero buscar por documento si existe
            if (!empty($numeroDocumento)) {
                $personaExistente = DB::table('persona')
                    ->where('documento_identidad', $numeroDocumento)
                    ->first();
            }

            // Si no encontró por documento, buscar por email
            if (!$personaExistente && !empty($cliente->email)) {
                $personaExistente = DB::table('persona')
                    ->where('email', $cliente->email)
                    ->first();
            }

            if ($personaExistente) {
                // Si ya existe, usar ese persona_id
                DB::table('cliente')
                    ->where('id', $cliente->id)
                    ->update(['persona_id' => $personaExistente->id]);
            } else {
                // Crear nueva persona con los datos del cliente
                $personaId = DB::table('persona')->insertGetId([
                    'nombre' => $cliente->nombre ?? 'Sin nombre',
                    'apellido' => '', // Cliente no tiene apellido separado
                    'documento_identidad' => $numeroDocumento ?: 'SIN-DOC-' . $cliente->id,
                    'tipo_documento' => $tipoDocumento,
                    'email' => $cliente->email,
                    'telefono' => $cliente->telefono,
                    'direccion' => $cliente->direccion,
                    'ciudad' => $cliente->ciudad,
                    'estado_persona' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Actualizar cliente con el persona_id
                DB::table('cliente')
                    ->where('id', $cliente->id)
                    ->update(['persona_id' => $personaId]);
            }
        }

        // Paso 3: Hacer persona_id NOT NULL y agregar FK
        Schema::table('cliente', function (Blueprint $table) {
            $table->unsignedBigInteger('persona_id')->nullable(false)->change();
            $table->foreign('persona_id')->references('id')->on('persona')->onDelete('restrict');
        });

        // Paso 4: Eliminar columnas redundantes
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropColumn(['nombre', 'email', 'telefono', 'documento', 'direccion', 'ciudad']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurar columnas eliminadas
        Schema::table('cliente', function (Blueprint $table) {
            $table->string('nombre')->nullable()->after('persona_id');
            $table->string('email')->nullable()->after('tipo_cliente');
            $table->string('telefono', 30)->nullable()->after('email');
            $table->string('documento', 50)->nullable()->after('telefono');
            $table->string('direccion')->nullable()->after('documento');
            $table->string('ciudad', 100)->nullable()->after('direccion');
        });

        // Migrar datos de vuelta desde persona a cliente
        DB::statement("
            UPDATE cliente c
            INNER JOIN persona p ON c.persona_id = p.id
            SET c.nombre = p.nombre,
                c.email = p.email,
                c.telefono = p.telefono,
                c.documento = CONCAT(p.tipo_documento, p.documento_identidad),
                c.direccion = p.direccion,
                c.ciudad = p.ciudad
        ");

        // Eliminar FK y columna persona_id
        Schema::table('cliente', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
        });
    }
};
