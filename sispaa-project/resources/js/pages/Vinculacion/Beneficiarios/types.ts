export interface AuditUser { id: number; name: string }

export type TipoBeneficiario = 'persona_natural' | 'persona_juridica' | 'comunidad_organizacion';

// El beneficiario aquí es un LUGAR/SECTOR (empresa, barrio, comunidad), no una
// persona. Las claves del enum se conservan por compatibilidad de datos.
export const TIPO_BENEFICIARIO_LABELS: Record<TipoBeneficiario, string> = {
    persona_juridica: 'Empresa o institución',
    comunidad_organizacion: 'Comunidad, barrio u organización',
    persona_natural: 'Otro lugar o sector',
};

export interface Beneficiario {
    id: number;
    tipo: TipoBeneficiario;
    nombre: string;
    ruc: string | null;
    cedula: string | null;
    sector: string | null;
    contacto: string | null;
    actividades_count?: number;
    creator?: AuditUser | null;
    updater?: AuditUser | null;
    created_at?: string;
}
