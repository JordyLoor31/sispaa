<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import FaltaSemanalForm from './FaltaSemanalForm.vue';
import type { CarreraOption, FaltaSemanalItem, PeriodoOption } from './types';

const props = defineProps<{
    falta: FaltaSemanalItem;
    carreras: CarreraOption[];
    periodos: PeriodoOption[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Registro de Faltas Semanales" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Registro de Faltas Semanales</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.falta.carrera.nombre }} — Semana {{ props.falta.semana }}</p>
            </div>

            <div class="max-w-xl mx-auto w-full rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <FaltaSemanalForm :falta="props.falta" :carreras="carreras" :periodos="periodos" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
