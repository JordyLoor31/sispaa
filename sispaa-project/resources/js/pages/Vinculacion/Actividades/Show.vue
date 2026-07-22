<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Handshake } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { Actividad } from './types';

const props = defineProps<{
    actividad: Actividad;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.actividad.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <Handshake class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ actividad.nombre }}</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">{{ actividad.carrera }} · {{ actividad.periodo }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child class="text-white bg-[var(--sispaa-accent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-accent)_85%,black)]">
                        <Link :href="route('vinculacion.actividades')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('vinculacion.actividades.edit', actividad.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="mx-auto grid w-full max-w-2xl gap-4 sm:gap-6 md:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <p
                        class="mt-2 text-sm font-semibold"
                        :class="actividad.estado === 'ejecutado'
                            ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]'
                            : 'text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]'"
                    >
                        {{ actividad.estado === 'ejecutado' ? 'Ejecutada' : 'Pendiente' }}
                    </p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Docente líder</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ actividad.docente_lider?.name ?? '—' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Empresa</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ actividad.empresa ?? 'Sin empresa asociada' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Fecha</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ actividad.fecha ?? '—' }}</p>
                </div>
            </div>

            <div class="mx-auto w-full max-w-2xl rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ actividad.creator?.name ?? '—' }}</p>
                        <p class="mt-0.5 text-xs opacity-50 text-[var(--sispaa-text)]">{{ formatDate(actividad.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Última actualización por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ actividad.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
