<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { BookOpen, CheckCircle2, Percent } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ApexChartCard from '@/components/charts/ApexChartCard.vue';
import type { ApexOptions } from 'apexcharts';

interface Catalogo { id: number; nombre: string }
interface ChartData { labels: string[]; series: number[] }

const props = defineProps<{
    kpis: { total: number; aprobados: number; porcentaje_aprobados: number };
    charts: {
        porEstado: ChartData;
        porCarrera: ChartData;
        porPeriodo: ChartData;
    };
    periodos: Catalogo[];
    carreras: Catalogo[];
    filters: { periodo_id?: string; carrera_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Reportes', href: route('reportes.index') },
    { title: 'Sílabos', href: route('reportes.silabos') },
];

const periodoId = ref(props.filters.periodo_id || 'all');
const carreraId = ref(props.filters.carrera_id || 'all');

const aplicar = () => {
    router.get(route('reportes.silabos'), {
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

const estadoColores: Record<string, string> = { aprobado: '#10b981', subido: '#f59e0b', pendiente: '#94a3b8' };
const pieOptions = (labels: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: labels.map((l) => estadoColores[l] ?? '#6366f1'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Sílabos" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reportes — Sílabos</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Estado de cumplimiento de sílabos por carrera y período.</p>
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

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Total sílabos</p>
                        <BookOpen class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Aprobados</p>
                        <CheckCircle2 class="h-5 w-5 text-emerald-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.aprobados }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">% Aprobación</p>
                        <Percent class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.porcentaje_aprobados }}%</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <ApexChartCard
                    chart-id="silabos-por-estado"
                    title="Sílabos por estado"
                    type="pie"
                    :series="charts.porEstado.series"
                    :options="pieOptions(charts.porEstado.labels)"
                    :empty="!hasData(charts.porEstado)"
                />
                <ApexChartCard
                    chart-id="silabos-por-carrera"
                    title="Sílabos por carrera"
                    type="bar"
                    :series="[{ name: 'Sílabos', data: charts.porCarrera.series }]"
                    :options="barOptions(charts.porCarrera.labels)"
                    :empty="!hasData(charts.porCarrera)"
                />
                <ApexChartCard
                    chart-id="silabos-por-periodo"
                    title="Sílabos por período académico"
                    type="bar"
                    class="xl:col-span-2"
                    :series="[{ name: 'Sílabos', data: charts.porPeriodo.series }]"
                    :options="barOptions(charts.porPeriodo.labels, ['#0ea5e9'])"
                    :empty="!hasData(charts.porPeriodo)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
