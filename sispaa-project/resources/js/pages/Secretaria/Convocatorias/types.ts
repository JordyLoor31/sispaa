export interface AuditUser {
    id: number;
    name: string;
}

export interface Convocatoria {
    id: number;
    titulo: string;
    descripcion: string | null;
    modulo: string;
    tipo_documento: string | null;
    fecha_inicio: string;
    fecha_fin: string;
    activa: boolean;
    creado_por?: number | null;
    creadoPor?: AuditUser | null;
    creator?: AuditUser | null;
    updater?: AuditUser | null;
    created_at?: string;
    updated_at?: string;
}
