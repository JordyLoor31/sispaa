<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, PlayCircle, FlagTriangleRight, Clock } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { EstadoPeriodo } from './columns';

interface Creator {
    id: number;
    name: string;
}

interface Periodo {
    id: number;
    nombre: string;
    fecha_inicio: string;
    fecha_fin: string;
    tipo: string;
    estado: EstadoPeriodo;
    fecha_limite_silabo: string | null;
    fecha_limite_informe: string | null;
    creator?: Creator | null;
    updater?: Creator | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    periodo: Periodo;
    totalMatriculados?: number;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const ESTADO_LABEL: Record<EstadoPeriodo, string> = {
    planificado: 'Planificado',
    activo: 'Activo',
    finalizado: 'Finalizado',
};

const ESTADO_CLASS: Record<EstadoPeriodo, string> = {
    planificado: 'text-amber-600',
    activo: 'text-emerald-600',
    finalizado: 'text-slate-400',
};

const formatDate = (date?: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const activar = () => {
    router.post(route('admin.periodos.activar', props.periodo.id), {}, { preserveScroll: true });
};

const finalizar = () => {
    router.post(route('admin.periodos.finalizar', props.periodo.id), {}, { preserveScroll: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="periodo.nombre" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ periodo.nombre }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400 capitalize">
                        Periodo {{ periodo.tipo }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="periodo.estado === 'planificado'" @click="activar" class="bg-emerald-600 hover:bg-emerald-500 text-white">
                        <PlayCircle class="h-4 w-4 mr-1.5" /> Activar
                    </Button>
                    <Button v-else-if="periodo.estado === 'activo'" @click="finalizar" variant="outline" class="text-rose-500 border-rose-200 hover:bg-rose-50">
                        <FlagTriangleRight class="h-4 w-4 mr-1.5" /> Finalizar
                    </Button>
                    <Button as-child variant="outline">
                        <Link :href="route('admin.periodos.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        <Link :href="route('admin.periodos.edit', periodo.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="ESTADO_CLASS[periodo.estado]">
                        {{ ESTADO_LABEL[periodo.estado] }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Duración</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ formatDate(periodo.fecha_inicio) }} — {{ formatDate(periodo.fecha_fin) }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Matriculados</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ totalMatriculados ?? 0 }} estudiantes
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider capitalize">Tipo</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200 capitalize">
                        {{ periodo.tipo }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <Clock class="h-3.5 w-3.5" /> Fechas Límites
                </h4>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs">Fecha Límite Sílabos</p>
                        <p class="font-semibold" :class="periodo.fecha_limite_silabo ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 italic'">
                            {{ periodo.fecha_limite_silabo ? formatDate(periodo.fecha_limite_silabo) : 'Sin configurar' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs">Fecha Límite Informes</p>
                        <p class="font-semibold" :class="periodo.fecha_limite_informe ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 italic'">
                            {{ periodo.fecha_limite_informe ? formatDate(periodo.fecha_limite_informe) : 'Sin configurar' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs">Creado por</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ periodo.creator?.name ?? '—' }}
                            <span class="text-slate-400 font-normal">· {{ formatDate(periodo.created_at) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs">Última edición por</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ periodo.updater?.name ?? '—' }}
                            <span class="text-slate-400 font-normal">· {{ formatDate(periodo.updated_at) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
