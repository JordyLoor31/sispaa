export interface SolicitudRow {
    id: number;
    estudiante: string | null;
    materia: string | null;
    fecha_falta: string | null;
    motivo_estudiante: string;
    estado: 'pendiente' | 'aprobada' | 'rechazada' | string;
    comentario_revisor: string | null;
    created_at: string | null;
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
