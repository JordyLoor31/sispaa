<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, GraduationCap } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { Titulacion } from './columns';

const props = defineProps<{
    titulacion: Titulacion;
    puedeGestionar: boolean;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_proceso: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]',
        defendido: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]',
        graduado: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]',
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ titulacion.estudiante.name }}</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Tutor: {{ titulacion.tutor.name }}</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button as-child variant="outline" class="border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] text-[var(--sispaa-text)]">
                        <Link :href="route('titulacion.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button v-if="puedeGestionar" as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('titulacion.edit', titulacion.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-6 sm:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(titulacion.estado)]">
                        {{ titulacion.estado.replace('_', ' ') }}
                    </span>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Fechas</h4>
                    <p class="mt-2 text-sm text-[var(--sispaa-text)] opacity-80">Inicio: {{ titulacion.fecha_inicio ?? '—' }}</p>
                    <p class="text-sm text-[var(--sispaa-text)] opacity-80">Graduación: {{ titulacion.fecha_graduacion ?? '—' }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="mb-2 text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Tema</h4>
                <p class="text-sm text-[var(--sispaa-text)] opacity-80">{{ titulacion.tema }}</p>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ titulacion.creator?.name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs opacity-60 text-[var(--sispaa-text)]">{{ formatDate(titulacion.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Última actualización por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ titulacion.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
