export interface Catalogo {
    id: number;
    nombre: string;
}

export interface StudentRow {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    telefono: string | null;
    carrera_id: number | null;
    carrera_nombre: string | null;
    matricula_estado: string | null;
    documentos_count: number;
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
