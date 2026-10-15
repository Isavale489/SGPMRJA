<?php

namespace App\Http\Requests;

use App\Models\Cargo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/** Alta/edición de cargo. También lo usa el alta rápida de Empleados (jQuery). */
class GuardarCargoRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'min:3', 'max:100'],
            'departamento_id' => ['required', 'exists:departamento,id'],
        ];
    }

    /** El nombre es único dentro de su departamento (sin distinguir mayúsculas). */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $existe = Cargo::whereRaw('LOWER(nombre) = ?', [mb_strtolower($this->input('nombre'))])
                ->where('departamento_id', $this->input('departamento_id'))
                ->when($this->route('cargo'), fn ($q, $cargo) => $q->where('id', '!=', $cargo->id))
                ->exists();

            if ($existe) {
                $validator->errors()->add('nombre', 'Ya existe un cargo con este nombre en el departamento seleccionado.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
            'departamento_id.required' => 'Debe seleccionar un departamento.',
            'departamento_id.exists' => 'El departamento no es válido.',
        ];
    }
}
