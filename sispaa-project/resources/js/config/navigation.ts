// Configuración central de navegación del Sidebar, segmentada por rol (RBAC).
//
// Cada rol de Spatie (ver database/seeders/RolesPermissionsSeeder.php) tiene su
// propio bloque de items aquí. Un usuario normal solo ve el bloque de SU rol
// (lista plana, como hasta ahora). El rol raíz `SYSTEM_ADMINISTRADOR` es la
// excepción: ve TODOS los bloques a la vez, agrupados y en cascada (colapsables)
// por nombre de rol, para poder navegar/supervisar cualquier módulo del sistema.
//
// Al agregar una vista nueva a un módulo, agregar el item aquí una sola vez:
// automáticamente aparecerá tanto en el menú del rol dueño como en la vista
// "todo" de SystemAdministrador.

import { type NavItem } from '@/types';
import { BarChart3, Bell, Book, BookOpen, Calendar, ClipboardCheck, Feather, FileText, FolderOpen, GraduationCap, Handshake, LayoutGrid, Megaphone, Search, Settings, User, UserCog, Users, type LucideIcon } from 'lucide-vue-next';

/** Nombres de rol tal como están sembrados en Spatie (roles.name). */
export const ROLES = {
    SYSTEM_ADMINISTRADOR: 'SystemAdministrador',
    SECRETARIA: 'secretaria',
    DOCENTE: 'docente',
    COORDINADOR: 'coordinador',
    ESTUDIANTE: 'estudiante',
} as const;

/** Item común a todos los roles, siempre visible y fuera de cualquier grupo. */
export const dashboardNavItem: NavItem = {
    title: 'Vista general',
    href: '/dashboard',
    icon: LayoutGrid,
};

/**
 * Menú cascada exclusivo de la vista "todo" de SystemAdministrador: agrupa
 * en un solo lugar todos los documentos que sube cada rol (docente,
 * coordinador, estudiante...) para revisión, en vez de repetirlos sueltos
 * dentro de los bloques de Docente/Secretaría como pasaba antes. Reutiliza
 * 100% las rutas/controladores/vistas de Secretaría y Docencia: no hay
 * pantallas nuevas, solo una entrada de menú distinta.
 */
export const revisionDocumentosNavItems: NavItem[] = [
    { title: 'Sílabos', href: route('secretaria.silabos.index') },
    { title: 'Informes de Asignatura', href: route('docencia.informes-asignaturas') },
    { title: 'Justificaciones', href: route('secretaria.justificaciones.index') },
    { title: 'Expediente / Documentos del Estudiante', href: route('secretaria.expediente.index') },
];

/** Exclusivo de SystemAdministrador (gestión del sistema en sí). */
export const systemAdministradorNavItems: NavItem[] = [
    {
        title: 'Gestión de Usuarios',
        href: route('admin.usuarios.index'),
        icon: User,
    },
    {
        title: 'Carreras',
        href: route('admin.carreras.index'),
        icon: GraduationCap,
    },
    {
        title: 'Asignaturas',
        href: route('admin.materias.index'),
        icon: Book,
    },
    {
        title: 'Gestión de Periodos',
        href: route('admin.periodos.index'),
        icon: Calendar,
    },
    {
        title: 'Revisión de Documentos',
        href: route('secretaria.silabos.index'),
        icon: ClipboardCheck,
        items: revisionDocumentosNavItems,
    },
];

/**
 * Rol Docente: docencia e investigación. El bloque "Laboratorio" se ocultó
 * a pedido (no se usa por ahora); las rutas/vistas siguen existiendo, solo
 * se quitó la entrada del menú. Para reactivarlo, restaurar el item que
 * enlazaba a laboratorio.index (con sus 6 sub-items) en este array y en
 * docenteAdminOverviewNavItems.
 */
export const docenteNavItems: NavItem[] = [
    {
        title: 'Docencia',
        href: route('docencia.mis-silabos'),
        icon: Feather,
        items: [
            { title: 'Mis Sílabos', href: route('docencia.mis-silabos') },
            { title: 'Mis Informes de Asignatura', href: route('docencia.mis-informes') },
        ],
    },
    {
        title: 'Investigación',
        href: route('investigacion.index'),
        icon: Search,
    },
    {
        title: 'Titulación',
        href: route('titulacion.index'),
        icon: GraduationCap,
    },
    {
        title: 'Mis Estudiantes',
        href: route('estudiantes.faltas'),
        icon: Book,
        items: [
            { title: 'Faltas', href: route('estudiantes.faltas') },
            { title: 'Justificaciones de faltas', href: route('estudiantes.justificaciones') },
        ],
    },
];

/**
 * Variante del bloque "Docente" para SystemAdministrador cuando NO tiene
 * también el rol docente. El antiguo item "Docencia" (Sílabos subidos /
 * Informes de Asignatura) se quitó de aquí porque ahora vive, seccionado
 * junto al resto de documentos que suben otros roles, dentro de "Revisión
 * de Documentos" (ver revisionDocumentosNavItems / systemAdministradorNavItems).
 * El bloque "Laboratorio" se ocultó a pedido (no se usa por ahora).
 */
export const docenteAdminOverviewNavItems: NavItem[] = [
    {
        title: 'Investigación',
        href: route('investigacion.index'),
        icon: Search,
    },
];

/** Rol Coordinador: supervisión de investigación, titulación y vinculación. */
export const coordinadorNavItems: NavItem[] = [
    {
        title: 'Investigación',
        href: route('investigacion.index'),
        icon: Search,
    },
    {
        title: 'Titulación',
        href: route('titulacion.index'),
        icon: GraduationCap,
    },
    {
        title: 'Vinculación',
        href: route('vinculacion.actividades'),
        icon: Handshake,
        items: [
            { title: 'Actividades', href: route('vinculacion.actividades') },
            { title: 'Empresas beneficiadas', href: route('vinculacion.empresas') },
        ],
    },
];

/** Rol Secretaría: expediente SGA, justificaciones, matrículas, convocatorias,
 *  grupos de documentos y notificaciones masivas. */
export const secretariaNavItems: NavItem[] = [
    {
        title: 'Expediente SGA',
        href: route('secretaria.expediente.index'),
        icon: FileText,
    },
    {
        title: 'Justificaciones',
        href: route('secretaria.justificaciones.index'),
        icon: Search,
    },
    {
        title: 'Revisión de Sílabos',
        href: route('secretaria.silabos.index'),
        icon: BookOpen,
    },
    {
        title: 'Titulación',
        href: route('titulacion.index'),
        icon: GraduationCap,
    },
    {
        title: 'Estudiantes',
        href: route('estudiantes.index'),
        icon: Book,
        items: [
            { title: 'Panel Estudiantes', href: route('estudiantes.index') },
            { title: 'Estudiantes matriculados', href: route('estudiantes.matriculados') },
            { title: 'Faltas', href: route('estudiantes.faltas') },
            { title: 'Justificaciones de faltas', href: route('estudiantes.justificaciones') },
        ],
    },
    {
        title: 'Asignación de Docentes',
        href: route('secretaria.asignaciones-docente.index'),
        icon: UserCog,
    },
    {
        title: 'Matrículas',
        href: route('secretaria.matriculas.index'),
        icon: Book,
    },
    {
        title: 'Convocatorias',
        href: route('secretaria.convocatorias.index'),
        icon: Megaphone,
    },
    {
        title: 'Grupos de Documentos',
        href: route('secretaria.grupos-documentos.index'),
        icon: FolderOpen,
    },
    {
        title: 'Notificaciones Masivas',
        href: route('secretaria.notificaciones-masivas.index'),
        icon: Bell,
    },
    {
        title: 'Reportes',
        href: route('reportes.index'),
        icon: BarChart3,
        items: [
            { title: 'Exportar datos', href: route('reportes.index') },
            { title: 'Estudiantes', href: route('reportes.estudiantes') },
            { title: 'Sílabos', href: route('reportes.silabos') },
            { title: 'Informes de Asignatura', href: route('reportes.informes') },
            { title: 'Vinculación', href: route('reportes.vinculacion') },
            { title: 'Titulación', href: route('reportes.titulacion') },
            // 'Laboratorio' (reportes.laboratorio) oculto a pedido: no se usa por ahora.
        ],
    },
];

/**
 * Variante de secretariaNavItems solo para la vista "todo" de
 * SystemAdministrador: quita items que ya se ven en otro bloque de esa
 * misma vista consolidada, para no repetirlos dos veces en el sidebar.
 * - 'Expediente SGA' / 'Justificaciones' / 'Revisión de Sílabos' ahora
 *   viven, seccionados junto al resto de documentos que suben otros
 *   roles, dentro de "Revisión de Documentos".
 * - 'Estudiantes' ya aparece en el bloque "Coordinador" (gestionEstudiantesNavItems).
 * Se deriva por filtro (no se duplica a mano) para no desincronizarse si
 * secretariaNavItems cambia. secretariaNavItems en sí queda intacto: el
 * personal real de Secretaría lo sigue necesitando completo en su propio
 * menú (navByRole).
 */
const TITULOS_YA_CUBIERTOS_EN_VISTA_ADMIN = new Set(['Expediente SGA', 'Justificaciones', 'Revisión de Sílabos', 'Estudiantes']);
export const secretariaAdminOverviewNavItems: NavItem[] = secretariaNavItems.filter(
    (item) => !TITULOS_YA_CUBIERTOS_EN_VISTA_ADMIN.has(item.title),
);

/** Rol Estudiante: portal propio del estudiante. */
export const estudianteNavItems: NavItem[] = [
    {
        title: 'Mis Documentos',
        href: '/estudiante/documentos',
        icon: FileText,
    },
    {
        title: 'Mis Justificaciones',
        href: '/estudiante/justificaciones',
        icon: Search,
    },
    {
        title: 'Mi Titulación',
        href: '/estudiante/titulacion',
        icon: GraduationCap,
    },
    {
        title: 'Mis Asistencias',
        href: '/estudiante/asistencias',
        icon: Feather,
    },
    {
        title: 'Mi Perfil',
        href: '/estudiante/perfil',
        icon: User,
    },
];

/**
 * Ítems relacionados a Estudiantes que hoy administra el staff
 * (secretaría/coordinación), separados del portal propio del estudiante.
 */
export const gestionEstudiantesNavItems: NavItem[] = [
    {
        title: 'Estudiantes',
        href: route('estudiantes.index'),
        icon: Book,
        items: [
            { title: 'Panel Estudiantes', href: route('estudiantes.index') },
            { title: 'Estudiantes matriculados', href: route('estudiantes.matriculados') },
            { title: 'Faltas', href: route('estudiantes.faltas') },
            { title: 'Justificaciones de faltas', href: route('estudiantes.justificaciones') },
        ],
    },
];

/** Un bloque por rol, usado para armar la vista "todo" en cascada de SystemAdministrador. */
export interface NavRoleGroup {
    key: string;
    label: string;
    items: NavItem[];
    icon?: LucideIcon;
}

export const roleNavGroups: NavRoleGroup[] = [
    { key: 'administracion', label: 'Administración', items: systemAdministradorNavItems, icon: Settings },
    { key: 'docente', label: 'Docente', items: docenteNavItems, icon: Feather },
    { key: 'coordinador', label: 'Coordinador', items: [...coordinadorNavItems, ...gestionEstudiantesNavItems], icon: Users },
    { key: 'secretaria', label: 'Secretaría', items: secretariaAdminOverviewNavItems, icon: FileText },
    { key: 'estudiante', label: 'Estudiante', items: estudianteNavItems, icon: User },
];

/** Menú plano por rol para usuarios normales (no SystemAdministrador). */
export const navByRole: Record<string, NavItem[]> = {
    [ROLES.SECRETARIA]: secretariaNavItems,
    [ROLES.DOCENTE]: docenteNavItems,
    [ROLES.COORDINADOR]: [...coordinadorNavItems, ...gestionEstudiantesNavItems],
    [ROLES.ESTUDIANTE]: estudianteNavItems,
};

/**
 * Resuelve qué debe pintar el Sidebar para un set de roles del usuario actual.
 * - SystemAdministrador -> modo "god view": grupos colapsables por rol.
 *   El grupo "Docente" muestra la vista de supervisión (todos los sílabos e
 *   informes) en vez de "Mis Sílabos"/"Mis Informes" en primera persona,
 *   salvo que el SystemAdministrador también tenga el rol docente. El grupo
 *   "Estudiante" (autoservicio en primera persona) se omite por completo,
 *   salvo que también tenga el rol estudiante, ya que sus equivalentes de
 *   supervisión (expediente, justificaciones, documentos) ya están cubiertos
 *   por los grupos Secretaría/Coordinador.
 * - Cualquier otro rol reconocido -> lista plana combinando los menús de
 *   TODOS los roles que tenga (ej. alguien con docente + coordinador ve
 *   ambos menús seguidos, no solo el primero que coincida).
 * - Sin rol reconocido -> solo la Vista general.
 */
export function resolveSidebarNav(userRoles: string[] = []): { mode: 'grouped' | 'flat'; groups?: NavRoleGroup[]; items?: NavItem[] } {
    if (userRoles.includes(ROLES.SYSTEM_ADMINISTRADOR)) {
        const isAlsoDocente = userRoles.includes(ROLES.DOCENTE);
        const isAlsoEstudiante = userRoles.includes(ROLES.ESTUDIANTE);

        const groups = roleNavGroups
            .filter((group) => group.key !== 'estudiante' || isAlsoEstudiante)
            .map((group) => {
                if (group.key === 'docente' && !isAlsoDocente) {
                    return { ...group, items: docenteAdminOverviewNavItems };
                }
                return group;
            });

        return { mode: 'grouped', groups };
    }

    const matchedRoles = Object.keys(navByRole).filter((role) => userRoles.includes(role));

    // Combina los items de cada rol coincidente evitando duplicar el mismo href
    // (relevante para docente+coordinador, ambos con acceso a "Estudiantes").
    const seenHrefs = new Set<string>();
    const items: NavItem[] = [];
    for (const role of matchedRoles) {
        for (const item of navByRole[role]) {
            const key = item.href ?? item.title;
            if (seenHrefs.has(key)) continue;
            seenHrefs.add(key);
            items.push(item);
        }
    }

    return { mode: 'flat', items };
}
