<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import PracticaForm from './PracticaForm.vue';
import type { Catalogo } from './types';

defineProps<{
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
        <Head title="Nueva Práctica de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Nueva Práctica de Laboratorio</h1>
            </div>

            <div class="w-full max-w-2xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <PracticaForm :materias="materias" :laboratorios="laboratorios" :periodos="periodos" :equipos-catalogo="equiposCatalogo" :reactivos-catalogo="reactivosCatalogo" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
