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
    planificado: 'text-[color:color-mix(in_srgb,#E4BC57_65%,black)]',
    activo: 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
    finalizado: 'opacity-50 text-[var(--sispaa-text)]',
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        {{ periodo.nombre }}
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)] capitalize">
                        Periodo {{ periodo.tipo }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button v-if="periodo.estado === 'planificado'" @click="activar" class="text-white bg-[var(--sispaa-secondary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)]">
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
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('admin.periodos.edit', periodo.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="ESTADO_CLASS[periodo.estado]">
                        {{ ESTADO_LABEL[periodo.estado] }}
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Duración</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ formatDate(periodo.fecha_inicio) }} — {{ formatDate(periodo.fecha_fin) }}
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Matriculados</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ totalMatriculados ?? 0 }} estudiantes
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider capitalize opacity-60 text-[var(--sispaa-text)]">Tipo</h4>
                    <p class="mt-2 text-sm font-semibold capitalize text-[var(--sispaa-text)]">
                        {{ periodo.tipo }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider mb-3 flex items-center gap-1.5 opacity-60 text-[var(--sispaa-text)]">
                    <Clock class="h-3.5 w-3.5" /> Fechas Límites
                </h4>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Fecha Límite Sílabos</p>
                        <p class="font-semibold" :class="periodo.fecha_limite_silabo ? 'text-[var(--sispaa-text)]' : 'italic opacity-50 text-[var(--sispaa-text)]'">
                            {{ periodo.fecha_limite_silabo ? formatDate(periodo.fecha_limite_silabo) : 'Sin configurar' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Fecha Límite Informes</p>
                        <p class="font-semibold" :class="periodo.fecha_limite_informe ? 'text-[var(--sispaa-text)]' : 'italic opacity-50 text-[var(--sispaa-text)]'">
                            {{ periodo.fecha_limite_informe ? formatDate(periodo.fecha_limite_informe) : 'Sin configurar' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider mb-3 opacity-60 text-[var(--sispaa-text)]">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="font-semibold text-[var(--sispaa-text)]">
                            {{ periodo.creator?.name ?? '—' }}
                            <span class="font-normal opacity-60 text-[var(--sispaa-text)]">· {{ formatDate(periodo.created_at) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Última edición por</p>
                        <p class="font-semibold text-[var(--sispaa-text)]">
                            {{ periodo.updater?.name ?? '—' }}
                            <span class="font-normal opacity-60 text-[var(--sispaa-text)]">· {{ formatDate(periodo.updated_at) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
