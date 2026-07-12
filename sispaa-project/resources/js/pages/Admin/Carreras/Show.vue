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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ carrera.nombre }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Código {{ carrera.codigo }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('admin.carreras.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        <Link :href="route('admin.carreras.edit', carrera.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="carrera.activa ? 'text-emerald-600' : 'text-slate-400'">
                        {{ carrera.activa ? 'Activa' : 'Inactiva' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Coordinador</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ carrera.coordinador?.name ?? 'No asignado' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Asignaturas / Usuarios</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ carrera.materias_count ?? 0 }} asignaturas · {{ carrera.usuarios_count ?? 0 }} usuarios
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <p class="text-slate-400 text-xs">Creado por</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ carrera.creator?.name ?? '—' }}
                            <span class="text-slate-400 font-normal">· {{ formatDate(carrera.created_at) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-slate-400 text-xs">Última edición por</p>
                        <p class="font-semibold text-slate-800 dark:text-slate-200">
                            {{ carrera.updater?.name ?? '—' }}
                            <span class="text-slate-400 font-normal">· {{ formatDate(carrera.updated_at) }}</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
