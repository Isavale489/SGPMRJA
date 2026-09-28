<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Contraseña temporal que asigna el administrador. El usuario la cambia en su
 * próximo inicio de sesión (password_reset_by_admin). La confirmación antes
 * solo la revisaba el navegador.
 */
class ResetearClaveUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // CheckPermiso
    }

    public function rules(): array
    {
        return [
            'password' => ['required', 'string', 'min:8', 'max:191', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'La contraseña temporal es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
