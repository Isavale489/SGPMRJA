<?php

namespace App\Http\Requests;

use App\Models\Persona;
use App\Models\Proveedor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta y edición de proveedores (natural o jurídico).
 *
 * Dos clientes usan este contrato: la página Inertia de Proveedores y el alta
 * rápida del wizard de Compras (jQuery). Forma del payload:
 *   jurídico → rif ("J-40123456"), razon_social, contacto, telefono_contacto
 *   natural  → tipo_documento ("V-"), documento_identidad, nombre [, apellido]
 *   ambos    → tipo_proveedor, email, direccion, telefonos[], estado_territorial, ciudad
 *
 * `apellido` es opcional: desde jun-2026 `persona.nombre` guarda el nombre
 * completo. Compras aún envía nombre y apellido por separado (se unen al guardar).
 */
class GuardarProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la autorización la hace el middleware CheckPermiso
    }

    private function proveedor(): ?Proveedor
    {
        $p = $this->route('proveedor');

        return $p instanceof Proveedor ? $p : ($p ? Proveedor::find($p) : null);
    }

    private function esJuridico(): bool
    {
        return $this->input('tipo_proveedor', $this->proveedor()?->tipo_proveedor ?? 'juridico') !== 'natural';
    }

    public function rules(): array
    {
        $personaId = $this->proveedor()?->persona_id;
        $emailUnico = Rule::unique('persona', 'email')->ignore($personaId);

        $comunes = [
            'tipo_proveedor' => ['required', 'in:natural,juridico'],
            'telefonos' => ['required', 'array', 'min:1', 'max:3'],
            'telefonos.*.numero' => ['required', 'string', 'regex:/^[0-9]{4}-[0-9]{7}$/'],
            'telefonos.*.tipo' => ['required', 'in:movil,casa,trabajo'],
            'telefonos.*.es_principal' => ['required', 'boolean'],
            'estado_territorial' => ['nullable', 'string', 'max:50'],
            'ciudad' => ['nullable', 'string', 'max:100'],
        ];

        if ($this->esJuridico()) {
            return $comunes + [
                // El RIF es inmutable: en edición se ignora (el service no lo cambia).
                'rif' => $this->proveedor() ? ['nullable'] : ['required', 'string', 'regex:/^[VEJG]-[0-9]{5,9}$/'],
                'razon_social' => ['required', 'string', 'min:2', 'max:100'],
                'direccion' => ['required', 'string', 'min:5', 'max:200'],
                'email' => ['required', 'email', 'max:100', $emailUnico],
                'contacto' => ['nullable', 'string', 'max:100'],
                'telefono_contacto' => ['nullable', 'string', 'regex:/^0[0-9]{3}-[0-9]{7}$/'],
            ];
        }

        return $comunes + [
            'tipo_documento' => ['required', 'in:V-,E-,J-,G-'],
            'documento_identidad' => ['required', 'string', 'regex:/^[0-9]{6,9}$/'],
            'nombre' => ['required', 'string', 'min:2', 'max:100'],
            'apellido' => ['nullable', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'min:5', 'max:255'],
            'email' => ['required', 'email', 'max:255', $emailUnico],
        ];
    }

    /**
     * Unicidad del documento sobre (tipo_documento, documento_identidad), igual
     * que el índice único persona_tipo_doc_documento_unique. La regla anterior
     * comparaba el RIF con prefijo contra el número sin prefijo: nunca detectaba
     * el duplicado y el índice lo frenaba con un 500.
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            [$campo, $prefijo, $numero] = $this->esJuridico()
                ? ['rif', substr((string) $this->input('rif'), 0, 2), substr((string) $this->input('rif'), 2)]
                : ['documento_identidad', $this->input('tipo_documento'), $this->input('documento_identidad')];

            if ($this->esJuridico() && $this->proveedor()) {
                return; // RIF inmutable en edición
            }

            $existe = Persona::where('tipo_documento', $prefijo)
                ->where('documento_identidad', $numero)
                ->when($this->proveedor()?->persona_id, fn ($q, $id) => $q->where('id', '!=', $id))
                ->exists();

            if ($existe) {
                $validator->errors()->add($campo, 'Este documento ya está registrado.');
            }
        }];
    }

    public function attributes(): array
    {
        return [
            'rif' => 'RIF',
            'razon_social' => 'razón social',
            'documento_identidad' => 'documento',
            'tipo_documento' => 'tipo de documento',
            'nombre' => 'nombre',
            'direccion' => 'dirección',
            'email' => 'correo electrónico',
            'contacto' => 'persona de contacto',
            'telefono_contacto' => 'teléfono de contacto',
            'estado_territorial' => 'estado',
            'ciudad' => 'municipio',
        ];
    }

    public function messages(): array
    {
        return [
            'telefonos.required' => 'Agrega al menos un teléfono.',
            'telefonos.min' => 'Agrega al menos un teléfono.',
            'telefonos.max' => 'Máximo 3 teléfonos por persona.',
            'telefonos.*.numero.required' => 'El número de teléfono es obligatorio.',
            'telefonos.*.numero.regex' => 'El teléfono debe tener el formato 0424-1234567.',
            'telefonos.*.tipo.in' => 'El tipo de teléfono no es válido.',
            'rif.regex' => 'El RIF debe tener el formato J-12345678.',
            'documento_identidad.regex' => 'El documento debe tener entre 6 y 9 dígitos.',
            'telefono_contacto.regex' => 'El teléfono de contacto debe tener el formato 0424-1234567.',
            'email.unique' => 'Este correo ya está registrado.',
        ];
    }
}
