/**
 * Configuración de colores personalizados para SISPAA
 * Paleta de colores definida en un lugar centralizado para fácil reutilización
 */

export const SISPAA_COLORS = {
  // Color primario - Verde
  primary: {
    green: '#88C273',
    rgb: 'rgb(136, 194, 115)',
    hsl: 'hsl(100, 45%, 61%)',
  },

  // Fondos - Tonos de beige
  background: {
    light: '#FFF1DB', // Beige más claro - para fondo general
    dark: '#D4BDAC', // Beige más oscuro - para elementos con texto
    rgb: {
      light: 'rgb(255, 241, 219)',
      dark: 'rgb(212, 189, 172)',
    },
  },

  // Sidebar - Azul
  sidebar: {
    blue: '#536493',
    rgb: 'rgb(83, 100, 147)',
    hsl: 'hsl(239, 30%, 45%)',
  },

  // Texto
  text: {
    dark: '#1a1a1a', // Negro oscuro para buen contraste
    light: '#444444', // Gris oscuro para texto secundario
    muted: '#666666', // Gris medio para texto terciario
  },

  // Estados
  status: {
    proposal: '#88C273', // Propuesta - verde
    inProgress: '#536493', // En desarrollo - azul
    completed: '#4CAF50', // Completado - verde más claro
    error: '#FF6B6B', // Error - rojo
    warning: '#FFA500', // Advertencia - naranja
  },
} as const;

/**
 * Función auxiliar para acceder a los colores
 * Ejemplo: getColor('primary.green')
 */
export function getColor(path: string): string {
  const keys = path.split('.');
  let value: any = SISPAA_COLORS;

  for (const key of keys) {
    value = value[key];
    if (value === undefined) {
      console.warn(`Color path not found: ${path}`);
      return '#000000';
    }
  }

  return value;
}

/**
 * Exportar como objeto CSS variables (opcional)
 * Para usar en hojas de estilo dinámicas
 */
export const cssVars = {
  '--sispaa-primary-green': SISPAA_COLORS.primary.green,
  '--sispaa-light-beige': SISPAA_COLORS.background.light,
  '--sispaa-dark-beige': SISPAA_COLORS.background.dark,
  '--sispaa-sidebar-blue': SISPAA_COLORS.sidebar.blue,
  '--sispaa-text-dark': SISPAA_COLORS.text.dark,
  '--sispaa-text-light': SISPAA_COLORS.text.light,
  '--sispaa-text-muted': SISPAA_COLORS.text.muted,
} as const;
