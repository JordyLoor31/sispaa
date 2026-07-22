<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * El sistema solo acepta correos institucionales de la ULEAM:
 *
 *  - @live.uleam.edu.ec (estudiantes)
 *  - @uleam.edu.ec      (personal institucional)
 *
 * Espejo en frontend: resources/js/lib/correo.ts (feedback inmediato en el
 * navegador; esta regla sigue siendo la fuente de verdad).
 */
class CorreoInstitucional implements ValidationRule
{
    public const DOMINIOS = ['live.uleam.edu.ec', 'uleam.edu.ec'];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = is_scalar($value) ? strtolower(trim((string) $value)) : '';

        foreach (self::DOMINIOS as $dominio) {
            if (str_ends_with($email, '@' . $dominio)) {
                return;
            }
        }

        $fail('Solo se aceptan correos institucionales @live.uleam.edu.ec o @uleam.edu.ec.');
    }
}
