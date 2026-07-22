<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import CarreraForm from './CarreraForm.vue';
import type { Coordinador } from './columns';

defineProps<{
    coordinadores: Coordinador[];
    paletaColores?: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Carrera" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Nueva Carrera
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Registra un nuevo programa académico.
                </p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-4 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <CarreraForm :coordinadores="coordinadores" :paleta-colores="paletaColores" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
