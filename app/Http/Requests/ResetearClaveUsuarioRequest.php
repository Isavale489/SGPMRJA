<?php

namespace App\Http\Requests;

use App\Rules\ContrasenaSegura;
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
            // Tipo y largo los valida la regla (tope de 72 por bcrypt).
            'password' => ['required', 'confirmed', new ContrasenaSegura],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'La contraseña temporal es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ];
    }
}
