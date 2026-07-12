export interface AuditUser { id: number; name: string }

export interface Empresa {
    id: number;
    nombre: string;
    ruc: string | null;
    sector: string | null;
    contacto: string | null;
    actividades_count?: number;
    creator?: AuditUser | null;
    updater?: AuditUser | null;
    created_at?: string;
}
