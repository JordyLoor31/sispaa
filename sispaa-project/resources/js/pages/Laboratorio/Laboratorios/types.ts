export interface Catalogo {
    id: number;
    nombre: string;
}

export interface Persona {
    id: number;
    name: string;
}

export interface LaboratorioItem {
    id: number;
    nombre: string;
    ubicacion: string | null;
    capacidad: number | null;
    estado: 'activo' | 'inactivo';
    carrera: string | null;
    carrera_id: number | null;
    responsable: Persona | null;
    equipos_count: number;
    reactivos_count: number;
    practicas_count: number;
    creator?: Persona | null;
    updater?: Persona | null;
    created_at?: string;
}
