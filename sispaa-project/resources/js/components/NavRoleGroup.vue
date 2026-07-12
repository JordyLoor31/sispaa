<script setup lang="ts">
// Bloque colapsable del Sidebar que agrupa todas las vistas de UN rol.
// Se usa únicamente para SystemAdministrador: cada rol del sistema (Estudiante,
// Docente, Coordinador, Secretaría, Administración) aparece aquí como una
// sección independiente que se expande "en cascada" para mostrar sus vistas,
// sin mezclarse con las de otros roles.
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import NavMain from '@/components/NavMain.vue';
import { type NavItem } from '@/types';
import { ChevronDown, type LucideIcon } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        label: string;
        items: NavItem[];
        icon?: LucideIcon;
        defaultOpen?: boolean;
    }>(),
    {
        defaultOpen: false,
    },
);
</script>

<template>
    <Collapsible :default-open="defaultOpen" class="group/role-collapsible px-2 group-data-[collapsible=icon]:px-0">
        <CollapsibleTrigger
            class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-xs font-semibold tracking-wide text-sidebar-foreground/70 uppercase hover:bg-sidebar-accent group-data-[collapsible=icon]:justify-center"
        >
            <span class="flex min-w-0 items-center gap-2">
                <component :is="icon" v-if="icon" class="h-4 w-4 shrink-0" />
                <span class="truncate group-data-[collapsible=icon]:hidden">{{ label }}</span>
            </span>
            <ChevronDown class="h-3.5 w-3.5 shrink-0 transition-transform group-data-[state=open]/role-collapsible:rotate-180 group-data-[collapsible=icon]:hidden" />
        </CollapsibleTrigger>
        <CollapsibleContent>
            <NavMain :items="items" hide-label />
        </CollapsibleContent>
    </Collapsible>
</template>
