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

// Color para el estado "pendiente": dorado #E4BC57 (fuera de la paleta base)
const estadoBadge = (estado: string) => {
    return estado === 'ejecutado'
        ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]'
        : 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]';
};

const estadoSelect = (estado: string) => {
    return estado === 'ejecutado'
        ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_35%,transparent)]'
        : 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)]';
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Actividades de Vinculación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Vinculación</h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Actividades de vinculación con la colectividad.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('vinculacion.actividades.create')">
                        <Plus class="h-4 w-4" /> Nueva Actividad
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,#E4BC57_65%,black)]">Pendientes</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,#E4BC57_65%,black)]">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">Ejecutadas</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">{{ stats.ejecutadas }}</p>
                </div>
            </div>

            <div class="w-full max-w-5xl space-y-4">
                <div class="flex gap-3">
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
                        class="flex flex-col gap-3 rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                        <div class="flex items-start justify-between">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                <Handshake class="h-4.5 w-4.5" />
                            </div>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(a.estado)]">
                                {{ a.estado === 'ejecutado' ? 'Ejecutada' : 'Pendiente' }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ a.nombre }}</h3>
                            <p class="mt-1 text-sm opacity-80 text-[var(--sispaa-text)]">{{ a.carrera }} · {{ a.periodo }}</p>
                            <p class="mt-1 text-sm opacity-80 text-[var(--sispaa-text)]">Líder: {{ a.docente_lider?.name }}</p>
                            <p v-if="a.empresa" class="text-sm opacity-70 text-[var(--sispaa-text)]">Empresa: {{ a.empresa }}</p>
                            <p v-if="a.fecha" class="text-sm opacity-70 text-[var(--sispaa-text)]">Fecha: {{ a.fecha }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <Select :model-value="a.estado" @update:model-value="val => changeEstado(a, val as string)">
                                <SelectTrigger :class="['h-8 w-full border-0 text-xs text-[var(--sispaa-text)]', estadoSelect(a.estado)]"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="pendiente">Pendiente</SelectItem>
                                    <SelectItem value="ejecutado">Ejecutado</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="flex items-center gap-2 border-t pt-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Link :href="route('vinculacion.actividades.show', a.id)" class="inline-flex items-center gap-1 text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)]">
                                <Eye class="h-3.5 w-3.5" /> Ver
                            </Link>
                            <Link :href="route('vinculacion.actividades.edit', a.id)" class="inline-flex items-center gap-1 text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)]">
                                <Pencil class="h-3.5 w-3.5" /> Editar
                            </Link>
                            <button @click="deleteTarget = a" class="ml-auto inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                                <Trash2 class="h-3.5 w-3.5" /> Eliminar
                            </button>
                        </div>
                    </div>

                    <div v-if="actividades.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
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
