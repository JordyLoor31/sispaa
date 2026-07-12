<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Calendar, Megaphone } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { Convocatoria } from './types';

const props = defineProps<{
    convocatoria: Convocatoria;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDateTime = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.convocatoria.titulo" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <Megaphone class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {{ convocatoria.titulo }}
                        </h1>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ convocatoria.modulo }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('secretaria.convocatorias.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        <Link :href="route('secretaria.convocatorias.edit', convocatoria.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="convocatoria.activa ? 'text-emerald-600' : 'text-slate-400'">
                        {{ convocatoria.activa ? 'Activa' : 'Inactiva' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tipo de documento</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ convocatoria.tipo_documento ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Ventana de fechas</h4>
                    <p class="mt-2 flex items-center gap-1.5 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        <Calendar class="h-3.5 w-3.5 text-slate-400" />
                        {{ convocatoria.fecha_inicio }} — {{ convocatoria.fecha_fin }}
                    </p>
                </div>
            </div>

            <div v-if="convocatoria.descripcion" class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Descripción</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ convocatoria.descripcion }}</p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Creado por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                            {{ convocatoria.creator?.name ?? convocatoria.creadoPor?.name ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ formatDateTime(convocatoria.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Última actualización por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                            {{ convocatoria.updater?.name ?? '—' }}
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ formatDateTime(convocatoria.updated_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
