<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Handshake, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { Actividad } from './types';

const props = defineProps<{
    actividades: Actividad[];
    filters: { estado?: string };
    stats: { pendientes: number; ejecutadas: number; total: number };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterEstado = ref(props.filters.estado || 'all');
const applyFilter = () => {
    router.get(route('vinculacion.actividades'), { estado: filterEstado.value !== 'all' ? filterEstado.value : undefined }, { preserveState: true, replace: true });
};

const changeEstado = (a: Actividad, estado: string) => {
    router.put(route('vinculacion.actividades.update', a.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const deleteTarget = ref<Actividad | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('vinculacion.actividades.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Actividad eliminada.'); deleteTarget.value = null; },
    });
};

const estadoBadge = (estado: string) => {
    return estado === 'ejecutado'
        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400'
        : 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400';
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Actividades de Vinculación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Vinculación</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Actividades de vinculación con la colectividad.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('vinculacion.actividades.create')">
                        <Plus class="h-4 w-4" /> Nueva Actividad
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <p class="text-xs font-semibold text-amber-700 uppercase">Pendientes</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Ejecutadas</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.ejecutadas }}</p>
                </div>
            </div>

            <div class="max-w-5xl w-full space-y-4">
                <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <Select v-model="filterEstado" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[180px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los estados</SelectItem>
                            <SelectItem value="pendiente">Pendientes</SelectItem>
                            <SelectItem value="ejecutado">Ejecutadas</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="a in actividades" :key="a.id"
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                                <Handshake class="h-4.5 w-4.5" />
                            </div>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(a.estado)]">
                                {{ a.estado === 'ejecutado' ? 'Ejecutada' : 'Pendiente' }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ a.nombre }}</h3>
                            <p class="text-xs text-slate-500 mt-1">{{ a.carrera }} · {{ a.periodo }}</p>
                            <p class="text-xs text-slate-400 mt-1">Líder: {{ a.docente_lider.name }}</p>
                            <p v-if="a.empresa" class="text-xs text-slate-400">Empresa: {{ a.empresa }}</p>
                            <p v-if="a.fecha" class="text-xs text-slate-400">Fecha: {{ a.fecha }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Select :model-value="a.estado" @update:model-value="val => changeEstado(a, val as string)">
                                <SelectTrigger class="w-full h-8 text-xs"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pendiente">Pendiente</SelectItem>
                                    <SelectItem value="ejecutado">Ejecutado</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <Link :href="route('vinculacion.actividades.show', a.id)" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold">
                                <Eye class="h-3.5 w-3.5" /> Ver
                            </Link>
                            <Link :href="route('vinculacion.actividades.edit', a.id)" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold">
                                <Pencil class="h-3.5 w-3.5" /> Editar
                            </Link>
                            <button @click="deleteTarget = a" class="ml-auto inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-600 font-semibold">
                                <Trash2 class="h-3.5 w-3.5" /> Eliminar
                            </button>
                        </div>
                    </div>

                    <div v-if="actividades.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                        No hay actividades de vinculación registradas.
                    </div>
                </div>
            </div>
        </div>

        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar actividad?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.nombre }}". Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
