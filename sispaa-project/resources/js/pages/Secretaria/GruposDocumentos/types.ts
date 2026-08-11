export interface AuditUser {
    id: number;
    name: string;
}

export interface Requisito {
    id: number;
    nombre: string;
    formatos_permitidos?: string[] | null;
    orden: number;
    activo: boolean;
}

export interface GrupoDocumento {
    id: number;
    nombre: string;
    descripcion: string | null;
    activo: boolean;
    requisitos: Requisito[];
    creado_por?: number | null;
    creadoPor?: AuditUser | null;
    creator?: AuditUser | null;
    updater?: AuditUser | null;
    created_at?: string;
    updated_at?: string;
}
