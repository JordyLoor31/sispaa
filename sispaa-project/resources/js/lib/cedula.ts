/**
 * Validación de cédulas de identidad ecuatorianas (algoritmo módulo 10 del
 * Registro Civil). Espejo exacto de la regla de backend
 * App\Rules\CedulaEcuatoriana: el backend sigue siendo la fuente de verdad,
 * esto solo da feedback inmediato en el navegador sin esperar el round-trip.
 *
 *  - 10 dígitos numéricos.
 *  - Código de provincia 01-24, o 30 (registrados en el exterior).
 *  - Tercer dígito 0-5 (personas naturales).
 *  - Décimo dígito verificador por módulo 10 con coeficientes 2,1,2,1,2,1,2,1,2.
 */

/** Devuelve el mensaje de error, o null si la cédula es válida. */
export function errorCedulaEcuatoriana(cedula: string): string | null {
    if (!/^\d{10}$/.test(cedula)) {
        return 'La cédula debe tener exactamente 10 dígitos numéricos.';
    }

    const provincia = parseInt(cedula.slice(0, 2), 10);
    if ((provincia < 1 || provincia > 24) && provincia !== 30) {
        return 'La cédula no corresponde a una provincia ecuatoriana válida.';
    }

    if (parseInt(cedula[2], 10) > 5) {
        return 'La cédula ingresada no corresponde a una persona natural.';
    }

    let suma = 0;
    for (let i = 0; i < 9; i++) {
        let digito = parseInt(cedula[i], 10);
        if (i % 2 === 0) {
            digito *= 2;
            if (digito > 9) digito -= 9;
        }
        suma += digito;
    }

    const verificador = (10 - (suma % 10)) % 10;

    if (verificador !== parseInt(cedula[9], 10)) {
        return 'La cédula ingresada no es válida (dígito verificador incorrecto).';
    }

    return null;
}

/** true si la cédula es válida según el algoritmo del Registro Civil. */
export function esCedulaEcuatorianaValida(cedula: string): boolean {
    return errorCedulaEcuatoriana(cedula) === null;
}
