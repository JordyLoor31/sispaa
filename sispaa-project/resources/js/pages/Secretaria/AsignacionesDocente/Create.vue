<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import AsignacionDocenteForm from './AsignacionDocenteForm.vue';
import type { Docente, MateriaOption, PeriodoOption } from './types';

defineProps<{
    docentes: Docente[];
    materias: MateriaOption[];
    periodos: PeriodoOption[];
    tiposRol: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Asignación de Docente" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Nueva Asignación de Docente</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Vincula un docente a una materia, período y grupo. Esto habilita sus pantallas de Mis Sílabos, Mis Informes y Mis Estudiantes.
                </p>
            </div>

            <div class="max-w-xl mx-auto w-full rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <AsignacionDocenteForm :docentes="docentes" :materias="materias" :periodos="periodos" :tipos-rol="tiposRol" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
