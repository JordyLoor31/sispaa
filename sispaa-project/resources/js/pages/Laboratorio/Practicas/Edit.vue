<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import PracticaForm from './PracticaForm.vue';
import type { Catalogo, PracticaEditItem } from './types';

const props = defineProps<{
    practica: PracticaEditItem;
    materias: Catalogo[];
    laboratorios: Catalogo[];
    periodos: Catalogo[];
    equiposCatalogo: Catalogo[];
    reactivosCatalogo: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.practica.tema}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Editar Práctica</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ props.practica.tema }}</p>
            </div>

            <div class="max-w-2xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <PracticaForm :practica="practica" :materias="materias" :laboratorios="laboratorios" :periodos="periodos" :equipos-catalogo="equiposCatalogo" :reactivos-catalogo="reactivosCatalogo" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
