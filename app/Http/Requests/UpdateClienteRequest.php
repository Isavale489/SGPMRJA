<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtener persona_id del cliente para la excepción de unicidad del email
        $cliente = \App\Models\Cliente::find($this->route('cliente'));
        $personaId = $cliente ? ($cliente->persona_id ?? 'NULL') : 'NULL';

        return [
            // `nombre` recibe el nombre (natural) o la razón social (jurídico/gubernamental),
            // por eso el regex admite números y signos comunes de razón social (. , & - ').
            'nombre' => 'required|string|min:2|max:200|regex:/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,&\'\-]+$/',
            'apellido' => 'nullable|string|max:100|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/',
            'tipo_cliente' => 'required|in:natural,juridico',
            'email' => 'nullable|string|email:rfc|max:255|unique:persona,email,' . $personaId,
            'telefonos' => 'required|array|min:1',
            'telefonos.*.numero' => ['required', 'string', 'regex:/^[0-9]{4}-[0-9]{7}$/'],
            'telefonos.*.tipo' => 'required|in:movil,casa,trabajo',
            'telefonos.*.es_principal' => 'required|boolean',
            'direccion' => 'nullable|string|max:500',
            'estado_territorial' => 'nullable|string|max:50',
            'ciudad' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.max' => 'El nombre o razón social no puede exceder los 200 caracteres.',
            'nombre.regex' => 'El nombre o razón social contiene caracteres no permitidos.',
            'apellido.max' => 'El apellido no puede exceder los 100 caracteres.',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios.',
            'tipo_cliente.required' => 'Debe seleccionar el tipo de cliente.',
            'tipo_cliente.in' => 'El tipo de cliente debe ser Natural o Jurídico.',
            'email.email' => 'El email debe ser una dirección de correo válida.',
            'email.unique' => 'Este email ya está registrado en el sistema.',
            'telefonos.required' => 'Agrega al menos un teléfono.',
            'telefonos.min' => 'Agrega al menos un teléfono.',
            'telefonos.*.numero.required' => 'El número de teléfono es obligatorio.',
            'telefonos.*.numero.regex' => 'El teléfono debe tener el formato 0424-1234567.',
            'telefonos.*.tipo.in' => 'El tipo de teléfono no es válido.',
            'direccion.max' => 'La dirección no puede exceder los 500 caracteres.',
            'estado_territorial.max' => 'El estado territorial no puede exceder los 50 caracteres.',
            'ciudad.max' => 'La ciudad no puede exceder los 100 caracteres.',
        ];
    }
}
