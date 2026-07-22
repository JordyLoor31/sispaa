<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

interface Creator {
    id: number;
    name: string;
}

interface Carrera {
    id: number;
    nombre: string;
    codigo: string;
    color?: string | null;
    activa: boolean;
    coordinador?: { id: number; name: string } | null;
    materias_count?: number;
    usuarios_count?: number;
    creator?: Creator | null;
    updater?: Creator | null;
    created_at?: string;
    updated_at?: string;
}

const props = defineProps<{
    carrera: Carrera;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.carrera.nombre" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        {{ carrera.nombre }}
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Código {{ carrera.codigo }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('admin.carreras.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('admin.carreras.edit', carrera.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-4">
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="carrera.activa ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]' : 'opacity-50 text-[var(--sispaa-text)]'">
                        {{ carrera.activa ? 'Activa' : 'Inactiva' }}
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Etiqueta / Color</h4>
                    <div class="mt-2 flex items-center gap-2">
                        <span
                            class="inline-block h-4 w-4 shrink-0 rounded-full border border-black/10"
                            :style="{ backgroundColor: carrera.color ?? '#94a3b8' }"
                        />
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">
                            {{ carrera.color ?? 'Sin asignar' }}
                        </p>
                    </div>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Coordinador</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ carrera.coordinador?.name ?? 'No asignado' }}
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">Asignaturas / Usuarios</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ carrera.materias_count ?? 0 }} asignaturas · {{ carrera.usuarios_count ?? 0 }} usuarios
                    </p>
                </div>
            </div>

            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider mb-3 opacity-60 text-[var(--sispaa-text)]">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Creado por</p>
                        <p class="font-semibold text-[var(--sispaa-text)]">
                            {{ carrera.creator?.name ?? '—' }}
                            <span class="font-normal opacity-60 text-[var(--sispaa-text)]">· {{ formatDate(carrera.created_at) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Última edición por</p>
                        <p class="font-semibold text-[var(--sispaa-text)]">
                            {{ carrera.updater?.name ?? '—' }}
                            <span class="font-normal opacity-60 text-[var(--sispaa-text)]">· {{ formatDate(carrera.updated_at) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
