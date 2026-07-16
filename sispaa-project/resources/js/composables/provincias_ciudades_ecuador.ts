import provinciasData from '@/data/provincias_ciudades_ecuador.json';

interface Ciudad {
    id: string;
    name: string;
}

interface Provincia {
    id: number;
    name: string;
    cities: Ciudad[];
}

export const provincias_ecuador = provinciasData as Provincia[];

/**
 * Composable para consumir el catálogo anterior en selects/Combobox en
 * cascada (provincia -> cantón/ciudad) sin repetir la lógica de búsqueda
 * en cada formulario que la necesite (ej. perfil de estudiante).
 */
export function useProvinciasCiudadesEcuador() {
    const provincias = provincias_ecuador.map((p) => p.name);

    function ciudadesDeProvincia(provincia: string | null | undefined): string[] {
        if (!provincia) return [];
        return provincias_ecuador.find((p) => p.name === provincia)?.cities.map((c) => c.name) ?? [];
    }

    // Listado plano de todos los cantones/ciudades del país, sin depender de
    // una provincia seleccionada (ej. "Ciudad donde Laboras").
    const todasLasCiudades = [...new Set(provincias_ecuador.flatMap((p) => p.cities.map((c) => c.name)))].sort((a, b) =>
        a.localeCompare(b, 'es'),
    );

    return { provincias, ciudadesDeProvincia, todasLasCiudades };
}
