<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /** El correo se guarda en minúsculas: se normaliza en vez de rechazarlo. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            // El correo es la vía de recuperación de la cuenta: cambiarlo exige
            // confirmar la contraseña (una sesión abierta ajena no basta).
            'current_password' => [Rule::requiredIf(fn () => $this->cambiaCorreo()), 'nullable', 'string', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Para cambiar el correo, ingresa tu contraseña actual.',
        ];
    }

    private function cambiaCorreo(): bool
    {
        return is_string($this->input('email')) && $this->input('email') !== mb_strtolower((string) $this->user()->email);
    }

    /** Solo nombre y correo se guardan en el usuario. */
    public function datos(): array
    {
        return $this->safe()->only(['name', 'email']);
    }
}
