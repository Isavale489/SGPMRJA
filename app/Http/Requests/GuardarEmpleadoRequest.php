<?php

namespace App\Http\Requests;

use App\Models\Cargo;
use App\Models\Empleado;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Alta y edición de empleados.
 *
 * `apellido` es opcional: desde jun-2026 `persona.nombre` guarda el nombre
 * completo (el service une nombre y apellido si llegan separados). Exigirlo
 * obligaba, al editar, a escribir de nuevo el apellido, que se duplicaba
 * ("Rosa Linares Linares"), e impedía vincular a una persona que ya era cliente.
 *
 * En edición el documento es inmutable (no se valida ni se reescribe) y el
 * código de empleado se conserva.
 */
class GuardarEmpleadoRequest extends FormRequest
{
    private const LETRAS = '/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/';

    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    private function empleado(): ?Empleado
    {
        $id = $this->route('empleado');

        return $id ? Empleado::find($id) : null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'apellido' => trim((string) $this->input('apellido')) ?: null,
            'email' => trim((string) $this->input('email')) ?: null,
        ]);
    }

    public function rules(): array
    {
        $empleado = $this->empleado();
        $reglas = [
            'nombre' => ['required', 'string', 'min:2', 'max:100', 'regex:'.self::LETRAS],
            'apellido' => ['nullable', 'string', 'min:2', 'max:100', 'regex:'.self::LETRAS],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'telefonos' => ['required', 'array', 'min:1', 'max:3'],
            'telefonos.*.numero' => ['required', 'string', 'regex:/^[0-9]{4}-[0-9]{7}$/'],
            'telefonos.*.tipo' => ['required', 'in:movil,casa,trabajo'],
            'telefonos.*.es_principal' => ['required', 'boolean'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'estado_geografico' => ['nullable', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:-18 years'],
            'genero' => ['nullable', 'in:M,F'],
            'fecha_ingreso' => ['required', 'date', 'before_or_equal:today'],
            'departamento_id' => ['required', 'exists:departamento,id'],
            'cargo_id' => ['required', 'exists:cargo,id'],
        ];

        if ($empleado) {
            $reglas['email'][] = Rule::unique('persona', 'email')->ignore($empleado->persona_id);
            $reglas['codigo_empleado'] = ['required', 'string', 'max:50', Rule::unique('empleado', 'codigo_empleado')->ignore($empleado->id)];
        } else {
            $reglas['documento_identidad'] = ['required', 'string', 'min:6', 'max:15', 'regex:/^[0-9]+$/'];
            $reglas['tipo_documento'] = ['required', 'in:V-,E-,J-,G-'];
            $reglas['codigo_empleado'] = ['nullable', 'string', 'max:50', 'unique:empleado,codigo_empleado'];
        }

        return $reglas;
    }

    /** El cargo tiene que ser del departamento elegido. */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->hasAny(['cargo_id', 'departamento_id'])) {
                return;
            }
            $cargo = Cargo::find($this->input('cargo_id'));
            if ($cargo && (int) $cargo->departamento_id !== (int) $this->input('departamento_id')) {
                $validator->errors()->add('cargo_id', 'El cargo seleccionado no pertenece al departamento elegido.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres',
            'nombre.regex' => 'El nombre solo puede contener letras y espacios',
            'apellido.min' => 'El apellido debe tener al menos 2 caracteres',
            'apellido.regex' => 'El apellido solo puede contener letras y espacios',
            'documento_identidad.required' => 'El documento de identidad es obligatorio',
            'documento_identidad.min' => 'El documento debe tener al menos 6 dígitos',
            'documento_identidad.regex' => 'El documento solo puede contener números',
            'tipo_documento.required' => 'Debe seleccionar el tipo de documento',
            'email.email' => 'El email debe ser una dirección válida',
            'email.unique' => 'Este email ya está registrado',
            'telefonos.required' => 'Agrega al menos un teléfono.',
            'telefonos.min' => 'Agrega al menos un teléfono.',
            'telefonos.max' => 'Máximo 3 teléfonos por persona.',
            'telefonos.*.numero.required' => 'El número de teléfono es obligatorio.',
            'telefonos.*.numero.regex' => 'El teléfono debe tener el formato 0424-1234567.',
            'fecha_nacimiento.before' => 'El empleado debe ser mayor de 18 años',
            'fecha_ingreso.required' => 'La fecha de ingreso es obligatoria',
            'fecha_ingreso.before_or_equal' => 'La fecha de ingreso no puede ser futura',
            'departamento_id.required' => 'El departamento es obligatorio',
            'departamento_id.exists' => 'El departamento seleccionado no es válido',
            'cargo_id.required' => 'El cargo es obligatorio',
            'cargo_id.exists' => 'El cargo seleccionado no es válido',
        ];
    }
}
