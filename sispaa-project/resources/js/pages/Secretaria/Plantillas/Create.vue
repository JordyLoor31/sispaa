<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlantillaForm from './PlantillaForm.vue';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Plantilla" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Nueva Plantilla de Documento</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Sube un formato institucional para que cualquier estudiante pueda descargarlo desde su portal.
                </p>
            </div>

            <div class="max-w-xl mx-auto w-full rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <PlantillaForm />
            </div>
        </div>
    </AppSidebarLayout>
</template>
