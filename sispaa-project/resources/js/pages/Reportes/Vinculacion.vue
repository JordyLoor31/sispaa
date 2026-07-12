<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Handshake, Building2, Percent } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ApexChartCard from '@/components/charts/ApexChartCard.vue';
import type { ApexOptions } from 'apexcharts';

interface Catalogo { id: number; nombre: string }
interface ChartData { labels: string[]; series: number[] }

const props = defineProps<{
    kpis: {
        total_actividades: number;
        ejecutadas: number;
        porcentaje_ejecutadas: number;
        total_empresas: number;
    };
    charts: {
        porEstado: ChartData;
        porCarrera: ChartData;
        empresasPorSector: ChartData;
    };
    periodos: Catalogo[];
    carreras: Catalogo[];
    filters: { periodo_id?: string; carrera_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Reportes', href: route('reportes.index') },
    { title: 'Vinculación', href: route('reportes.vinculacion') },
];

const periodoId = ref(props.filters.periodo_id || 'all');
const carreraId = ref(props.filters.carrera_id || 'all');

const aplicar = () => {
    router.get(route('reportes.vinculacion'), {
        periodo_id: periodoId.value !== 'all' ? periodoId.value : undefined,
        carrera_id: carreraId.value !== 'all' ? carreraId.value : undefined,
    }, { preserveState: true, replace: true });
};

const barOptions = (labels: string[], colors?: string[]): ApexOptions => ({
    chart: { type: 'bar' },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
    dataLabels: { enabled: true },
    xaxis: { categories: labels, labels: { style: { colors: '#64748b' } } },
    yaxis: { labels: { style: { colors: '#64748b' } } },
    colors: colors ?? ['#6366f1'],
    grid: { borderColor: '#e2e8f0' },
});

const estadoColores: Record<string, string> = { ejecutado: '#10b981', pendiente: '#f59e0b' };
const pieOptions = (labels: string[], fallback?: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: labels.map((l, i) => estadoColores[l] ?? fallback?.[i] ?? '#6366f1'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Vinculación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reportes — Vinculación</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Actividades de vinculación y empresas beneficiadas.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Período</label>
                    <Select v-model="periodoId" @update:model-value="aplicar">
                        <SelectTrigger class="w-[200px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Carrera</label>
                    <Select v-model="carreraId" @update:model-value="aplicar">
                        <SelectTrigger class="w-[200px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Actividades</p>
                        <Handshake class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_actividades }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Ejecutadas</p>
                        <Handshake class="h-5 w-5 text-emerald-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.ejecutadas }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">% Ejecución</p>
                        <Percent class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.porcentaje_ejecutadas }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Empresas</p>
                        <Building2 class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_empresas }}</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <ApexChartCard
                    chart-id="vinculacion-por-estado"
                    title="Actividades por estado"
                    type="pie"
                    :series="charts.porEstado.series"
                    :options="pieOptions(charts.porEstado.labels)"
                    :empty="!hasData(charts.porEstado)"
                />
                <ApexChartCard
                    chart-id="vinculacion-por-carrera"
                    title="Actividades por carrera"
                    type="bar"
                    :series="[{ name: 'Actividades', data: charts.porCarrera.series }]"
                    :options="barOptions(charts.porCarrera.labels)"
                    :empty="!hasData(charts.porCarrera)"
                />
                <ApexChartCard
                    chart-id="empresas-por-sector"
                    title="Empresas beneficiadas por sector"
                    type="bar"
                    class="xl:col-span-2"
                    :series="[{ name: 'Empresas', data: charts.empresasPorSector.series }]"
                    :options="barOptions(charts.empresasPorSector.labels, ['#0ea5e9'])"
                    :empty="!hasData(charts.empresasPorSector)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
