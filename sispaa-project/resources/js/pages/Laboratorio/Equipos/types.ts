export interface Catalogo {
    id: number;
    nombre: string;
}

export interface Persona {
    id: number;
    name: string;
}

export interface EquipoItem {
    id: number;
    nombre: string;
    codigo: string;
    cantidad: number;
    estado: 'operativo' | 'mantenimiento' | 'dañado';
    laboratorio: string;
    laboratorio_id: number;
    creator?: Persona | null;
    updater?: Persona | null;
    created_at?: string;
}
