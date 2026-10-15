<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Política única de contraseñas del sistema: entre 8 y 72 caracteres, con una
 * mayúscula, un número y un carácter especial. La usan TODOS los puntos donde
 * se fija una contraseña (alta de usuario, clave temporal del admin, cambio
 * desde el perfil, cambio forzoso y las dos recuperaciones), para que no haya
 * un camino con una regla más débil.
 *
 * El texto de ayuda de las páginas React sale de resources/js/lib/contrasena.ts,
 * que un test obliga a mantener igual a DESCRIPCION.
 */
class ContrasenaSegura implements ValidationRule
{
    public const DESCRIPCION = 'Entre 8 y 72 caracteres, con una mayúscula, un número y un carácter especial.';

    /** bcrypt ignora lo que pase de 72 bytes: más largo daría una falsa sensación de seguridad. */
    private const MAXIMO_BYTES = 72;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('La contraseña no es válida.');

            return;
        }

        if (mb_strlen($value) < 8) {
            $fail('La contraseña debe tener al menos 8 caracteres.');

            return;
        }
        if (strlen($value) > self::MAXIMO_BYTES) {
            $fail('La contraseña es demasiado larga: máximo 72 caracteres (las letras con acento y la ñ cuentan doble).');

            return;
        }
        // Unicode: Ñ/Á cuentan como mayúscula y ñ/á como letras (no como símbolo).
        if (! preg_match('/\p{Lu}/u', $value) || ! preg_match('/\p{Nd}/u', $value) || ! preg_match('/[^\p{L}\p{N}]/u', $value)) {
            $fail('La contraseña debe incluir al menos una mayúscula, un número y un carácter especial.');
        }
    }
}
