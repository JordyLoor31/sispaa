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
    xaxis: { categories: labels, labels: { style: { colors: '#353535' } } },
    yaxis: { labels: { style: { colors: '#353535' } } },
    colors: colors ?? ['#3c6e71'],
    grid: { borderColor: '#d9d9d9' },
});

const estadoColores: Record<string, string> = { graduado: '#72c184', defendido: '#536493', en_proceso: '#E4BC57' };
const pieOptions = (labels: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels: labels.map((l) => l.replace('_', ' ')),
    colors: labels.map((l) => estadoColores[l] ?? '#3c6e71'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Titulación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Reportes — Titulación</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Avance de los procesos de titulación por estado, tutor y año.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 rounded-xl p-4 bg-[var(--sispaa-surface)]">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Estado</label>
                    <Select v-model="estado" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[200px] bg-[var(--sispaa-background)]"><SelectValue /></SelectTrigger>
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
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Total procesos</p>
                        <GraduationCap class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Graduados</p>
                        <Award class="h-5 w-5 text-[var(--sispaa-secondary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.graduados }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">% Graduación</p>
                        <Percent class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.porcentaje_graduados }}%</p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 xl:grid-cols-2">
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
                    :options="barOptions(charts.porAnio.labels, ['#536493'])"
                    :empty="!hasData(charts.porAnio)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
