/**
 * Tokens visuales compartidos del "lenguaje admin" de SISPAA (el look
 * definido primero en Gestión de Usuarios y replicado en los demás módulos):
 * tiles/avatares con degradado de marca, y pills de estado/rol tintados.
 *
 * IMPORTANTE: los colores dinámicos van SIEMPRE como style inline, nunca
 * como clases Tailwind interpoladas en runtime — Tailwind genera su CSS en
 * build escaneando literales completos del código fuente, así que una clase
 * armada con template literals (`bg-[...${color}...]`) no existe en el CSS
 * final y el elemento queda sin color.
 */

/** Degradado institucional teal → teal-verde para tiles y avatares. */
export const BRAND_GRADIENT =
    'background: linear-gradient(135deg, var(--sispaa-primary), color-mix(in srgb, var(--sispaa-primary) 45%, var(--sispaa-secondary)))';

/** Iniciales para avatares (primeras dos palabras del nombre). */
export const getInitials = (name: string): string =>
    name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0]!.toUpperCase())
        .join('');

/**
 * Estilo inline para un pill tintado (badge de rol/estado) que funciona en
 * tema claro y oscuro: el texto se mezcla con --sispaa-text (nunca con
 * negro/blanco fijos). `color` acepta hex o var(--...).
 */
export const tintedBadgeStyle = (color: string): string =>
    [
        `background-color: color-mix(in srgb, ${color} 22%, transparent)`,
        `color: color-mix(in srgb, ${color} 50%, var(--sispaa-text))`,
        `box-shadow: inset 0 0 0 1px color-mix(in srgb, ${color} 45%, transparent)`,
    ].join('; ');

/** Pill neutro (estados inactivos/finalizados). */
export const neutralBadgeStyle = (): string =>
    [
        'background-color: color-mix(in srgb, var(--sispaa-text) 8%, transparent)',
        'color: color-mix(in srgb, var(--sispaa-text) 65%, transparent)',
        'box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--sispaa-text) 15%, transparent)',
    ].join('; ');

/** Colores semánticos de estados de revisión/flujo usados en todo el sistema. */
export const STATUS_COLORS = {
    exito: 'var(--sispaa-secondary)', // aprobado / activo / subido
    advertencia: '#E4BC57', // pendiente / planificado
    peligro: '#d9634f', // rechazado / atrasado
    info: 'var(--sispaa-accent)', // en revisión / informativo
} as const;

/** Clase compartida del interruptor: verde institucional al estar activo. */
export const SWITCH_ACTIVE_CLASS = 'data-[state=checked]:!bg-[var(--sispaa-secondary)]';
