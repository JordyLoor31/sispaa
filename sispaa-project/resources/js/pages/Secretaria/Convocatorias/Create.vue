<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import ConvocatoriaForm from './ConvocatoriaForm.vue';

defineProps<{
    modulos: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Convocatoria" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Nueva Convocatoria</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Define el módulo, tipo de documento y ventana de fechas.
                </p>
            </div>

            <div class="max-w-xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <ConvocatoriaForm :modulos="modulos" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
