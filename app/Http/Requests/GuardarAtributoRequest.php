<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta/edición de atributo de confección. El código solo se valida al crear:
 * es inmutable porque forma parte del SKU (en la edición se ignora).
 */
class GuardarAtributoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    protected function prepareForValidation(): void
    {
        $descripcion = trim((string) $this->input('descripcion'));
        $this->merge([
            'nombre' => trim((string) $this->input('nombre')),
            'descripcion' => $descripcion === '' ? null : $descripcion,
        ]);
        if (! $this->route('atributo')) {
            $this->merge(['codigo' => strtoupper(trim((string) $this->input('codigo')))]);
        }
    }

    public function rules(): array
    {
        $atributo = $this->route('atributo');
        $reglas = [
            'nombre' => ['required', 'string', 'min:3', 'max:80', Rule::unique('atributo', 'nombre')->ignore($atributo)],
            'descripcion' => ['nullable', 'string', 'max:191'],
            'tipos_producto' => ['nullable', 'array'],
            'tipos_producto.*' => ['integer', 'exists:tipo_producto,id'],
        ];
        if (! $atributo) {
            $reglas['codigo'] = ['required', 'string', 'min:2', 'max:8', 'regex:/^[A-Z0-9]+$/', 'unique:atributo,codigo'];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'nombre.unique' => 'Ya existe un atributo con este nombre.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.min' => 'El código debe tener entre 2 y 8 caracteres.',
            'codigo.max' => 'El código debe tener entre 2 y 8 caracteres.',
            'codigo.unique' => 'Ya existe un atributo con este código.',
            'codigo.regex' => 'El código solo admite letras mayúsculas y números.',
        ];
    }
}
