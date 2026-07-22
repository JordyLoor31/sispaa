<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, MapPin } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { LaboratorioItem } from './types';

const props = defineProps<{
    laboratorio: LaboratorioItem;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.laboratorio.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <MapPin class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ laboratorio.nombre }}</h1>
                        <p v-if="laboratorio.ubicacion" class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">{{ laboratorio.ubicacion }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('laboratorio.laboratorios')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('laboratorio.laboratorios.edit', laboratorio.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Carrera</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ laboratorio.carrera ?? '—' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Responsable</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ laboratorio.responsable?.name ?? '—' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Capacidad</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ laboratorio.capacidad ?? '—' }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Uso</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ laboratorio.equipos_count }} equipos · {{ laboratorio.reactivos_count }} reactivos · {{ laboratorio.practicas_count }} prácticas</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ laboratorio.creator?.name ?? '—' }}</p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ formatDate(laboratorio.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Última actualización por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ laboratorio.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
