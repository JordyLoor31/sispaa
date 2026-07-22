<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { Search, FileText } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
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
    colors: ['#72c184', '#E4BC57', '#ef4444'],
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Informes de Asignatura</h1>
                <p class="mt-1 text-sm opacity-70 text-[var(--sispaa-text)]">Aprueba o rechaza los informes de asignatura subidos por los docentes.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,#E4BC57_65%,black)]">Pendientes de revisión</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,#E4BC57_65%,black)]">{{ stats.subidos + stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">Aprobados</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">{{ stats.aprobados }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-6">
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                        <div class="relative w-full max-w-sm">
                            <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                            <Input
                                v-model="search"
                                type="text"
                                placeholder="Buscar docente..."
                                class="pl-9"
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

                    <div class="overflow-hidden rounded-lg bg-[var(--sispaa-surface)]">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                        <TableHead v-for="header in hg.headers" :key="header.id" class="h-12 whitespace-nowrap px-4 text-sm font-medium opacity-70 text-[var(--sispaa-text)]">
                                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                                    <template v-if="table.getRowModel().rows.length">
                                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_6%,transparent)]">
                                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="whitespace-nowrap px-4 py-4 text-[var(--sispaa-text)]">
                                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <TableRow v-else>
                                        <TableCell :colspan="columns.length" class="h-32 text-center">
                                            <div class="flex flex-col items-center gap-2 opacity-50 text-[var(--sispaa-text)]">
                                                <FileText class="h-8 w-8" />
                                                <span class="text-sm font-medium">No hay informes que coincidan con el filtro</span>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                        <div class="flex flex-col gap-3 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                            <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ informes.data.length }} de {{ informes.total }} informes</span>
                            <div class="flex flex-wrap items-center gap-1">
                                <button
                                    v-for="link in informes.links"
                                    :key="link.label"
                                    :disabled="!link.url || link.active"
                                    v-html="link.label"
                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                    :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-70 hover:opacity-100 disabled:opacity-30']"
                                    @click="navigateToPage(link.url)"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-1">
                    <div class="sticky top-6 rounded-xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                        <h2 class="text-md font-semibold mb-1 text-[var(--sispaa-text)]">Resumen de Cumplimiento</h2>
                        <p class="text-sm opacity-60 mb-4 text-[var(--sispaa-text)]">Porcentajes por estado de informe (todos los que coinciden con el filtro de carrera)</p>

                        <div v-if="stats.total > 0" class="flex justify-center">
                            <ApexChart type="pie" width="100%" height="320" :options="chartOptions" :series="chartSeries" />
                        </div>
                        <div v-else class="py-10 text-center opacity-50 text-[var(--sispaa-text)]">No hay datos para graficar</div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
