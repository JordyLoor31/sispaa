export interface Estudiante { id: number; name: string; email: string; cedula: string | null; carrera_id: number | null }
export interface Periodo { id: number; nombre: string; estado: 'planificado' | 'activo' | 'finalizado' }
export interface Carrera { id: number; nombre: string; codigo: string }
export interface MatriculaItem {
    id: number;
    estado: 'activo' | 'retirado' | 'egresado';
    fecha_matricula: string;
    created_at: string;
    estudiante: { id: number; name: string; email: string; cedula: string | null };
    periodo: { id: number; nombre: string };
    carrera: { id: number; nombre: string; codigo: string };
}
