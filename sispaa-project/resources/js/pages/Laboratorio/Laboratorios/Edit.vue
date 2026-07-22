<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import LaboratorioForm from './LaboratorioForm.vue';
import type { Catalogo, LaboratorioItem, Persona } from './types';

const props = defineProps<{
    laboratorio: LaboratorioItem;
    carreras: Catalogo[];
    responsables: Persona[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.laboratorio.nombre}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Laboratorio</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.laboratorio.nombre }}</p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <LaboratorioForm :laboratorio="laboratorio" :carreras="carreras" :responsables="responsables" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
