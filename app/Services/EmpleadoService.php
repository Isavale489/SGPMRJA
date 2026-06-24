<?php

namespace App\Services;

use App\Models\Direccion;
use App\Models\Empleado;
use App\Models\Persona;
use App\Models\Telefono;
use Illuminate\Support\Facades\DB;

class EmpleadoService
{
    /**
     * Crear un nuevo empleado con persona, teléfono y dirección normalizados.
     */
    public function crear(array $data): int
    {
        return DB::transaction(function () use ($data) {
            // Buscar si ya existe una persona con ese documento (ej: es cliente)
            $persona = Persona::where('documento_identidad', $data['documento_identidad'])->first();

            if ($persona) {
                // La persona ya existe — verificar que no sea ya un empleado
                if (Empleado::where('persona_id', $persona->id)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'documento_identidad' => ['Este documento ya pertenece a un empleado registrado.'],
                    ]);
                }
                // Reutilizar la persona existente — agregar teléfono/dirección si se proveyeron
                $this->crearTelefono($persona->id, $data);
                $this->crearDireccion($persona->id, $data);
            } else {
                // Persona nueva — verificar unicidad de email antes de crear
                if (!empty($data['email']) && Persona::where('email', $data['email'])->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => ['Este correo ya está registrado.'],
                    ]);
                }

                $persona = Persona::create([
                    'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                    'documento_identidad' => $data['documento_identidad'],
                    'tipo_documento' => $data['tipo_documento'],
                    'email' => $data['email'] ?? null,
                ]);

                $this->crearTelefono($persona->id, $data);
                $this->crearDireccion($persona->id, $data);
            }

            // Auto-generar código de empleado si no se proporciona.
            // Importante: incluir trashed en el cálculo, porque la UNIQUE constraint
            // de la DB cuenta los soft-deleted aunque el modelo los oculte.
            $codigoEmpleado = $data['codigo_empleado'] ?? null;
            if (!$codigoEmpleado) {
                $ultimoCodigo = Empleado::withTrashed()->max('codigo_empleado');
                $numero = $ultimoCodigo ? ((int) substr($ultimoCodigo, 4) + 1) : 1;

                // Defensa extra contra colisiones (registros restaurados, etc.)
                do {
                    $codigoEmpleado = 'EMP-' . str_pad($numero, 3, '0', STR_PAD_LEFT);
                    $existe = Empleado::withTrashed()->where('codigo_empleado', $codigoEmpleado)->exists();
                    $numero++;
                } while ($existe);
            }

            $empleado = Empleado::create([
                'persona_id'       => $persona->id,
                'codigo_empleado'  => $codigoEmpleado,
                'fecha_ingreso'    => $data['fecha_ingreso'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero'           => $data['genero'] ?? null,
                'cargo_id'         => $data['cargo_id'],
                'departamento_id'  => $data['departamento_id'],
                // 'estado' (laboral) ya no se edita: activo=1 por defecto. La inhabilitación
                // se maneja con SoftDeletes (deleted_at), igual que Clientes/Proveedores.
                'estado'          => $data['estado'] ?? 1,
            ]);

            return $empleado->id;
        });
    }

    /**
     * Actualizar un empleado existente con persona, teléfono y dirección.
     */
    public function actualizar(Empleado $empleado, array $data): void
    {
        DB::transaction(function () use ($empleado, $data) {
            $persona = $empleado->persona;

            $persona->update([
                'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                // Documento inmutable: si no viene (campo deshabilitado en edición) se conserva el actual
                'documento_identidad' => $data['documento_identidad'] ?? $persona->documento_identidad,
                'tipo_documento' => $data['tipo_documento'] ?? $persona->tipo_documento,
                'email' => $data['email'] ?? null,
            ]);

            $this->actualizarTelefono($persona, $data);
            $this->actualizarDireccion($persona, $data);

            $empleado->update([
                'codigo_empleado'  => $data['codigo_empleado'],
                'fecha_ingreso'    => $data['fecha_ingreso'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero'           => $data['genero'] ?? null,
                'cargo_id'         => $data['cargo_id'],
                'departamento_id'  => $data['departamento_id'],
                // 'estado' (laboral) ya no se edita aquí; la baja se hace con SoftDeletes
            ]);
        });
    }

    private function crearTelefono(int $personaId, array $data): void
    {
        if (!empty($data['telefono'])) {
            Telefono::create([
                'persona_id' => $personaId,
                'numero' => $data['telefono'],
                'tipo' => 'movil',
                'es_principal' => true,
            ]);
        }
    }

    private function crearDireccion(int $personaId, array $data): void
    {
        if (!empty($data['direccion']) || !empty($data['ciudad'])) {
            Direccion::create([
                'persona_id' => $personaId,
                'direccion' => $data['direccion'] ?? '',
                ...Direccion::resolverUbicacion($data['estado_geografico'] ?? null, $data['ciudad'] ?? null),
                'tipo' => 'casa',
                'es_principal' => true,
            ]);
        }
    }

    private function actualizarTelefono(Persona $persona, array $data): void
    {
        if (!empty($data['telefono'])) {
            $telefonoPrincipal = $persona->telefonos()->where('es_principal', true)->first();
            if ($telefonoPrincipal) {
                $telefonoPrincipal->update(['numero' => $data['telefono']]);
            } else {
                Telefono::create([
                    'persona_id' => $persona->id,
                    'numero' => $data['telefono'],
                    'tipo' => 'movil',
                    'es_principal' => true,
                ]);
            }
        }
    }

    private function actualizarDireccion(Persona $persona, array $data): void
    {
        if (!empty($data['direccion']) || !empty($data['ciudad'])) {
            $direccionPrincipal = $persona->direcciones()->where('es_principal', true)->first();
            if ($direccionPrincipal) {
                $direccionPrincipal->update([
                    'direccion' => $data['direccion'] ?? '',
                    ...Direccion::resolverUbicacion($data['estado_geografico'] ?? null, $data['ciudad'] ?? null),
                ]);
            } else {
                Direccion::create([
                    'persona_id' => $persona->id,
                    'direccion' => $data['direccion'] ?? '',
                    ...Direccion::resolverUbicacion($data['estado_geografico'] ?? null, $data['ciudad'] ?? null),
                    'tipo' => 'casa',
                    'es_principal' => true,
                ]);
            }
        }
    }
}
