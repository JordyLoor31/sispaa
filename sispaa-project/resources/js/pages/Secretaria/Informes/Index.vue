<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { Search, FileText } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useDebounceFn } from '@vueuse/core';
import type { ApexOptions } from 'apexcharts';
import ApexChart from 'vue3-apexcharts';
import makeInformeColumns from './columns';
import type { InformeItem } from './types';

interface Paginated<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] }
interface Stats { total: number; aprobados: number; subidos: number; pendientes: number }
interface Carrera { id: number; nombre: string }

const props = defineProps<{
    informes: Paginated<InformeItem>;
    filters: { q?: string; carrera_id?: string; estado?: string; per_page?: string };
    stats: Stats;
    carreras: Carrera[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterCarrera = ref(props.filters.carrera_id || 'all');
const filterEstado = ref(props.filters.estado || 'all');

const applyFilters = () => {
    router.get(
        route('secretaria.informes.index'),
        {
            q: search.value || undefined,
            carrera_id: filterCarrera.value !== 'all' ? filterCarrera.value : undefined,
            estado: filterEstado.value !== 'all' ? filterEstado.value : undefined,
            per_page: props.informes.per_page,
        },
        { preserveState: true, replace: true },
    );
};
const debouncedSearch = useDebounceFn(applyFilters, 300);

const columns = makeInformeColumns();

const table = useVueTable(
    reactive({
        get data() {
            return props.informes.data;
        },
        columns,
        getCoreRowModel: getCoreRowModel(),
    }),
);

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};

// Gráfico: usa los conteos globales (stats), no solo la página actual.
const chartSeries = computed(() => [props.stats.aprobados, props.stats.subidos, props.stats.pendientes]);

const chartOptions = computed<ApexOptions>(() => ({
    chart: { type: 'pie', background: 'transparent', foreColor: '#353535' },
    labels: ['Cumplidos (aprobados)', 'Pendientes (subidos)', 'Incumplidos (pendientes)'],
    colors: ['#10b981', '#f59e0b', '#ef4444'],
    stroke: { colors: ['transparent'] },
    legend: { position: 'bottom', labels: { colors: 'var(--sispaa-text)' } },
    dataLabels: {
        enabled: true,
        formatter: (val: number) => Math.round(val) + '%',
        dropShadow: { enabled: true, top: 1, left: 1, blur: 1, color: '#000', opacity: 0.45 },
    },
    tooltip: { theme: 'dark', y: { formatter: (value: number) => value + ' informe(s)' } },
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Informes de Asignatura" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Informes de Asignatura</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Aprueba o rechaza los informes de asignatura subidos por los docentes.</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <p class="text-xs font-semibold text-amber-700 uppercase">Pendientes de revisión</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.subidos + stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Aprobados</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.aprobados }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <div class="flex flex-col gap-3 rounded-xl border border-slate-200/80 bg-white p-4 sm:flex-row dark:border-slate-800 dark:bg-slate-950">
                        <div class="relative min-w-[200px] flex-1">
                            <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar docente..."
                                class="w-full rounded-lg border-slate-200 bg-slate-50/30 pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                                @input="debouncedSearch"
                            />
                        </div>
                        <Select v-model="filterCarrera" @update:model-value="applyFilters">
                            <SelectTrigger class="w-[200px]"><SelectValue placeholder="Carrera" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todas las carreras</SelectItem>
                                <SelectItem v-for="carrera in carreras" :key="carrera.id" :value="carrera.id.toString()">
                                    {{ carrera.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="filterEstado" @update:model-value="applyFilters">
                            <SelectTrigger class="w-[160px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Todos</SelectItem>
                                <SelectItem value="subido">Subidos</SelectItem>
                                <SelectItem value="pendiente">Pendientes</SelectItem>
                                <SelectItem value="aprobado">Aprobados</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="overflow-hidden rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-slate-200/80 dark:border-slate-800">
                                        <TableHead v-for="header in hg.headers" :key="header.id" class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody class="divide-y divide-slate-100 text-sm dark:divide-slate-800">
                                    <template v-if="table.getRowModel().rows.length">
                                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-slate-50/40 dark:hover:bg-slate-900/20">
                                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <TableRow v-else>
                                        <TableCell :colspan="columns.length" class="h-32 text-center">
                                            <div class="flex flex-col items-center gap-2 text-slate-400">
                                                <FileText class="h-8 w-8" />
                                                <span class="text-sm font-medium">No hay informes que coincidan con el filtro</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4 dark:border-slate-800">
                            <span class="text-xs text-slate-500">Mostrando {{ informes.data.length }} de {{ informes.total }} informes</span>
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="link in informes.links"
                                    :key="link.label"
                                    :disabled="!link.url || link.active"
                                    v-html="link.label"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                    :class="[link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40']"
                                    @click="navigateToPage(link.url)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-6 rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <h2 class="text-md font-semibold mb-1 text-slate-900 dark:text-white">Resumen de Cumplimiento</h2>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Porcentajes por estado de informe (todos los que coinciden con el filtro de carrera)</p>

                        <div v-if="stats.total > 0" class="flex justify-center">
                            <ApexChart type="pie" width="100%" height="320" :options="chartOptions" :series="chartSeries" />
                        </div>
                        <div v-else class="py-10 text-center text-slate-400">No hay datos para graficar</div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
