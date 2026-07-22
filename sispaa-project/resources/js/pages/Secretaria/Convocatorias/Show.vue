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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <Megaphone class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                            {{ convocatoria.titulo }}
                        </h1>
                        <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                            {{ convocatoria.modulo }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('secretaria.convocatorias.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('secretaria.convocatorias.edit', convocatoria.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="convocatoria.activa ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]' : 'opacity-50 text-[var(--sispaa-text)]'">
                        {{ convocatoria.activa ? 'Activa' : 'Inactiva' }}
                    </p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Tipo de documento</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ convocatoria.tipo_documento ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Ventana de fechas</h4>
                    <p class="mt-2 flex items-center gap-1.5 text-sm font-semibold text-[var(--sispaa-text)]">
                        <Calendar class="h-3.5 w-3.5 opacity-50" />
                        {{ convocatoria.fecha_inicio }} — {{ convocatoria.fecha_fin }}
                    </p>
                </div>
            </div>

            <div v-if="convocatoria.descripcion" class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-2">Descripción</h4>
                <p class="text-sm text-[var(--sispaa-text)]">{{ convocatoria.descripcion }}</p>
            </div>

            <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Auditoría</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">
                            {{ convocatoria.creator?.name ?? convocatoria.creadoPor?.name ?? '—' }}
                        </p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ formatDateTime(convocatoria.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Última actualización por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">
                            {{ convocatoria.updater?.name ?? '—' }}
                        </p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ formatDateTime(convocatoria.updated_at) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
