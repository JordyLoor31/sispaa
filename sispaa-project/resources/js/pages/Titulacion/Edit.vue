<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import TitulacionForm from './TitulacionForm.vue';
import type { Persona, Titulacion } from './columns';

defineProps<{
    titulacion: Titulacion;
    tutores: Persona[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar proceso de ${titulacion.estudiante.name}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Editar Proceso de Titulación</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ titulacion.estudiante.name }}</p>
            </div>

            <div class="w-full max-w-5xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <TitulacionForm :tutores="tutores" :titulacion="titulacion" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
