// Configuración central del Sidebar por rol (RBAC).
// Cada rol de Spatie tiene su bloque de items; el usuario normal ve solo los
// de sus roles (lista plana). SystemAdministrador ve todos agrupados en cascada.
// Agregar un item aquí lo muestra en el menú del rol dueño y en la vista del
// SysAdmin a la vez.

import { type NavItem } from '@/types';
import { BarChart3, Book, BookOpen, Calendar, ClipboardCheck, Feather, FileText, Files, FolderOpen, GraduationCap, Handshake, LayoutGrid, Megaphone, Search, Settings, User, UserCog, Users, type LucideIcon } from 'lucide-vue-next';

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
 * Menú exclusivo de la vista SysAdmin: agrupa los documentos que suben todos
 * los roles para revisión, reutilizando las rutas/vistas existentes.
 */
export const revisionDocumentosNavItems: NavItem[] = [
    { title: 'Sílabos', href: route('coordinador.silabos.index') },
    { title: 'Informes de Asignatura', href: route('secretaria.informes.index') },
    { title: 'Expediente / Documentos del Estudiante', href: route('estudiantes.index') },
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
        href: route('coordinador.silabos.index'),
        icon: ClipboardCheck,
        items: revisionDocumentosNavItems,
    },
];

/** Rol Docente: docencia, investigación y titulación. "Laboratorio" se ocultó
 *  a pedido (las rutas existen, solo se quitó del menú). */
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
];

/**
 * Variante del bloque Docente para la vista SysAdmin (cuando no tiene el rol
 * docente): "Docencia" e "Investigación" ya viven en otros bloques de esa
 * misma vista, así que este grupo queda vacío y resolveSidebarNav() lo omite.
 */
export const docenteAdminOverviewNavItems: NavItem[] = [];

/** Rol Coordinador: supervisión de investigación, titulación, sílabos y vinculación. */
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
        title: 'Revisión de Sílabos',
        href: route('coordinador.silabos.index'),
        icon: BookOpen,
    },
    {
        title: 'Vinculación',
        href: route('vinculacion.actividades'),
        icon: Handshake,
        items: [
            { title: 'Actividades', href: route('vinculacion.actividades') },
            { title: 'Beneficiarios', href: route('vinculacion.beneficiarios') },
        ],
    },
];

/**
 * Variante para la vista SysAdmin: quita 'Revisión de Sílabos', que ya vive
 * en "Revisión de Documentos". coordinadorNavItems queda intacto para el
 * coordinador real (navByRole).
 */
const TITULOS_YA_CUBIERTOS_COORDINADOR_EN_VISTA_ADMIN = new Set(['Revisión de Sílabos']);
export const coordinadorAdminOverviewNavItems: NavItem[] = coordinadorNavItems
    .filter((item) => !TITULOS_YA_CUBIERTOS_COORDINADOR_EN_VISTA_ADMIN.has(item.title));

/** Rol Secretaría: expediente de estudiantes (index + show), justificaciones,
 *  convocatorias y grupos de documentos. */
export const secretariaNavItems: NavItem[] = [
    {
        title: 'Estudiantes',
        href: route('estudiantes.index'),
        icon: Book,
    },
    {
        title: 'Titulación',
        href: route('titulacion.index'),
        icon: GraduationCap,
    },
    {
        title: 'Faltas Semanales',
        href: route('secretaria.faltas-semanales.index'),
        icon: Search,
    },
    {
        title: 'Asignación de Docentes',
        href: route('secretaria.asignaciones-docente.index'),
        icon: UserCog,
    },
    {
        title: 'Plantillas de Documentos',
        href: route('secretaria.plantillas.index'),
        icon: Files,
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
 * Variante para la vista SysAdmin: quita items que ya se ven en otro bloque
 * de esa misma vista, para no repetirlos en el sidebar. Se deriva por
 * filtro/map para no desincronizarse si secretariaNavItems cambia; este
 * array queda intacto para el personal real de Secretaría (navByRole).
 */
const TITULOS_YA_CUBIERTOS_EN_VISTA_ADMIN = new Set(['Titulación']);
export const secretariaAdminOverviewNavItems: NavItem[] = secretariaNavItems
    .filter((item) => !TITULOS_YA_CUBIERTOS_EN_VISTA_ADMIN.has(item.title));

/** Rol Estudiante: portal propio del estudiante. */
export const estudianteNavItems: NavItem[] = [
    {
        title: 'Mis Documentos',
        href: '/estudiante/documentos',
        icon: FileText,
    },
    {
        title: 'Mi Titulación',
        href: '/estudiante/titulacion',
        icon: GraduationCap,
    },
    {
        title: 'Plantillas',
        href: '/estudiante/plantillas',
        icon: Files,
    },
    {
        title: 'Mi Perfil',
        href: '/estudiante/perfil',
        icon: User,
    },
];

/** Ítems de Estudiantes que administra el staff, separados del portal del estudiante. */
export const gestionEstudiantesNavItems: NavItem[] = [
    {
        title: 'Estudiantes',
        href: route('estudiantes.index'),
        icon: Book,
        items: [
            { title: 'Estudiantes', href: route('estudiantes.index') },
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
    { key: 'coordinador', label: 'Coordinación', items: [...coordinadorAdminOverviewNavItems, ...gestionEstudiantesNavItems], icon: Users },
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
 * Resuelve qué pinta el Sidebar para los roles del usuario:
 * - SystemAdministrador -> grupos colapsables por rol ("Docente" usa la vista
 *   de supervisión y "Estudiante" se omite salvo que también tenga el rol).
 * - Otros roles -> lista plana combinando los menús de todos sus roles.
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
            })
            // Un grupo sin items (ej. "Docente" cuando todo su contenido ya
            // se ve en otro bloque, como Investigación en Coordinador) no se
            // muestra: evita un encabezado colapsable vacío en el sidebar.
            .filter((group) => group.items.length > 0);

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
