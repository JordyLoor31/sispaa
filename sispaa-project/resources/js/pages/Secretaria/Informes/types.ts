export interface InformeItem {
    id: number;
    estado: 'pendiente' | 'subido' | 'aprobado';
    archivo_url: string | null;
    observaciones: string | null;
    fecha_subida: string | null;
    docente: { id: number; name: string; email: string };
    materia: string;
    carrera: string | null;
    periodo: string;
    ver_url: string | null;
}
