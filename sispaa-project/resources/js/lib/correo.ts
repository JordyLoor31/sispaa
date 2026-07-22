/**
 * Validación del correo institucional ULEAM. Espejo exacto de la regla de
 * backend App\Rules\CorreoInstitucional (que sigue siendo la fuente de
 * verdad): el sistema solo acepta estos dos dominios.
 */
export const DOMINIOS_INSTITUCIONALES = ['live.uleam.edu.ec', 'uleam.edu.ec'] as const;

/** Devuelve el mensaje de error, o null si el correo tiene un dominio aceptado. */
export function errorCorreoInstitucional(email: string): string | null {
    const normalizado = email.trim().toLowerCase();

    const esInstitucional = DOMINIOS_INSTITUCIONALES.some((dominio) => normalizado.endsWith('@' + dominio));

    if (!esInstitucional) {
        return 'Solo se aceptan correos institucionales @live.uleam.edu.ec o @uleam.edu.ec.';
    }

    return null;
}

/** true si el correo pertenece a uno de los dominios institucionales. */
export function esCorreoInstitucional(email: string): boolean {
    return errorCorreoInstitucional(email) === null;
}
