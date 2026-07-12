<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, FlaskConical, ChevronRight, Trash2, Pencil } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { ProyectoItem, Catalogo } from './types';

const props = defineProps<{
    proyectos: ProyectoItem[];
    periodos: Catalogo[];
    coordinadores: Catalogo[];
    filters: { estado?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterEstado = ref(props.filters.estado || 'all');
const applyFilter = () => {
    router.get(route('investigacion.index'), { estado: filterEstado.value !== 'all' ? filterEstado.value : undefined }, { preserveState: true, replace: true });
};

// ── Cambiar estado (acción inline, no amerita página propia) ─────────
const changeEstado = (p: ProyectoItem, estado: string) => {
    router.put(route('investigacion.update', p.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

// ── Eliminar ──────────────────────────────────────────
const deleteTarget = ref<ProyectoItem | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('investigacion.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Proyecto eliminado.'); deleteTarget.value = null; },
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_curso: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400',
        pausada: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        finalizada: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    };
    return map[estado] ?? map.en_curso;
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Investigación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Investigación</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Tus proyectos de investigación y los que supervisas como coordinador.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('investigacion.create')">
                        <Plus class="h-4 w-4" />
                        Nuevo Proyecto
                    </Link>
                </Button>
            </div>

            <div class="max-w-5xl w-full mx-auto space-y-4">
                <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <Select v-model="filterEstado" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[180px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los estados</SelectItem>
                            <SelectItem value="en_curso">En curso</SelectItem>
                            <SelectItem value="pausada">Pausada</SelectItem>
                            <SelectItem value="finalizada">Finalizada</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="p in proyectos" :key="p.id"
                        class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                        <div class="flex items-start justify-between">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                                <FlaskConical class="h-4.5 w-4.5" />
                            </div>
                            <span :class="['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(p.estado)]">
                                {{ p.estado.replace('_', ' ') }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ p.titulo }}</h3>
                            <p v-if="p.objetivo" class="text-xs text-slate-500 mt-1 line-clamp-2">{{ p.objetivo }}</p>
                            <p class="text-xs text-slate-400 mt-2">Docente: {{ p.docente.name }}</p>
                            <p class="text-xs text-slate-400">Coordinador: {{ p.coordinador.name }} · {{ p.periodo }}</p>
                            <p class="text-xs text-slate-400 mt-1">Hitos: {{ p.hitos_completados }}/{{ p.total_hitos }} completados</p>
                        </div>

                        <div v-if="p.es_propio || p.es_coordinador" class="flex items-center gap-2">
                            <Select :model-value="p.estado" @update:model-value="val => changeEstado(p, val as string)">
                                <SelectTrigger class="w-full h-8 text-xs"><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="en_curso">En curso</SelectItem>
                                    <SelectItem value="pausada">Pausada</SelectItem>
                                    <SelectItem value="finalizada">Finalizada</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                            <Link :href="route('investigacion.hitos', p.id)"
                                class="inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-500 font-semibold">
                                Ver hitos y seguimiento <ChevronRight class="h-3.5 w-3.5" />
                            </Link>
                            <div class="ml-auto flex items-center gap-1" v-if="p.es_propio">
                                <Link :href="route('investigacion.edit', p.id)" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                    <Pencil class="h-3.5 w-3.5" />
                                </Link>
                                <button @click="deleteTarget = p" class="rounded-lg p-1.5 text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/20">
                                    <Trash2 class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-if="proyectos.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                        No hay proyectos de investigación todavía.
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmar eliminación -->
        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar proyecto?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.titulo }}" y sus hitos/seguimiento. Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
