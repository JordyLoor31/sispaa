export interface AuditUser { id: number; name: string }
export interface Catalogo { id: number; nombre: string }

export type EstadoActividad = 'en_ejecucion' | 'ejecutado' | 'cancelado';

export const ESTADO_LABELS: Record<EstadoActividad, string> = {
    en_ejecucion: 'En ejecución',
    ejecutado: 'Ejecutado',
    cancelado: 'Cancelado',
};

export type Genero = 'hombre' | 'mujer';
export type RangoEdad = 'ninos' | 'jovenes' | 'adultos' | 'adultos_mayores';

export const GENEROS: { key: Genero; label: string }[] = [
    { key: 'hombre', label: 'Hombres' },
    { key: 'mujer', label: 'Mujeres' },
];

export const RANGOS_EDAD: { key: RangoEdad; label: string; hint: string }[] = [
    { key: 'ninos', label: 'Niños', hint: '0–11 años' },
    { key: 'jovenes', label: 'Jóvenes', hint: '12–18 años' },
    { key: 'adultos', label: 'Adultos', hint: '19–64 años' },
    { key: 'adultos_mayores', label: 'Adultos mayores', hint: '65+ años' },
];

/** Matriz género x edad usada en los formularios. */
export type Matriz = Record<Genero, Record<RangoEdad, number>>;

export const emptyMatriz = (): Matriz => ({
    hombre: { ninos: 0, jovenes: 0, adultos: 0, adultos_mayores: 0 },
    mujer: { ninos: 0, jovenes: 0, adultos: 0, adultos_mayores: 0 },
});

export interface Conteo {
    genero: Genero;
    rango_edad: RangoEdad;
    cantidad: number;
}

/** Convierte una lista de conteos (del backend) a una matriz para el formulario. */
export const conteosToMatriz = (conteos: Conteo[]): Matriz => {
    const m = emptyMatriz();
    for (const c of conteos) {
        if (m[c.genero] && c.rango_edad in m[c.genero]) {
            m[c.genero][c.rango_edad] = c.cantidad;
        }
    }
    return m;
};

/** Convierte una matriz a la lista de conteos que espera el backend. */
export const matrizToConteos = (m: Matriz): Conteo[] => {
    const out: Conteo[] = [];
    (Object.keys(m) as Genero[]).forEach((g) => {
        (Object.keys(m[g]) as RangoEdad[]).forEach((r) => {
            out.push({ genero: g, rango_edad: r, cantidad: Number(m[g][r]) || 0 });
        });
    });
    return out;
};

export interface Registro {
    id: number;
    tipo: 'inicial' | 'adicional';
    fecha: string | null;
    observacion: string | null;
    total: number;
    conteos: Conteo[];
}

export interface ResumenBeneficiarios {
    matriz: Matriz;
    por_genero: Record<Genero, number>;
    por_edad: Record<RangoEdad, number>;
    total: number;
}

export const TIPO_LABEL_CORTO: Record<string, string> = {
    persona_juridica: 'Empresa/institución',
    comunidad_organizacion: 'Comunidad/barrio',
    persona_natural: 'Otro lugar/sector',
};

export interface BeneficiarioRef {
    id: number;
    nombre: string;
    tipo: string;
    ruc: string | null;
    cedula: string | null;
}

export interface RepresentanteRef {
    id: number;
    nombre: string;
    cargo: string | null;
    cedula: string | null;
    telefono: string | null;
    beneficiario_id: number | null;
}

export interface Actividad {
    id: number;
    nombre: string;
    estado: EstadoActividad;

    fecha_inicio: string | null;
    fecha_fin: string | null;
    fecha_cierre?: string | null;
    motivo_cancelacion?: string | null;

    docente_lider: AuditUser | null;
    supervisor: AuditUser | null;

    carrera_id?: number;
    carrera?: string;

    periodo_id?: number;
    periodo?: string;

    beneficiario_id: number | null;
    beneficiario?: BeneficiarioRef | string | null;

    representante_id?: number | null;
    representante?: RepresentanteRef | null;

    total_beneficiarios?: number;
    registros?: Registro[];
    resumen?: ResumenBeneficiarios;

    // Solo en edición: conteos del registro inicial.
    conteos_iniciales?: Conteo[];

    creator?: AuditUser | null;
    updater?: AuditUser | null;
    created_at?: string;
}
