<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import ConvocatoriaForm from './ConvocatoriaForm.vue';
import type { Convocatoria } from './types';

const props = defineProps<{
    convocatoria: Convocatoria;
    modulos: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.convocatoria.titulo}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Convocatoria</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    {{ props.convocatoria.titulo }}
                </p>
            </div>

            <div class="max-w-xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <ConvocatoriaForm :convocatoria="convocatoria" :modulos="modulos" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
