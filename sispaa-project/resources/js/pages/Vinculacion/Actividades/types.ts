export interface AuditUser { id: number; name: string }
export interface Catalogo { id: number; nombre: string }

export interface Actividad {
    id: number;
    nombre: string;
    estado: 'pendiente' | 'ejecutado';
    fecha: string | null;
    docente_lider: { id: number; name: string } | null;
    carrera_id: number;
    carrera: string;
    periodo_id: number;
    periodo: string;
    empresa_id: number | null;
    empresa: string | null;
    creator?: AuditUser | null;
    updater?: AuditUser | null;
    created_at?: string;
}
