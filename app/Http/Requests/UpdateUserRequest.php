<?php

namespace App\Http\Requests;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:user,email,' . $userId,
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'role_id' => 'required|exists:rol,id',
        ];
    }

    /**
     * Mismo resguardo que al inhabilitar: el sistema no puede quedar sin un
     * Administrador activo. Antes, editar el rol del último lo permitía.
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->has('role_id')) {
                return;
            }
            $user = User::find($this->route('user'));
            $nuevoRol = Rol::find($this->input('role_id'));
            if (! $user || ! $user->isAdmin() || ! $user->estado || $nuevoRol?->nombre === 'Administrador') {
                return;
            }
            $adminsActivos = User::whereHas('rol', fn ($q) => $q->where('nombre', 'Administrador'))->where('estado', 1)->count();
            if ($adminsActivos <= 1) {
                $validator->errors()->add('role_id', 'No puedes quitarle el rol al último administrador activo.');
            }
        }];
    }

    public function messages(): array
    {
        return [
            'name.required'    => 'El nombre es obligatorio.',
            'email.required'   => 'El email es obligatorio.',
            'email.email'      => 'Ingrese un email válido.',
            'email.unique'     => 'Este correo ya está registrado.',
            'role_id.required' => 'El rol es obligatorio.',
            'role_id.exists'   => 'El rol seleccionado no es válido.',
        ];
    }
}
