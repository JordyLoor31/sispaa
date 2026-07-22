<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida una cédula de identidad ecuatoriana con el algoritmo oficial del
 * Registro Civil (módulo 10):
 *
 *  - 10 dígitos numéricos.
 *  - Los 2 primeros dígitos son el código de provincia: 01-24, o 30 para
 *    ecuatorianos registrados en el exterior.
 *  - El tercer dígito es 0-5 (personas naturales; 6 es cédula antigua de
 *    extranjeros, 9 es RUC de sociedades — ninguno aplica aquí).
 *  - El décimo dígito es el verificador: se multiplican los 9 primeros
 *    dígitos por los coeficientes 2,1,2,1,2,1,2,1,2 (restando 9 a cada
 *    producto mayor a 9), se suman, y el verificador es lo que falta a esa
 *    suma para llegar a la siguiente decena (módulo 10).
 */
class CedulaEcuatoriana implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cedula = is_scalar($value) ? (string) $value : '';

        if (!preg_match('/^\d{10}$/', $cedula)) {
            $fail('La cédula debe tener exactamente 10 dígitos numéricos.');

            return;
        }

        $provincia = (int) substr($cedula, 0, 2);
        if (($provincia < 1 || $provincia > 24) && $provincia !== 30) {
            $fail('La cédula no corresponde a una provincia ecuatoriana válida.');

            return;
        }

        if ((int) $cedula[2] > 5) {
            $fail('La cédula ingresada no corresponde a una persona natural.');

            return;
        }

        $suma = 0;
        for ($i = 0; $i < 9; $i++) {
            $digito = (int) $cedula[$i];
            if ($i % 2 === 0) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }
            $suma += $digito;
        }

        $verificador = (10 - ($suma % 10)) % 10;

        if ($verificador !== (int) $cedula[9]) {
            $fail('La cédula ingresada no es válida (dígito verificador incorrecto).');
        }
    }
}
