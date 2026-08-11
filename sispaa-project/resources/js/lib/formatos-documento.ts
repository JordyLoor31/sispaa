export const FORMATOS_DOCUMENTO = [
    { ext: 'pdf', mime: 'application/pdf', label: 'PDF' },
    { ext: 'jpg', mime: 'image/jpeg', label: 'JPG' },
    { ext: 'png', mime: 'image/png', label: 'PNG' },
    { ext: 'jpeg', mime: 'image/jpeg', label: 'JPEG' },
] as const;

export type FormatoExtension = (typeof FORMATOS_DOCUMENTO)[number]['ext'];

const FORMATO_LABEL = Object.fromEntries(FORMATOS_DOCUMENTO.map((f) => [f.ext, f.label]));

/** Mimes reales del navegador (File.type) que corresponden a cada extensión. */
export function mimesParaFormatos(formatos: readonly string[]): string[] {
    const mimes = FORMATOS_DOCUMENTO.filter((f) => formatos.includes(f.ext)).map((f) => f.mime);
    return Array.from(new Set(mimes));
}

/** Etiquetas legibles, p. ej. ['PDF', 'JPG'] o null cuando se permiten todos. */
export function etiquetasFormatos(formatos?: readonly string[] | null): string {
    if (!formatos || formatos.length === 0) {
        return FORMATOS_DOCUMENTO.map((f) => f.label).join(', ');
    }
    return formatos.map((ext) => FORMATO_LABEL[ext] ?? ext.toUpperCase()).join(', ');
}
