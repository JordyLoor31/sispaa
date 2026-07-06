<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Book, Feather, FlaskConical, GraduationCap, Handshake, LayoutGrid, Search, User, Bell, FileText, Calendar } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Vista general',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Docencia',
        href: '/docencia',
        icon: Feather,
        items: [
            {
                title: 'Informes de asignatura',
                href: '/docencia/informes-asignatura',
            },
            {
                title: 'Informes de sílabo',
                href: '/docencia/informes-silabo',
            },
        ],
    },
    {
        title: 'Investigación',
        href: '/investigacion',
        icon: Search,
        items: [
            {
                title: 'Informes',
                href: '/investigacion/informes',
            },
        ],
    },
    {
        title: 'Estudiantes',
        href: route('estudiantes.index'),
        icon: Book,
        items: [
            {
                title: 'Panel Estudiantes',
                href: route('estudiantes.index'),
            },
            {
                title: 'Estudiantes matriculados',
                href: route('estudiantes.matriculados'),
            },
            {
                title: 'Estudiantes activos',
                //href: route('estudiantes.index'),
            },
            {
                title: 'Estudiantes retirados',
                //href: route('estudiantes.index'),
            },
            {
                title: 'Faltas',
                href: route('estudiantes.faltas'),
            },
            {
                title: 'Justificaciones de faltas',
                href: route('estudiantes.justificaciones'),
            },
        ],
    },
{
    title: 'Laboratorio',
    href: route('laboratorio.index'),
    icon: FlaskConical,

    items: [
        {
            title: 'Dashboard',
            href: route('laboratorio.index'),
        },
        {
            title: 'Registro prácticas',
            href: route('laboratorio.create'),
        },
        {
            title: 'Prácticas',
            href: route('laboratorio.practicas'),
        },
        {
            title: 'Por carrera',
            href: route('laboratorio.porCarrera'),
        },
        {
            title: 'Ubicaciones',
            href: route('laboratorio.ubicaciones'),
        },
    ],
},
    {
        title: 'Vinculación',
        href: '/vinculacion',
        icon: Handshake,
        items: [
            {
                title: 'Líderes de vinculación',
                href: '/vinculacion/lideres',
            },
            {
                title: 'Actividades',
                href: '/vinculacion/actividades',
            },
            {
                title: 'Empresas beneficiadas',
                href: '/vinculacion/empresas-beneficiadas',
            },
        ],
    },
    {
        title: 'Titulación',
        href: '/titulacion',
        icon: GraduationCap,
        items: [
            {
                title: 'Temas en desarrollo',
                href: '/titulacion/temas-desarrollo',
            },
            {
                title: 'Estudiantes en proceso',
                href: '/titulacion/estudiantes-titulacion',
            },
            {
                title: 'Estudiantes titulados',
                href: '/titulacion/estudiantes-titulados',
            },
        ],
    },
];

const studentNavItems: NavItem[] = [
    {
        title: 'Vista general',
        href: '/dashboard',
        icon: LayoutGrid,
    },
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
    }
];

const adminNavItems: NavItem[] = [
    {
        title: 'Vista general',
        href: '/dashboard',
        icon: LayoutGrid,
    },
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
    {
        title: 'Informes de Asignatura',
        href: '/docencia/informes-asignatura',
        icon: FileText,
    }
];

const secretariaNavItems: NavItem[] = [
    {
        title: 'Vista general',
        href: '/dashboard',
        icon: LayoutGrid,
    },
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
];

const page = usePage<any>();
const user = computed(() => page.props.auth?.user);
const isAdmin = computed(() => user.value?.roles?.includes('administrador'));
const isStudent = computed(() => user.value?.roles?.includes('estudiante'));
const isSecretaria = computed(() => user.value?.roles?.includes('secretaria'));

const navItems = computed(() => {
    if (isAdmin.value) return adminNavItems;
    if (isStudent.value) return studentNavItems;
    if (isSecretaria.value) return secretariaNavItems;
    return mainNavItems;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="navItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
