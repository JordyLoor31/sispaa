<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, MapPin, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { LaboratorioItem } from './types';

defineProps<{
    laboratorios: LaboratorioItem[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const toggleEstado = (l: LaboratorioItem) => {
    router.put(route('laboratorio.laboratorios.update', l.id), { estado: l.estado === 'activo' ? 'inactivo' : 'activo' }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const deleteTarget = ref<LaboratorioItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('laboratorio.laboratorios.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Laboratorio eliminado.'); deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Laboratorios" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Laboratorios</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Espacios físicos disponibles para prácticas.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('laboratorio.laboratorios.create')">
                        <Plus class="h-4 w-4" /> Nuevo Laboratorio
                    </Link>
                </Button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="l in laboratorios" :key="l.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <MapPin class="h-4.5 w-4.5" />
                        </div>
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', l.estado === 'activo' ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400']">
                            {{ l.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ l.nombre }}</h3>
                        <p v-if="l.ubicacion" class="text-xs text-slate-500 mt-0.5">{{ l.ubicacion }}</p>
                        <p v-if="l.carrera" class="text-xs text-slate-400 mt-1">{{ l.carrera }}</p>
                        <p v-if="l.capacidad" class="text-xs text-slate-400">Capacidad: {{ l.capacidad }}</p>
                        <p v-if="l.responsable" class="text-xs text-slate-400">Responsable: {{ l.responsable.name }}</p>
                    </div>
                    <div class="flex gap-3 text-xs text-slate-500">
                        <span>{{ l.equipos_count }} equipos</span>
                        <span>{{ l.reactivos_count }} reactivos</span>
                        <span>{{ l.practicas_count }} prácticas</span>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <button @click="toggleEstado(l)" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">
                            {{ l.estado === 'activo' ? 'Desactivar' : 'Activar' }}
                        </button>
                        <Link :href="route('laboratorio.laboratorios.show', l.id)" class="ml-auto rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                            <Eye class="h-3.5 w-3.5" />
                        </Link>
                        <Link :href="route('laboratorio.laboratorios.edit', l.id)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                            <Pencil class="h-3.5 w-3.5" />
                        </Link>
                        <button @click="deleteTarget = l" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div v-if="laboratorios.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay laboratorios registrados.
                </div>
            </div>
        </div>

        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar laboratorio?</AlertDialogTitle>
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
