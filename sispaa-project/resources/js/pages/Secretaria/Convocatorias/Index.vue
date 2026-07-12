<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Calendar, Megaphone, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { Convocatoria } from './types';

const props = defineProps<{
    convocatorias: Convocatoria[];
    filters: { modulo?: string };
    modulos: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterModulo = ref(props.filters.modulo || 'all');
const applyFilter = () => {
    router.get(route('secretaria.convocatorias.index'), {
        modulo: filterModulo.value !== 'all' ? filterModulo.value : undefined,
    }, { preserveState: true, replace: true });
};

const deleteTarget = ref<Convocatoria | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('secretaria.convocatorias.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Convocatoria eliminada.');
            deleteTarget.value = null;
        },
    });
};

const estadoBadge = (activa: boolean) =>
    activa
        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400'
        : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500';
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Convocatorias" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Convocatorias y Fechas Límite</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Define ventanas de tiempo para la entrega de documentos por módulo.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('secretaria.convocatorias.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Convocatoria
                    </Link>
                </Button>
            </div>

            <div class="max-w-5xl w-full space-y-4">
                <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <Select v-model="filterModulo" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[200px]"><SelectValue placeholder="Módulo" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los módulos</SelectItem>
                            <SelectItem v-for="m in modulos" :key="m" :value="m">{{ m }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="c in convocatorias" :key="c.id"
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                                <Megaphone class="h-4.5 w-4.5" />
                            </div>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(c.activa)]">
                                {{ c.activa ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ c.titulo }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ c.modulo }}</p>
                            <p v-if="c.descripcion" class="text-xs text-slate-400 mt-2">{{ c.descripcion }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-auto pt-2 border-t border-slate-100 dark:border-slate-800">
                            <Calendar class="h-3.5 w-3.5" />
                            {{ c.fecha_inicio }} — {{ c.fecha_fin }}
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <Link :href="route('secretaria.convocatorias.show', c.id)"
                                class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 border border-slate-200 hover:border-indigo-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 transition-colors">
                                <Eye class="h-3.5 w-3.5" /> Ver
                            </Link>
                            <Link :href="route('secretaria.convocatorias.edit', c.id)"
                                class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 border border-slate-200 hover:border-indigo-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 transition-colors">
                                <Pencil class="h-3.5 w-3.5" /> Editar
                            </Link>
                            <button @click="deleteTarget = c" class="inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-600 border border-rose-200 hover:border-rose-300 dark:border-rose-900/40 rounded-lg px-2.5 py-1.5 transition-colors">
                                <Trash2 class="h-3.5 w-3.5" /> Eliminar
                            </button>
                        </div>
                    </div>

                    <div v-if="convocatorias.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                        No hay convocatorias registradas.
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmar eliminación -->
        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar convocatoria?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Se eliminará "{{ deleteTarget?.titulo }}" permanentemente. Esta acción no se puede deshacer.
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
