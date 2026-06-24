<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Persona;
use App\Models\Telefono;
use App\Models\Direccion;
use Illuminate\Support\Facades\DB;

class ClienteService
{
    /**
     * Crear un cliente con su persona, teléfono y dirección normalizados.
     *
     * @return int ID del cliente creado
     */
    public function crear(array $data): int
    {
        $clienteId = null;

        DB::transaction(function () use ($data, &$clienteId) {
            // Extraer prefijo y número del documento
            $documento = $data['documento'];
            $tipoDocumento = 'V-';
            $numeroDocumento = $documento;

            if (preg_match('/^(V-|J-|E-|G-)(.+)$/', $documento, $matches)) {
                $tipoDocumento = $matches[1];
                $numeroDocumento = $matches[2];
            }

            // Buscar si ya existe una persona con ese documento (ej: es empleado)
            $persona = Persona::where('documento_identidad', $numeroDocumento)->first();

            if ($persona) {
                // La persona ya existe — verificar que no sea ya un cliente
                if (Cliente::where('persona_id', $persona->id)->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'documento' => ['Este documento ya está registrado como cliente.'],
                    ]);
                }
                // Reutilizar la persona existente — sincronizar teléfonos/dirección
                $this->sincronizarTelefonos($persona, $data['telefonos'] ?? []);
                if (!empty($data['direccion']) || !empty($data['ciudad']) || !empty($data['estado_territorial'])) {
                    Direccion::create([
                        'persona_id' => $persona->id,
                        'direccion' => $data['direccion'] ?? '',
                        ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
                    ]);
                }
            } else {
                // Persona nueva — verificar unicidad de email antes de crear
                if (!empty($data['email']) && Persona::where('email', $data['email'])->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'email' => ['Este correo ya está registrado.'],
                    ]);
                }

                $persona = Persona::create([
                    'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                    'documento_identidad' => $numeroDocumento,
                    'tipo_documento' => $tipoDocumento,
                    'email' => $data['email'] ?? null,
                ]);

                // Crear teléfonos
                $this->sincronizarTelefonos($persona, $data['telefonos'] ?? []);

                // Crear dirección principal
                if (!empty($data['direccion']) || !empty($data['ciudad']) || !empty($data['estado_territorial'])) {
                    Direccion::create([
                        'persona_id' => $persona->id,
                        'direccion' => $data['direccion'] ?? '',
                        ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
                    ]);
                }
            }

            // Crear cliente
            $cliente = Cliente::create([
                'persona_id' => $persona->id,
                'tipo_cliente' => $data['tipo_cliente'],
                // 'estatus' ya no se edita: activo=1 por defecto. La inhabilitación se
                // maneja con SoftDeletes (deleted_at), igual que Empleados/Proveedores.
                'estatus' => $data['estatus'] ?? 1,
            ]);

            $clienteId = $cliente->id;
        });

        return $clienteId;
    }

    /**
     * Actualizar un cliente y sus datos normalizados.
     */
    public function actualizar(Cliente $cliente, array $data): void
    {
        DB::transaction(function () use ($data, $cliente) {
            // Actualizar persona (sin documento)
            if ($cliente->persona) {
                $cliente->persona->update([
                    'nombre' => trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
                    'email' => $data['email'] ?? null,
                ]);

                // Sincronizar el set completo de teléfonos
                $this->sincronizarTelefonos($cliente->persona, $data['telefonos'] ?? []);

                // Actualizar o crear dirección principal
                if (!empty($data['direccion']) || !empty($data['ciudad']) || !empty($data['estado_territorial'])) {
                    $direccionPrincipal = $cliente->persona->direccion;
                    if ($direccionPrincipal) {
                        $direccionPrincipal->update([
                            'direccion' => $data['direccion'] ?? '',
                            ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
                        ]);
                    } else {
                        Direccion::create([
                            'persona_id' => $cliente->persona->id,
                            'direccion' => $data['direccion'] ?? '',
                            ...Direccion::resolverUbicacion($data['estado_territorial'] ?? null, $data['ciudad'] ?? null),
                        ]);
                    }
                }
            }

            // Actualizar cliente
            $cliente->update([
                'tipo_cliente' => $data['tipo_cliente'],
                // 'estatus' ya no se edita aquí; la baja se hace con SoftDeletes
            ]);
        });
    }

    /**
     * Sincroniza el conjunto de teléfonos de la persona con el set recibido
     * (reemplazo completo). Garantiza exactamente un principal.
     *
     * @param array $telefonos lista de ['numero', 'tipo', 'es_principal']
     */
    private function sincronizarTelefonos(Persona $persona, array $telefonos): void
    {
        // Normalizar: descartar vacíos y asegurar un único principal
        $telefonos = array_values(array_filter($telefonos, fn ($t) => !empty($t['numero'])));
        if (empty($telefonos)) {
            return; // sin teléfonos: no se toca el set actual
        }

        $principalAsignado = false;
        foreach ($telefonos as $i => &$t) {
            $esPrincipal = !empty($t['es_principal']) && !$principalAsignado;
            $t['es_principal'] = $esPrincipal;
            if ($esPrincipal) {
                $principalAsignado = true;
            }
        }
        unset($t);
        if (!$principalAsignado) {
            $telefonos[0]['es_principal'] = true;
        }

        // Reemplazo completo del set (forceDelete evita acumular soft-deleted)
        $persona->telefonos()->forceDelete();
        foreach ($telefonos as $t) {
            Telefono::create([
                'persona_id' => $persona->id,
                'numero' => $t['numero'],
                'tipo' => $t['tipo'] ?? 'movil',
                'es_principal' => $t['es_principal'],
            ]);
        }
    }
}
