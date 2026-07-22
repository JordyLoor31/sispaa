<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import ActividadForm from './ActividadForm.vue';
import type { Catalogo } from './types';

defineProps<{
    docentes: { id: number; name: string }[];
    carreras: Catalogo[];
    periodos: Catalogo[];
    empresas: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Actividad de Vinculación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,white)]">
            <div>
                <h3 class="text-base font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Nueva Actividad de Vinculación</h3>
            </div>

            <div class="mx-auto w-full max-w-xl rounded-2xl border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] p-4 shadow-sm sm:p-6">
                <ActividadForm :docentes="docentes" :carreras="carreras" :periodos="periodos" :empresas="empresas" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
