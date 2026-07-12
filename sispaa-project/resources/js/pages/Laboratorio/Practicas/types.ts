export interface Catalogo {
    id: number;
    nombre: string;
}

export interface UsoItem {
    id: number;
    nombre: string;
    cantidad_usada: number;
}

export interface PracticaItem {
    id: number;
    numero_practica: number;
    tema: string;
    subtema: string | null;
    logro_aprendizaje: string | null;
    semestre: string | null;
    numero_estudiantes: number | null;
    horario: string | null;
    objetivo: string | null;
    metodologia: string | null;
    resultados: string | null;
    conclusiones: string | null;
    fecha: string | null;
    periodo_id?: number;
    materia: Catalogo;
    carrera: string;
    docente: { id: number; name: string };
    laboratorio: Catalogo | null;
    periodo: string;
    equipos: UsoItem[];
    reactivos: UsoItem[];
    es_propio: boolean;
}

export interface PracticaEditItem {
    id: number;
    materia_id: number;
    periodo_id: number;
    laboratorio_id: number | null;
    numero_practica: number;
    fecha: string | null;
    tema: string;
    subtema: string | null;
    logro_aprendizaje: string | null;
    semestre: string | null;
    numero_estudiantes: number | null;
    horario: string | null;
    objetivo: string | null;
    metodologia: string | null;
    resultados: string | null;
    conclusiones: string | null;
    equipos: { id: number; cantidad_usada: number }[];
    reactivos: { id: number; cantidad_usada: number }[];
}
