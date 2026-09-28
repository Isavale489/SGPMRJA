<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Alta/edición de color. También lo usa el alta rápida de Cotizaciones (jQuery). */
class GuardarColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    protected function prepareForValidation(): void
    {
        $grupo = trim((string) $this->input('grupo'));
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'hex_referencial' => strtoupper(trim((string) $this->input('hex_referencial'))),
            'grupo' => $grupo === '' ? null : $grupo,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'min:2', 'max:100', Rule::unique('color', 'nombre')->ignore($this->route('color'))],
            'hex_referencial' => ['required', 'string', 'regex:/^#[0-9A-F]{6}$/'],
            'grupo' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
            'nombre.unique' => 'Ya existe un color con este nombre.',
            'hex_referencial.required' => 'El color HEX es obligatorio.',
            'hex_referencial.regex' => 'El color HEX debe tener el formato #RRGGBB.',
        ];
    }
}
