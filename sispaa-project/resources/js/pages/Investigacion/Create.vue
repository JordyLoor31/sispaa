<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import InvestigacionForm from './InvestigacionForm.vue';
import type { Catalogo } from './types';

defineProps<{
    periodos: Catalogo[];
    usuarios: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Proyecto de Investigación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Nuevo Proyecto de Investigación</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Define el equipo que llevará adelante el proyecto.</p>
            </div>

            <div class="w-full max-w-5xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <InvestigacionForm :periodos="periodos" :usuarios="usuarios" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
