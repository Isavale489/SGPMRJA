<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta/edición de departamento. También lo usa el alta rápida de Empleados (jQuery). */
class GuardarDepartamentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nombre' => trim((string) $this->input('nombre'))]);
    }

    public function rules(): array
    {
        return [
            // unique incluye inhabilitados: un nombre del historial se restaura, no se duplica.
            'nombre' => ['required', 'string', 'min:3', 'max:100', Rule::unique('departamento', 'nombre')->ignore($this->route('departamento'))],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.unique' => 'Ya existe un departamento con este nombre.',
        ];
    }
}
