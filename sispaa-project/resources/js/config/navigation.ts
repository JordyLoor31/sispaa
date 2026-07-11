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
import { Bell, Book, Calendar, Feather, FileText, FlaskConical, FolderOpen, GraduationCap, Handshake, LayoutGrid, Megaphone, Search, User } from 'lucide-vue-next';

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
        href: '/admin/usuarios',
        icon: User,
    },
    {
        title: 'Malla Curricular',
        href: '/admin/malla',
        icon: Book,
    },
    {
        title: 'Fechas y Convocatorias',
        href: '/admin/fechas',
        icon: Calendar,
    },
];

/** Rol Docente: docencia, investigación y laboratorio. */
export const docenteNavItems: NavItem[] = [
    {
        title: 'Docencia',
        href: '/docencia',
        icon: Feather,
        items: [
            { title: 'Informes de asignatura', href: '/docencia/informes-asignatura' },
            { title: 'Informes de sílabo', href: '/docencia/informes-silabo' },
        ],
    },
    {
        title: 'Investigación',
        href: '/investigacion',
        icon: Search,
        items: [{ title: 'Informes', href: '/investigacion/informes' }],
    },
    {
        title: 'Laboratorio',
        href: route('laboratorio.index'),
        icon: FlaskConical,
        items: [
            { title: 'Dashboard', href: route('laboratorio.index') },
            { title: 'Registro prácticas', href: route('laboratorio.create') },
            { title: 'Prácticas', href: route('laboratorio.practicas') },
            { title: 'Por carrera', href: route('laboratorio.porCarrera') },
            { title: 'Ubicaciones', href: route('laboratorio.ubicaciones') },
        ],
    },
];

/** Rol Coordinador: titulación y vinculación. */
export const coordinadorNavItems: NavItem[] = [
    {
        title: 'Titulación',
        href: '/titulacion',
        icon: GraduationCap,
        items: [
            { title: 'Temas en desarrollo', href: '/titulacion/temas-desarrollo' },
            { title: 'Estudiantes en proceso', href: '/titulacion/estudiantes-titulacion' },
            { title: 'Estudiantes titulados', href: '/titulacion/estudiantes-titulados' },
        ],
    },
    {
        title: 'Vinculación',
        href: '/vinculacion',
        icon: Handshake,
        items: [
            { title: 'Líderes de vinculación', href: '/vinculacion/lideres' },
            { title: 'Actividades', href: '/vinculacion/actividades' },
            { title: 'Empresas beneficiadas', href: '/vinculacion/empresas-beneficiadas' },
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
}

export const roleNavGroups: NavRoleGroup[] = [
    { key: 'administracion', label: 'Administración', items: systemAdministradorNavItems },
    { key: 'docente', label: 'Docente', items: docenteNavItems },
    { key: 'coordinador', label: 'Coordinador', items: [...coordinadorNavItems, ...gestionEstudiantesNavItems] },
    { key: 'secretaria', label: 'Secretaría', items: secretariaNavItems },
    { key: 'estudiante', label: 'Estudiante', items: estudianteNavItems },
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
 * - Cualquier otro rol reconocido -> lista plana de su propio rol.
 * - Sin rol reconocido -> solo la Vista general.
 */
export function resolveSidebarNav(userRoles: string[] = []): { mode: 'grouped' | 'flat'; groups?: NavRoleGroup[]; items?: NavItem[] } {
    if (userRoles.includes(ROLES.SYSTEM_ADMINISTRADOR)) {
        return { mode: 'grouped', groups: roleNavGroups };
    }

    const matchedRole = Object.keys(navByRole).find((role) => userRoles.includes(role));

    return { mode: 'flat', items: matchedRole ? navByRole[matchedRole] : [] };
}
