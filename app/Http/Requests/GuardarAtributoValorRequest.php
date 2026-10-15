<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Alta/edición de un valor de atributo. Nombre y código son únicos dentro del
 * atributo; el código solo se valida al crear (inmutable: forma el SKU).
 */
class GuardarAtributoValorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['nombre' => trim((string) $this->input('nombre'))]);
        if (! $this->route('valor')) {
            $this->merge(['codigo' => strtoupper(trim((string) $this->input('codigo')))]);
        }
    }

    public function rules(): array
    {
        $atributoId = $this->route('atributo')->id;
        $valor = $this->route('valor');
        $delAtributo = fn ($campo) => Rule::unique('atributo_valor', $campo)->where('atributo_id', $atributoId);
        $reglas = [
            'nombre' => ['required', 'string', 'min:1', 'max:80', $delAtributo('nombre')->ignore($valor)],
            'orden' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
        if (! $valor) {
            $reglas['codigo'] = ['required', 'string', 'min:1', 'max:8', 'regex:/^[A-Z0-9]+$/', $delAtributo('codigo')];
        }

        return $reglas;
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.unique' => $this->route('valor')
                ? 'Ya existe otro valor con este nombre en el atributo.'
                : 'Ya existe un valor con este nombre en el atributo.',
            'codigo.required' => 'El código es obligatorio.',
            'codigo.max' => 'El código admite hasta 8 caracteres.',
            'codigo.regex' => 'El código solo admite letras mayúsculas y números.',
            'codigo.unique' => 'Ya existe un valor con este código en el atributo.',
        ];
    }
}
