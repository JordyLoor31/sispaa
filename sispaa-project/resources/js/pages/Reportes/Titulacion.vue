<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { GraduationCap, Award, Percent } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ApexChartCard from '@/components/charts/ApexChartCard.vue';
import type { ApexOptions } from 'apexcharts';

interface ChartData { labels: string[]; series: number[] }

const props = defineProps<{
    kpis: { total: number; graduados: number; porcentaje_graduados: number };
    charts: {
        porEstado: ChartData;
        porTutor: ChartData;
        porAnio: ChartData;
    };
    filters: { estado?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Reportes', href: route('reportes.index') },
    { title: 'Titulación', href: route('reportes.titulacion') },
];

const estado = ref(props.filters.estado || 'all');

const aplicar = () => {
    router.get(route('reportes.titulacion'), {
        estado: estado.value !== 'all' ? estado.value : undefined,
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

const estadoColores: Record<string, string> = { graduado: '#10b981', defendido: '#0ea5e9', en_proceso: '#f59e0b' };
const pieOptions = (labels: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels: labels.map((l) => l.replace('_', ' ')),
    colors: labels.map((l) => estadoColores[l] ?? '#6366f1'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Titulación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reportes — Titulación</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Avance de los procesos de titulación por estado, tutor y año.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Estado</label>
                    <Select v-model="estado" @update:model-value="aplicar">
                        <SelectTrigger class="w-[200px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="en_proceso">En proceso</SelectItem>
                            <SelectItem value="defendido">Defendido</SelectItem>
                            <SelectItem value="graduado">Graduado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Total procesos</p>
                        <GraduationCap class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Graduados</p>
                        <Award class="h-5 w-5 text-emerald-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.graduados }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">% Graduación</p>
                        <Percent class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.porcentaje_graduados }}%</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <ApexChartCard
                    chart-id="titulacion-por-estado"
                    title="Procesos por estado"
                    type="pie"
                    :series="charts.porEstado.series"
                    :options="pieOptions(charts.porEstado.labels)"
                    :empty="!hasData(charts.porEstado)"
                />
                <ApexChartCard
                    chart-id="titulacion-por-tutor"
                    title="Procesos por tutor"
                    subtitle="Top 10 tutores con más estudiantes a cargo"
                    type="bar"
                    :series="[{ name: 'Estudiantes', data: charts.porTutor.series }]"
                    :options="barOptions(charts.porTutor.labels)"
                    :empty="!hasData(charts.porTutor)"
                />
                <ApexChartCard
                    chart-id="titulacion-por-anio"
                    title="Procesos iniciados por año"
                    type="bar"
                    class="xl:col-span-2"
                    :series="[{ name: 'Procesos', data: charts.porAnio.series }]"
                    :options="barOptions(charts.porAnio.labels, ['#0ea5e9'])"
                    :empty="!hasData(charts.porAnio)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
