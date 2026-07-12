<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavRoleGroup from '@/components/NavRoleGroup.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboardNavItem, resolveSidebarNav } from '@/config/navigation';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage<SharedData>();
const userRoles = computed<string[]>(() => page.props.auth?.user?.roles ?? []);
const sidebarNav = computed(() => resolveSidebarNav(userRoles.value));

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
            <!-- Vista general: siempre visible, sin importar el rol -->
            <NavMain :items="[dashboardNavItem]" hide-label />

            <!-- SystemAdministrador: ve TODO, segmentado en cascada por rol -->
            <template v-if="sidebarNav.mode === 'grouped'">
                <NavRoleGroup v-for="group in sidebarNav.groups" :key="group.key" :label="group.label" :items="group.items" :icon="group.icon" />
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
