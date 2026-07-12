<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Search, Plus, ChevronDown, ClipboardList } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import type { ColumnDef } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/components/ui/dialog';
import { toast } from 'vue-sonner';
import { useDebounceFn } from '@vueuse/core';
import type { Periodo, Carrera, MatriculaItem } from './types';

interface PaginatedMatriculas {
    data: MatriculaItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}
interface Stats { activos: number; retirados: number; egresados: number; total: number }

const props = defineProps<{
    matriculas: PaginatedMatriculas;
    periodos: Periodo[];
    carreras: Carrera[];
    filters: { q?: string; estado?: string; periodo_id?: string; carrera_id?: string; per_page?: string };
    stats: Stats;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterEstado = ref(props.filters.estado || 'all');
const filterPeriodo = ref(props.filters.periodo_id || 'all');
const filterCarrera = ref(props.filters.carrera_id || 'all');

const applyFilters = () => {
    router.get(route('secretaria.matriculas.index'), {
        q: search.value || undefined,
        estado: filterEstado.value !== 'all' ? filterEstado.value : undefined,
        periodo_id: filterPeriodo.value !== 'all' ? filterPeriodo.value : undefined,
        carrera_id: filterCarrera.value !== 'all' ? filterCarrera.value : undefined,
        per_page: props.matriculas.per_page,
    }, { preserveState: true, replace: true });
};
const debouncedSearch = useDebounceFn(applyFilters, 300);

// ── Cambio de Estado (acción inline, no amerita página propia) ──────────
const changeEstadoItem = ref<MatriculaItem | null>(null);
const estadoForm = useForm({ estado: '' as 'activo' | 'retirado' | 'egresado' });

const openChangeEstado = (item: MatriculaItem) => {
    changeEstadoItem.value = item;
    estadoForm.estado = item.estado;
};

const submitChangeEstado = () => {
    if (!changeEstadoItem.value) return;
    estadoForm.patch(route('secretaria.matriculas.update-estado', changeEstadoItem.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Estado de matrícula actualizado.');
            changeEstadoItem.value = null;
        },
        onError: () => toast.error('Error al actualizar el estado.'),
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        activo: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        retirado: 'bg-rose-50 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400',
        egresado: 'bg-blue-50 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400',
    };
    return map[estado] ?? 'bg-slate-100 text-slate-500';
};

const columns: ColumnDef<MatriculaItem>[] = [
    { id: 'estudiante', header: 'Estudiante', cell: ({ row }) => row.original.estudiante },
    { id: 'carrera', header: 'Carrera', cell: ({ row }) => row.original.carrera },
    { id: 'periodo', header: 'Período', cell: ({ row }) => row.original.periodo },
    { accessorKey: 'estado', header: 'Estado', cell: ({ row }) => row.original.estado },
    { accessorKey: 'fecha_matricula', header: 'Fecha Matrícula', cell: ({ row }) => row.original.fecha_matricula },
    { id: 'actions', header: 'Acciones', cell: ({ row }) => row.original },
];

const table = useVueTable(reactive({
    get data() { return props.matriculas.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Registro de Matrículas" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Registro de Matrículas
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Inscribe estudiantes en materias y períodos académicos.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('secretaria.matriculas.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Matrícula
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Activos</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.activos }}</p>
                </div>
                <div class="rounded-2xl border border-rose-200/60 bg-rose-50/40 p-5 shadow-sm dark:border-rose-900/30 dark:bg-rose-950/10">
                    <p class="text-xs font-semibold text-rose-700 uppercase">Retirados</p>
                    <p class="mt-1 text-3xl font-extrabold text-rose-700 dark:text-rose-300">{{ stats.retirados }}</p>
                </div>
                <div class="rounded-2xl border border-blue-200/60 bg-blue-50/40 p-5 shadow-sm dark:border-blue-900/30 dark:bg-blue-950/10">
                    <p class="text-xs font-semibold text-blue-700 uppercase">Egresados</p>
                    <p class="mt-1 text-3xl font-extrabold text-blue-700 dark:text-blue-300">{{ stats.egresados }}</p>
                </div>
            </div>

            <div class="max-w-5xl w-full space-y-4">
                <div class="flex flex-col sm:flex-row gap-3 flex-wrap bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <div class="flex-1 relative min-w-[200px]">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Buscar estudiante, cédula..."
                            class="pl-9 w-full rounded-lg border-slate-200 bg-slate-50/30 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                        />
                    </div>
                    <Select v-model="filterEstado" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[140px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="activo">Activo</SelectItem>
                            <SelectItem value="retirado">Retirado</SelectItem>
                            <SelectItem value="egresado">Egresado</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterPeriodo" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[180px]"><SelectValue placeholder="Período" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los períodos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterCarrera" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[180px]"><SelectValue placeholder="Carrera" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.codigo }} — {{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                    class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                                    <TableHead v-for="header in hg.headers" :key="header.id"
                                        class="py-3.5 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                                <template v-if="table.getRowModel().rows.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                        class="hover:bg-slate-50/40 dark:hover:bg-slate-900/20 transition-colors">
                                        <TableCell class="py-3.5 px-4">
                                            <div class="font-semibold text-slate-900 dark:text-white">{{ row.original.estudiante.name }}</div>
                                            <div class="text-xs text-slate-400">{{ row.original.estudiante.cedula ?? row.original.estudiante.email }}</div>
                                        </TableCell>
                                        <TableCell class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-300">
                                            {{ row.original.carrera.codigo }} — {{ row.original.carrera.nombre }}
                                        </TableCell>
                                        <TableCell class="py-3.5 px-4 text-xs text-slate-600 dark:text-slate-300">
                                            {{ row.original.periodo.nombre }}
                                        </TableCell>
                                        <TableCell class="py-3.5 px-4">
                                            <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(row.original.estado)]">
                                                {{ row.original.estado.charAt(0).toUpperCase() + row.original.estado.slice(1) }}
                                            </span>
                                        </TableCell>
                                        <TableCell class="py-3.5 px-4 text-xs text-slate-500">{{ row.original.fecha_matricula }}</TableCell>
                                        <TableCell class="py-3.5 px-4">
                                            <button
                                                @click="openChangeEstado(row.original)"
                                                class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 border border-slate-200 hover:border-indigo-300 dark:border-slate-700 rounded-lg px-2.5 py-1.5 transition-colors"
                                            >
                                                <ChevronDown class="h-3.5 w-3.5" />
                                                Cambiar estado
                                            </button>
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="6" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 text-slate-400">
                                            <ClipboardList class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay matrículas registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                        <span class="text-xs text-slate-500">Mostrando {{ matriculas.data.length }} de {{ matriculas.total }} matrículas</span>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in matriculas.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                :class="[link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40']"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog: Cambiar Estado -->
        <Dialog :open="!!changeEstadoItem" @update:open="val => { if (!val) changeEstadoItem = null; }">
            <DialogContent class="sm:max-w-sm">
                <DialogHeader>
                    <DialogTitle>Cambiar estado de matrícula</DialogTitle>
                    <DialogDescription v-if="changeEstadoItem">
                        {{ changeEstadoItem.estudiante.name }} — {{ changeEstadoItem.periodo.nombre }}
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nuevo estado</label>
                    <Select v-model="estadoForm.estado">
                        <SelectTrigger class="w-full">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="activo">Activo</SelectItem>
                            <SelectItem value="retirado">Retirado</SelectItem>
                            <SelectItem value="egresado">Egresado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <DialogFooter class="gap-2">
                    <Button variant="outline" @click="changeEstadoItem = null">Cancelar</Button>
                    <Button @click="submitChangeEstado" :disabled="estadoForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        {{ estadoForm.processing ? 'Guardando...' : 'Guardar cambio' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppSidebarLayout>
</template>
