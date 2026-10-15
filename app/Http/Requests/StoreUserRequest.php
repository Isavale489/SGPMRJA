<?php

namespace App\Http\Requests;

use App\Rules\ContrasenaSegura;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:user',
            // Tipo y largo los valida la regla (tope de 72 por bcrypt).
            'password' => ['required', 'confirmed', new ContrasenaSegura],
            'avatar'   => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_id'  => 'required|exists:rol,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'El nombre es obligatorio.',
            'email.required'     => 'El email es obligatorio.',
            'email.email'        => 'Ingrese un email válido.',
            'email.unique'       => 'Este correo ya está registrado.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role_id.required'   => 'El rol es obligatorio.',
            'role_id.exists'     => 'El rol seleccionado no es válido.',
        ];
    }
}
