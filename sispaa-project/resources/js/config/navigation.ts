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
import { BarChart3, Bell, Book, BookOpen, Calendar, Feather, FileText, FlaskConical, FolderOpen, GraduationCap, Handshake, LayoutGrid, Megaphone, Search, Settings, User, Users, type LucideIcon } from 'lucide-vue-next';

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
        title: 'Fechas y Convocatorias',
        href: '/admin/fechas',
        icon: Calendar,
    },
    {
        title: 'Notificaciones',
        href: route('notificaciones.index'),
        icon: Bell,
    },
];

/** Rol Docente: docencia, investigación y laboratorio. */
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
        title: 'Laboratorio',
        href: route('laboratorio.index'),
        icon: FlaskConical,
        items: [
            { title: 'Dashboard', href: route('laboratorio.index') },
            { title: 'Prácticas', href: route('laboratorio.practicas') },
            { title: 'Laboratorios', href: route('laboratorio.laboratorios') },
            { title: 'Equipos', href: route('laboratorio.equipos') },
            { title: 'Reactivos', href: route('laboratorio.reactivos') },
            { title: 'Por carrera', href: route('laboratorio.porCarrera') },
        ],
    },
    {
        title: 'Notificaciones',
        href: route('notificaciones.index'),
        icon: Bell,
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
    {
        title: 'Notificaciones',
        href: route('notificaciones.index'),
        icon: Bell,
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
    },
    {
        title: 'Notificaciones',
        href: route('notificaciones.index'),
        icon: Bell,
    },
];

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
    {
        title: 'Notificaciones',
        href: '/estudiante/notificaciones',
        icon: Bell,
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
    { key: 'secretaria', label: 'Secretaría', items: secretariaNavItems, icon: FileText },
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
 * - Cualquier otro rol reconocido -> lista plana combinando los menús de
 *   TODOS los roles que tenga (ej. alguien con docente + coordinador ve
 *   ambos menús seguidos, no solo el primero que coincida).
 * - Sin rol reconocido -> solo la Vista general.
 */
export function resolveSidebarNav(userRoles: string[] = []): { mode: 'grouped' | 'flat'; groups?: NavRoleGroup[]; items?: NavItem[] } {
    if (userRoles.includes(ROLES.SYSTEM_ADMINISTRADOR)) {
        return { mode: 'grouped', groups: roleNavGroups };
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
