<script setup lang="ts">
// Bloque colapsable del Sidebar que agrupa todas las vistas de UN rol.
// Se usa únicamente para SystemAdministrador: cada rol del sistema (Estudiante,
// Docente, Coordinador, Secretaría, Administración) aparece aquí como una
// sección independiente que se expande "en cascada" para mostrar sus vistas,
// sin mezclarse con las de otros roles.
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import NavMain from '@/components/NavMain.vue';
import { type NavItem } from '@/types';
import { ChevronDown } from 'lucide-vue-next';

withDefaults(
    defineProps<{
        label: string;
        items: NavItem[];
        defaultOpen?: boolean;
    }>(),
    {
        defaultOpen: false,
    },
);
</script>

<template>
    <Collapsible :default-open="defaultOpen" class="group/role-collapsible px-2">
        <CollapsibleTrigger
            class="flex w-full items-center justify-between rounded-md px-2 py-1.5 text-xs font-semibold tracking-wide text-sidebar-foreground/70 uppercase hover:bg-sidebar-accent"
        >
            <span>{{ label }}</span>
            <ChevronDown class="h-3.5 w-3.5 transition-transform group-data-[state=open]/role-collapsible:rotate-180" />
        </CollapsibleTrigger>
        <CollapsibleContent>
            <NavMain :items="items" hide-label />
        </CollapsibleContent>
    </Collapsible>
</template>
