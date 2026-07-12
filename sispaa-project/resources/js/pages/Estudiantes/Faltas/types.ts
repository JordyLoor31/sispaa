export interface Catalogo {
    id: number;
    nombre: string;
}

export interface FaltaRow {
    id: number;
    fecha: string | null;
    estudiante: { id: number; name: string } | null;
    carrera: string | null;
    materia: string | null;
    periodo: string | null;
    justificada: boolean;
    motivo: string | null;
    solicitud_estado: 'pendiente' | 'aprobada' | 'rechazada' | null;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    total: number;
    links: PaginationLink[];
}
