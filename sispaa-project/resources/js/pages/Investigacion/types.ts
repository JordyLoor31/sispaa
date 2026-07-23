export interface Catalogo { id: number; name?: string; nombre?: string }

export interface ProyectoItem {
    id: number;
    titulo: string;
    objetivo: string | null;
    estado: 'en_curso' | 'pausada' | 'finalizada';
    docente: { id: number; name: string };
    lider: { id: number; name: string };
    colider: { id: number; name: string } | null;
    miembros: { id: number; name: string }[];
    periodo: string;
    total_hitos: number;
    hitos_completados: number;
    es_propio: boolean;
    es_lider: boolean;
    es_colider: boolean;
    puede_gestionar: boolean;
}
