export interface CarreraOption {
    id: number;
    nombre: string;
}

export interface PeriodoOption {
    id: number;
    nombre: string;
    estado: 'planificado' | 'activo' | 'finalizado';
}

export interface FaltaSemanalItem {
    id: number;
    semana: number;
    cantidad_faltas: number;
    observaciones: string | null;
    carrera: { id: number; nombre: string };
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
