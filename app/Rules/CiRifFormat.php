<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CiRifFormat implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!preg_match('/^(V-|J-|E-|G-)?\d+$/', $value)) {
            $fail('El campo :attribute debe tener el formato de documento válido (Ej: V-12345678, J-12345678, E-12345678 o G-12345678).');
        }
    }
}
