export interface Docente {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
}

export interface CarreraOption {
    id: number;
    nombre: string;
    codigo: string;
}

export interface MateriaOption {
    id: number;
    carrera_id: number | null;
    nombre: string;
    codigo: string;
    nivel: number;
    carrera?: CarreraOption | null;
}

export interface PeriodoOption {
    id: number;
    nombre: string;
    estado: 'planificado' | 'activo' | 'finalizado';
}

export interface AsignacionDocenteItem {
    id: number;
    tipo_rol: string;
    grupo: string | null;
    docente: { id: number; name: string; email: string };
    materia: { id: number; nombre: string; codigo: string; carrera?: CarreraOption | null };
    periodo: { id: number; nombre: string; estado: string };
    created_at?: string;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}
