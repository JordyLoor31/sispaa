<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Informes de Asignatura" />
        <div class="min-h-screen pl-3 pr-3 pt-2" :style="{ backgroundColor: 'var(--sispaa-background)' }">
            <!-- Header and Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: 'var(--sispaa-text)' }">Informes de Asignatura</h1>
                    <p class="mt-2 text-sm" :style="{ color: 'var(--sispaa-text)', opacity: 0.7 }">
                        Visualización y control de informes de asignatura por docentes
                    </p>
                </div>

                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium" :style="{ color: 'var(--sispaa-text)' }">Carrera:</span>
                        <Select v-model="selectedCarrera" @update:model-value="handleCarreraChange">
                            <SelectTrigger class="w-[180px] rounded-xl border-[var(--sispaa-text)]/20 bg-[var(--sispaa-surface)]">
                                <SelectValue placeholder="Todas las carreras" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="all">Todas las carreras</SelectItem>
                                <SelectItem v-for="carrera in carreras" :key="carrera.id" :value="carrera.id.toString()">
                                    {{ carrera.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium" :style="{ color: 'var(--sispaa-text)' }">Estado de informe:</span>
                        <Select v-model="selectedEstado" @update:model-value="handleEstadoChange">
                            <SelectTrigger class="w-[180px] rounded-xl border-[var(--sispaa-text)]/20 bg-[var(--sispaa-surface)]">
                                <SelectValue placeholder="Todos los estados" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="all">Todos los estados</SelectItem>
                                <SelectItem value="Cumplido">Cumplidos</SelectItem>
                                <SelectItem value="Pendiente">Pendientes (Subidos)</SelectItem>
                                <SelectItem value="Incumplido">Incumplidos</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Content grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: List of reports (TanStack + Shadcn Table) -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Listado de Informes</h2>
                    
                    <div class="rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="border-b border-slate-200/80 dark:border-slate-850">
                                        <TableHead v-for="header in headerGroup.headers" :key="header.id" class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                                    <template v-if="table.getRowModel().rows?.length">
                                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10">
                                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                            </TableCell>
                                        </TableRow>
                                    </template>
                                    <TableRow v-else>
                                        <TableCell :colspan="columns.length" class="h-24 text-center text-slate-500">
                                            No se encontraron informes.
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <!-- Paginación -->
                        <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-850 px-6 py-4">
                            <span class="text-xs text-slate-500">
                                Mostrando {{ informesPaginated.data.length }} de {{ informesPaginated.total }} informes
                            </span>
                            <div class="flex items-center gap-1">
                                <button
                                    v-for="link in informesPaginated.links"
                                    :key="link.label"
                                    @click="navigateToPage(link.url)"
                                    :disabled="!link.url || link.active"
                                    v-html="link.label"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border border-slate-200 bg-white text-slate-650 hover:bg-slate-50 disabled:opacity-50"
                                    :class="[
                                        link.active ? '!bg-indigo-600 !text-white' : ''
                                    ]"
                                ></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Chart -->
                <div class="lg:col-span-1 lg:pt-[44px]">
                    <div class="sticky top-6 rounded-xl border border-[var(--sispaa-text)]/10 bg-[var(--sispaa-surface)] p-5 shadow-sm">
                        <h2 class="text-md font-semibold mb-3 text-left" :style="{ color: 'var(--sispaa-text)' }">Resumen de Cumplimiento</h2>
                        <h3 class="text-sm font-normal mb-4 text-left" :style="{ color: 'var(--sispaa-text)' }">Porcentajes por estado de informe (Página actual)</h3>

                        <div v-if="chartSeries.reduce((a, b:any) => a + b, 0) > 0" class="flex justify-center">
                            <ApexChart
                                type="pie"
                                width="100%"
                                height="350"
                                :options="chartOptions"
                                :series="chartSeries"
                            />
                        </div>
                        <div v-else class="text-center py-10" :style="{ color: 'var(--sispaa-text)', opacity: 0.5 }">
                            No hay datos para graficar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import type { ApexOptions } from 'apexcharts';
import { computed, ref } from 'vue';
import ApexChart from 'vue3-apexcharts';
import { Head, router } from '@inertiajs/vue3';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import makeInformeColumns from './Informes/column';

interface Carrera {
    id: number;
    nombre: string;
}

interface Docente {
    id: number;
    name: string;
}

interface Materia {
    id: number;
    nombre: string;
    carrera: Carrera;
}

interface Periodo {
    id: number;
    nombre: string;
}

interface Informe {
    id: number;
    docente: Docente;
    materia: Materia;
    periodo: Periodo;
    estado: 'pendiente' | 'subido' | 'aprobado' | string;
    archivo_url: string | null;
}

interface PaginatedInformes {
    data: Informe[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

const props = defineProps<{
    informesPaginated: PaginatedInformes;
    carreras: Carrera[];
    filters: {
        carrera_id?: string;
        estado?: string;
        per_page?: string;
    };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Docencia', href: '/docencia' },
    { title: 'Informes de Asignatura', href: '/docencia/informes' },
];

const selectedCarrera = ref<string>(props.filters.carrera_id || 'all');
const selectedEstado = ref<string>(props.filters.estado || 'all');

const handleCarreraChange = (val: string) => {
    selectedCarrera.value = val;
    reloadPage();
};

const handleEstadoChange = (val: string) => {
    selectedEstado.value = val;
    reloadPage();
};

const reloadPage = () => {
    router.get(route('docencia.informes-asignaturas'), {
        carrera_id: selectedCarrera.value,
        estado: selectedEstado.value,
        per_page: props.informesPaginated.per_page
    }, {
        preserveState: true,
        replace: true
    });
};

const navigateToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true });
    }
};

const mapEstado = (dbEstado: string) => {
    switch (dbEstado) {
        case 'aprobado':
            return 'Cumplido';
        case 'subido':
            return 'Pendiente';
        case 'pendiente':
            return 'Incumplido';
        default:
            return dbEstado;
    }
};

const chartSeries = computed(() => {
    const counts: Record<string, number> = { Cumplido: 0, Pendiente: 0, Incumplido: 0 };
    props.informesPaginated.data.forEach(inf => {
        const est = mapEstado(inf.estado);
        if(counts[est] !== undefined) {
             counts[est]++;
        }
    });
    return [counts['Cumplido'], counts['Pendiente'], counts['Incumplido']];
});

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'pie',
        background: 'transparent',
        foreColor: '#353535',
    },
    labels: ['Cumplidos', 'Pendientes (Subidos)', 'Incumplidos'],
    colors: ['#10b981', '#f59e0b', '#ef4444'], // Acorde a la paleta principal
    stroke: {
        colors: ['transparent'],
    },
    legend: {
        position: 'bottom',
        labels: {
            colors: 'var(--sispaa-text)',
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function (val: number) {
            return Math.round(val) + "%"
        },
        dropShadow: {
            enabled: true,
            top: 1,
            left: 1,
            blur: 1,
            color: '#000',
            opacity: 0.45
        }
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function(value: number) {
                return value + " informe(s)";
            }
        }
    }
}));

const columns = makeInformeColumns(mapEstado);

const table = useVueTable({
    get data() { return props.informesPaginated.data },
    columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>
<style shadow>
</style>
