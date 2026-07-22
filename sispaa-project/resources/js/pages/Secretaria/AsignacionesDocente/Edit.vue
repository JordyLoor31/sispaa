<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import AsignacionDocenteForm from './AsignacionDocenteForm.vue';
import type { AsignacionDocenteItem, Docente, MateriaOption, PeriodoOption } from './types';

defineProps<{
    asignacion: AsignacionDocenteItem;
    docentes: Docente[];
    materias: MateriaOption[];
    periodos: PeriodoOption[];
    tiposRol: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Asignación de Docente" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Asignación de Docente</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ asignacion.docente.name }} — {{ asignacion.materia.nombre }}</p>
            </div>

            <div class="max-w-xl mx-auto w-full rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <AsignacionDocenteForm
                    :asignacion="asignacion"
                    :docentes="docentes"
                    :materias="materias"
                    :periodos="periodos"
                    :tipos-rol="tiposRol"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
