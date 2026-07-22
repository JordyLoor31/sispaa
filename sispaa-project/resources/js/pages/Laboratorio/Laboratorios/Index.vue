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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Laboratorios</h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Espacios físicos disponibles para prácticas.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('laboratorio.laboratorios.create')">
                        <Plus class="h-4 w-4" /> Nuevo Laboratorio
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <div v-for="l in laboratorios" :key="l.id"
                    class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)] flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                            <MapPin class="h-4.5 w-4.5" />
                        </div>
                        <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', l.estado === 'activo' ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]' : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] text-[var(--sispaa-text)] opacity-70']">
                            {{ l.estado === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ l.nombre }}</h3>
                        <p v-if="l.ubicacion" class="text-xs opacity-70 text-[var(--sispaa-text)] mt-0.5">{{ l.ubicacion }}</p>
                        <p v-if="l.carrera" class="text-xs opacity-50 text-[var(--sispaa-text)] mt-1">{{ l.carrera }}</p>
                        <p v-if="l.capacidad" class="text-xs opacity-50 text-[var(--sispaa-text)]">Capacidad: {{ l.capacidad }}</p>
                        <p v-if="l.responsable" class="text-xs opacity-50 text-[var(--sispaa-text)]">Responsable: {{ l.responsable.name }}</p>
                    </div>
                    <div class="flex gap-3 text-xs opacity-70 text-[var(--sispaa-text)]">
                        <span>{{ l.equipos_count }} equipos</span>
                        <span>{{ l.reactivos_count }} reactivos</span>
                        <span>{{ l.practicas_count }} prácticas</span>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <button @click="toggleEstado(l)" class="text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)]">
                            {{ l.estado === 'activo' ? 'Desactivar' : 'Activar' }}
                        </button>
                        <Link :href="route('laboratorio.laboratorios.show', l.id)" class="ml-auto rounded-lg p-1.5 opacity-60 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <Eye class="h-3.5 w-3.5" />
                        </Link>
                        <Link :href="route('laboratorio.laboratorios.edit', l.id)" class="rounded-lg p-1.5 opacity-60 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <Pencil class="h-3.5 w-3.5" />
                        </Link>
                        <button @click="deleteTarget = l" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50">
                            <Trash2 class="h-3.5 w-3.5" />
                        </button>
                    </div>
                </div>

                <div v-if="laboratorios.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
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
