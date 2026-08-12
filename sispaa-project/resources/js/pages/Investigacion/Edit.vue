<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import InvestigacionForm from './InvestigacionForm.vue';
import type { Catalogo, ProyectoEditable } from './types';

defineProps<{
    proyecto: ProyectoEditable;
    usuarios: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${proyecto.titulo}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Editar Proyecto</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ proyecto.titulo }}</p>
            </div>

            <div class="w-full max-w-5xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <InvestigacionForm :usuarios="usuarios" :proyecto="proyecto" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
