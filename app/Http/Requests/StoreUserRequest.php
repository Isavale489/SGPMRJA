<?php

namespace App\Http\Requests;

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
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z0-9]).*$/'],
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
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.regex'     => 'La contraseña debe incluir al menos una mayúscula, un número y un carácter especial.',
            'role_id.required'   => 'El rol es obligatorio.',
            'role_id.exists'     => 'El rol seleccionado no es válido.',
        ];
    }
}
