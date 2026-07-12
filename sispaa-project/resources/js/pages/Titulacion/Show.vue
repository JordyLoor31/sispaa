<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, GraduationCap } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { Titulacion } from './columns';

const props = defineProps<{
    titulacion: Titulacion;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_proceso: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        defendido: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400',
        graduado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    };
    return map[estado] ?? map.en_proceso;
};

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="titulacion.estudiante.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ titulacion.estudiante.name }}</h1>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Tutor: {{ titulacion.tutor.name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('titulacion.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        <Link :href="route('titulacion.edit', titulacion.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(titulacion.estado)]">
                        {{ titulacion.estado.replace('_', ' ') }}
                    </span>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fechas</h4>
                    <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">Inicio: {{ titulacion.fecha_inicio ?? '—' }}</p>
                    <p class="text-sm text-slate-700 dark:text-slate-300">Graduación: {{ titulacion.fecha_graduacion ?? '—' }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Tema</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ titulacion.tema }}</p>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Creado por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ titulacion.creator?.name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ formatDate(titulacion.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Última actualización por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ titulacion.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
