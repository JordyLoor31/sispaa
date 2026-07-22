<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Microscope } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { EquipoItem } from './types';

const props = defineProps<{
    equipo: EquipoItem;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.equipo.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <Microscope class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ equipo.nombre }}</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Código: {{ equipo.codigo }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('laboratorio.equipos')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('laboratorio.equipos.edit', equipo.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Laboratorio</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ equipo.laboratorio }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Cantidad</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ equipo.cantidad }}</p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider">Estado</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)] capitalize">{{ equipo.estado }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold opacity-60 text-[var(--sispaa-text)] uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ equipo.creator?.name ?? '—' }}</p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ formatDate(equipo.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Última actualización por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ equipo.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
