<?php

namespace App\Services;

use App\Models\Direccion;
use App\Models\Persona;
use App\Models\Proveedor;
use App\Models\Telefono;
use Illuminate\Support\Facades\DB;

class ProveedorService
{
    /**
     * Crear un proveedor natural (persona física).
     */
    public function crearNatural(array $data): Proveedor
    {
        return DB::transaction(function () use ($data) {
            $persona = Persona::create([
                'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                'tipo_documento' => $data['tipo_documento'],
                'documento_identidad' => $data['documento_identidad'],
                'email' => $data['email'],
            ]);

            $this->crearTelefono($persona->id, $data['telefono']);

            if (!empty($data['direccion'])) {
                $this->crearDireccion($persona->id, $data);
            }

            return Proveedor::create([
                'tipo_proveedor' => 'natural',
                'persona_id' => $persona->id,
                'estado' => $data['estado'] ?? true,
            ]);
        });
    }

    /**
     * Crear un proveedor jurídico (empresa) — normalizado al sistema Persona.
     */
    public function crearJuridico(array $data): Proveedor
    {
        return DB::transaction(function () use ($data) {
            // Parsear RIF: 'J-1231321' → tipo_documento='J-', documento_identidad='1231321'
            $rif = $data['rif'];
            $tipoDoc = 'J-';
            $docId = $rif;
            if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $rif, $matches)) {
                $tipoDoc = $matches[1];
                $docId = $matches[2];
            }

            $persona = Persona::create([
                'nombre' => $data['razon_social'],
                'tipo_documento' => $tipoDoc,
                'documento_identidad' => $docId,
                'email' => $data['email'],
            ]);

            $this->crearTelefono($persona->id, $data['telefono']);

            if (!empty($data['direccion'])) {
                $this->crearDireccion($persona->id, $data);
            }

            return Proveedor::create([
                'tipo_proveedor' => 'juridico',
                'persona_id' => $persona->id,
                'contacto' => $data['contacto'] ?? null,
                'telefono_contacto' => $data['telefono_contacto'] ?? null,
                'estado' => $data['estado'] ?? true,
            ]);
        });
    }

    /**
     * Actualizar un proveedor natural existente.
     */
    public function actualizarNatural(Proveedor $proveedor, array $data): void
    {
        DB::transaction(function () use ($proveedor, $data) {
            if ($proveedor->persona_id && $proveedor->persona) {
                $proveedor->persona->update([
                    'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                    'tipo_documento' => $data['tipo_documento'],
                    'documento_identidad' => $data['documento_identidad'],
                    'email' => $data['email'],
                ]);

                $this->actualizarTelefono($proveedor->persona, $data['telefono']);
                $this->actualizarDireccion($proveedor->persona, $data);
            } else {
                // Convertir de jurídico a natural: crear persona
                $persona = Persona::create([
                    'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                    'tipo_documento' => $data['tipo_documento'],
                    'documento_identidad' => $data['documento_identidad'],
                    'email' => $data['email'],
                ]);

                $this->crearTelefono($persona->id, $data['telefono']);

                if (!empty($data['direccion'])) {
                    $this->crearDireccion($persona->id, $data);
                }

                $proveedor->persona_id = $persona->id;
            }

            $proveedor->tipo_proveedor = 'natural';
            $proveedor->estado = $data['estado'] ?? true;
            $proveedor->save();
        });
    }

    /**
     * Actualizar un proveedor jurídico existente — a través de Persona.
     */
    public function actualizarJuridico(Proveedor $proveedor, array $data): void
    {
        DB::transaction(function () use ($proveedor, $data) {
            $persona = $proveedor->persona;

            if ($persona) {
                $persona->update([
                    'nombre' => $data['razon_social'],
                    'email' => $data['email'],
                ]);

                $this->actualizarTelefono($persona, $data['telefono']);
                $this->actualizarDireccion($persona, $data);
            }

            $proveedor->update([
                'contacto' => $data['contacto'] ?? null,
                'telefono_contacto' => $data['telefono_contacto'] ?? null,
                'estado' => $data['estado'] ?? true,
            ]);
        });
    }

    private function crearTelefono(int $personaId, string $numero): void
    {
        Telefono::create([
            'persona_id' => $personaId,
            'numero' => $numero,
            'tipo' => 'movil',
            'es_principal' => true,
        ]);
    }

    private function crearDireccion(int $personaId, array $data): void
    {
        Direccion::create([
            'persona_id' => $personaId,
            'direccion' => $data['direccion'],
            ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
            'es_principal' => true,
        ]);
    }

    private function actualizarTelefono(Persona $persona, string $numero): void
    {
        $telefonoPrincipal = $persona->telefonos()->where('es_principal', true)->first();
        if ($telefonoPrincipal) {
            $telefonoPrincipal->update(['numero' => $numero]);
        } else {
            Telefono::create([
                'persona_id' => $persona->id,
                'numero' => $numero,
                'tipo' => 'movil',
                'es_principal' => true,
            ]);
        }
    }

    private function actualizarDireccion(Persona $persona, array $data): void
    {
        if (!empty($data['direccion'])) {
            $direccionPrincipal = $persona->direcciones()->where('es_principal', true)->first();
            if ($direccionPrincipal) {
                $direccionPrincipal->update([
                    'direccion' => $data['direccion'],
                    ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
                ]);
            } else {
                Direccion::create([
                    'persona_id' => $persona->id,
                    'direccion' => $data['direccion'],
                    ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
                    'es_principal' => true,
                ]);
            }
        }
    }
}
