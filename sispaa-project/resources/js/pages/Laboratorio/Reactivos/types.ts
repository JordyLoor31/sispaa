export interface Catalogo {
    id: number;
    nombre: string;
}

export interface Persona {
    id: number;
    name: string;
}

export interface ReactivoItem {
    id: number;
    nombre: string;
    formula: string | null;
    cantidad: number;
    unidad: string | null;
    estado: 'disponible' | 'agotado' | 'vencido';
    laboratorio: string;
    laboratorio_id: number;
    creator?: Persona | null;
    updater?: Persona | null;
    created_at?: string;
}
