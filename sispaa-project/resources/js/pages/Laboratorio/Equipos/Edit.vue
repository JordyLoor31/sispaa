<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import EquipoForm from './EquipoForm.vue';
import type { Catalogo, EquipoItem } from './types';

const props = defineProps<{
    equipo: EquipoItem;
    laboratorios: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.equipo.nombre}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Equipo</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.equipo.nombre }}</p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <EquipoForm :equipo="equipo" :laboratorios="laboratorios" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
