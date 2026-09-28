<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Política única de contraseñas del sistema: al menos 8 caracteres, con una
 * mayúscula, un número y un carácter especial. La usan TODOS los puntos donde
 * se fija una contraseña (alta de usuario, clave temporal del admin, cambio
 * desde el perfil, cambio forzoso y las dos recuperaciones), para que no haya
 * un camino con una regla más débil.
 */
class ContrasenaSegura implements ValidationRule
{
    public const DESCRIPCION = 'Mínimo 8 caracteres, con una mayúscula, un número y un carácter especial.';

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $valor = (string) $value;

        if (mb_strlen($valor) < 8) {
            $fail('La contraseña debe tener al menos 8 caracteres.');

            return;
        }
        if (! preg_match('/[A-Z]/', $valor) || ! preg_match('/\d/', $valor) || ! preg_match('/[^a-zA-Z0-9]/', $valor)) {
            $fail('La contraseña debe incluir al menos una mayúscula, un número y un carácter especial.');
        }
    }
}
