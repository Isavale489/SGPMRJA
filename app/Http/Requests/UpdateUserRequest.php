<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'role' => 'required|in:Administrador,Supervisor',
        ];
    }
}
