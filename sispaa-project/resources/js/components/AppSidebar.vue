<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavRoleGroup from '@/components/NavRoleGroup.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboardNavItem, resolveSidebarNav, type NavRoleGroup as NavRoleGroupConfig } from '@/config/navigation';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData & Record<string, unknown>>();
const userRoles = computed<string[]>(() => page.props.auth?.user?.roles ?? []);
const sidebarNav = computed(() => resolveSidebarNav(userRoles.value));

const footerNavItems: NavItem[] = [];

// Resuelve el pathname real de un href (Ziggy puede entregar hrefs absolutos
// o relativos), mismo criterio que NavMain.vue.
const resolvePath = (href?: string) => {
    if (!href) {
        return '#';
    }

    const ziggy = page.props.ziggy;

    if (ziggy?.url) {
        return new URL(href, ziggy.url).pathname;
    }

    if (typeof window === 'undefined') {
        return href;
    }

    return new URL(href, window.location.origin).pathname;
};

// ¿El item (o alguno de sus sub-items, recursivamente) corresponde a la ruta activa?
const itemMatchesCurrentPath = (item: NavItem): boolean => {
    if (resolvePath(item.href) === page.url) {
        return true;
    }

    return item.items?.some((subItem) => itemMatchesCurrentPath(subItem)) ?? false;
};

// Un grupo de rol (ej. "Docente") debe abrirse por defecto si la vista activa
// pertenece a ese grupo. Como cada navegación de Inertia remonta el Sidebar
// completo (no hay layout persistente en este proyecto), este cálculo se
// re-evalúa en cada carga y reemplaza el `defaultOpen` fijo que colapsaba
// el menú en cascada al seleccionar una opción.
const isGroupActive = (group: NavRoleGroupConfig): boolean => group.items.some((item) => itemMatchesCurrentPath(item));
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
            <!-- Vista general: siempre visible, sin importar el rol -->
            <NavMain :items="[dashboardNavItem]" hide-label />

            <!-- SystemAdministrador: ve TODO, segmentado en cascada por rol -->
            <template v-if="sidebarNav.mode === 'grouped'">
                <NavRoleGroup
                    v-for="group in sidebarNav.groups"
                    :key="group.key"
                    :label="group.label"
                    :items="group.items"
                    :icon="group.icon"
                    :default-open="isGroupActive(group)"
                />
            </template>

            <!-- Cualquier otro rol: solo su propio menú, en lista plana -->
            <NavMain v-else :items="sidebarNav.items ?? []" hide-label />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
