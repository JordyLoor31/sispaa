export interface Catalogo { id: number; name?: string; nombre?: string }

export interface ProyectoItem {
    id: number;
    titulo: string;
    objetivo: string | null;
    estado: 'en_curso' | 'pausada' | 'finalizada';
    docente: { id: number; name: string };
    coordinador: { id: number; name: string };
    periodo: string;
    total_hitos: number;
    hitos_completados: number;
    es_propio: boolean;
    es_coordinador: boolean;
}
