<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { BRAND_GRADIENT } from '@/lib/brand';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { useDebounceFn } from '@vueuse/core';
import type { ApexOptions } from 'apexcharts';
import { CheckCircle2, Clock3, Files, FileText, Search } from 'lucide-vue-next';
import { computed, reactive, ref } from 'vue';
import ApexChart from 'vue3-apexcharts';
import makeInformeColumns from './columns';
import type { InformeItem } from './types';

interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}
interface Stats {
    total: number;
    aprobados: number;
    subidos: number;
    pendientes: number;
}
interface Carrera {
    id: number;
    nombre: string;
}

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

        <div
            class="flex h-full flex-1 flex-col gap-4 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))] p-4 sm:gap-6 sm:p-6"
        >
            <!-- Header -->
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                    <FileText class="h-5 w-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Informes de Asignatura</h1>
                        <span
                            class="rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] px-2.5 py-0.5 text-xs font-bold text-[color:color-mix(in_srgb,var(--sispaa-primary)_60%,var(--sispaa-text))]"
                        >
                            {{ stats.total }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm text-[var(--sispaa-text)] opacity-60">
                        Aprueba o rechaza los informes de asignatura subidos por los docentes.
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div
                    class="flex items-center gap-3.5 rounded-2xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[var(--sispaa-background)] p-5 shadow-sm"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)]"
                    >
                        <Files class="h-5 w-5 text-[color:color-mix(in_srgb,var(--sispaa-primary)_70%,var(--sispaa-text))]" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[var(--sispaa-text)] opacity-50">Total</p>
                        <p class="text-2xl font-extrabold leading-tight text-[var(--sispaa-text)]">{{ stats.total }}</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3.5 rounded-2xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[var(--sispaa-background)] p-5 shadow-sm"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,#E4BC57_18%,transparent)]">
                        <Clock3 class="h-5 w-5 text-[color:color-mix(in_srgb,#E4BC57_60%,var(--sispaa-text))]" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]">
                            Pendientes
                        </p>
                        <p class="text-2xl font-extrabold leading-tight text-[var(--sispaa-text)]">{{ stats.subidos + stats.pendientes }}</p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3.5 rounded-2xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[var(--sispaa-background)] p-5 shadow-sm"
                >
                    <div
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_15%,transparent)]"
                    >
                        <CheckCircle2 class="h-5 w-5 text-[color:color-mix(in_srgb,var(--sispaa-secondary)_60%,var(--sispaa-text))]" />
                    </div>
                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wider text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]"
                        >
                            Aprobados
                        </p>
                        <p class="text-2xl font-extrabold leading-tight text-[var(--sispaa-text)]">{{ stats.aprobados }}</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta única a todo lo ancho: toolbar + tabla + paginación -->
            <div
                class="overflow-hidden rounded-2xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[var(--sispaa-background)] shadow-sm"
            >
                <div
                    class="flex flex-col gap-3 border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] p-4 sm:flex-row sm:flex-wrap sm:items-center"
                >
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-[var(--sispaa-text)] opacity-50" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar docente..."
                            class="rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))] pl-9"
                            @input="debouncedSearch"
                        />
                    </div>
                    <Select v-model="filterCarrera" @update:model-value="applyFilters">
                        <SelectTrigger
                            class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))] sm:w-[200px]"
                        >
                            <SelectValue placeholder="Carrera" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg">
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="carrera in carreras" :key="carrera.id" :value="carrera.id.toString()">
                                {{ carrera.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterEstado" @update:model-value="applyFilters">
                        <SelectTrigger
                            class="w-full rounded-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))] sm:w-[160px]"
                        >
                            <SelectValue placeholder="Estado" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg">
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="subido">Subidos</SelectItem>
                            <SelectItem value="pendiente">Pendientes</SelectItem>
                            <SelectItem value="aprobado">Aprobados</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow
                                v-for="hg in table.getHeaderGroups()"
                                :key="hg.id"
                                class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-text)_3%,transparent)]"
                            >
                                <TableHead
                                    v-for="header in hg.headers"
                                    :key="header.id"
                                    class="h-9 whitespace-nowrap px-3 text-xs font-semibold uppercase tracking-wider text-[var(--sispaa-text)] opacity-60"
                                >
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)] text-sm">
                            <template v-if="table.getRowModel().rows.length">
                                <TableRow
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                    class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]"
                                >
                                    <TableCell
                                        v-for="cell in row.getVisibleCells()"
                                        :key="cell.id"
                                        class="whitespace-nowrap px-3 py-2 text-[var(--sispaa-text)]"
                                    >
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-40">
                                    <div class="flex flex-col items-center justify-center gap-2 text-center">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]"
                                        >
                                            <FileText class="h-5 w-5 text-[var(--sispaa-text)] opacity-40" />
                                        </div>
                                        <p class="text-sm font-medium text-[var(--sispaa-text)] opacity-70">
                                            No hay informes que coincidan con el filtro.
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div
                    class="flex flex-col gap-3 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6"
                >
                    <span class="text-xs text-[var(--sispaa-text)] opacity-60"
                        >Mostrando {{ informes.data.length }} de {{ informes.total }} informes</span
                    >
                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="link in informes.links"
                            :key="link.label"
                            :disabled="!link.url || link.active"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[
                                link.active
                                    ? 'bg-[var(--sispaa-primary)] text-white shadow-sm'
                                    : 'border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-background)] text-[var(--sispaa-text)] hover:border-[var(--sispaa-primary)] hover:text-[var(--sispaa-primary)] disabled:opacity-50',
                            ]"
                            @click="navigateToPage(link.url)"
                        />
                    </div>
                </div>
            </div>

            <!-- Resumen de Cumplimiento: debajo de la tabla para dejarle todo el ancho -->
            <div
                class="rounded-2xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[var(--sispaa-background)] p-5 shadow-sm"
            >
                <h2 class="text-md mb-1 font-semibold text-[var(--sispaa-text)]">Resumen de Cumplimiento</h2>
                <p class="mb-4 text-sm text-[var(--sispaa-text)] opacity-60">
                    Porcentajes por estado de informe (todos los que coinciden con el filtro de carrera)
                </p>

                <div v-if="stats.total > 0" class="mx-auto flex w-full max-w-md justify-center">
                    <ApexChart type="pie" width="100%" height="320" :options="chartOptions" :series="chartSeries" />
                </div>
                <div v-else class="py-10 text-center text-[var(--sispaa-text)] opacity-50">No hay datos para graficar</div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
