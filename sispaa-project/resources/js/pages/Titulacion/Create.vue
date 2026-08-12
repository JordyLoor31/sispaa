<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import TitulacionForm from './TitulacionForm.vue';
import type { Persona } from './columns';

defineProps<{
    estudiantes: Persona[];
    tutores: Persona[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Tema de Titulación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Registrar Tema de Titulación</h1>
            </div>

            <div class="w-full max-w-5xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <TitulacionForm :estudiantes="estudiantes" :tutores="tutores" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
