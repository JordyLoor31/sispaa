<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Feather, Book, Search, FlaskConical, Handshake, GraduationCap, LayoutGrid } from 'lucide-vue-next';
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
        href: '/estudiantes',
        icon: Book,
        items: [
            {
                title: 'Estudiantes matriculados',
                href: '/estudiantes/matriculados',
            },
            {
                title: 'Estudiantes activos',
                href: '/estudiantes/activos',
            },
            {
                title: 'Estudiantes retirados',
                href: '/estudiantes/retirados',
            },
            {
                title: 'Faltas',
                href: '/estudiantes/faltas',
            },
            {
                title: 'Justificaciones de faltas',
                href: '/estudiantes/justificaciones',
            },
        ],
    },
    {
        title: 'Prácticas de Laboratorio',
        href: '/practicas-laboratorio',
        icon: FlaskConical,
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
        ],
    },
];

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
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
