<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FlaskConical, Microscope, Beaker } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ApexChartCard from '@/components/charts/ApexChartCard.vue';
import type { ApexOptions } from 'apexcharts';

interface Catalogo { id: number; nombre: string }
interface ChartData { labels: string[]; series: number[]; colors?: (string | null)[] }

const props = defineProps<{
    kpis: { total_practicas: number; total_equipos: number; total_reactivos: number };
    charts: {
        practicasPorCarrera: ChartData;
        practicasPorLaboratorio: ChartData;
        equiposPorEstado: ChartData;
        reactivosPorEstado: ChartData;
    };
    periodos: Catalogo[];
    carreras: Catalogo[];
    filters: { periodo_id?: string; carrera_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Reportes', href: route('reportes.index') },
    { title: 'Laboratorio', href: route('reportes.laboratorio') },
];

const periodoId = ref(props.filters.periodo_id || 'all');
const carreraId = ref(props.filters.carrera_id || 'all');

const aplicar = () => {
    router.get(route('reportes.laboratorio'), {
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

const barOptionsPorCarrera = (labels: string[], colors?: (string | null)[]): ApexOptions => ({
    chart: { type: 'bar' },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', distributed: true } },
    dataLabels: { enabled: true },
    legend: { show: false },
    xaxis: { categories: labels, labels: { style: { colors: '#64748b' } } },
    yaxis: { labels: { style: { colors: '#64748b' } } },
    colors: colors?.length ? colors.map((c) => c ?? '#6366f1') : ['#6366f1'],
    grid: { borderColor: '#e2e8f0' },
});

const equipoColores: Record<string, string> = { operativo: '#10b981', mantenimiento: '#f59e0b', dañado: '#ef4444' };
const reactivoColores: Record<string, string> = { disponible: '#10b981', agotado: '#f59e0b', vencido: '#ef4444' };
const pieOptions = (labels: string[], palette: Record<string, string>): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: labels.map((l) => palette[l] ?? '#6366f1'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reportes — Laboratorio</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Uso de laboratorios por carrera y estado del inventario.</p>
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
                        <p class="text-xs font-semibold text-slate-500 uppercase">Prácticas</p>
                        <FlaskConical class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_practicas }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Equipos</p>
                        <Microscope class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_equipos }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Reactivos</p>
                        <Beaker class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_reactivos }}</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-2">
                <ApexChartCard
                    chart-id="practicas-por-carrera"
                    title="Prácticas por carrera"
                    type="bar"
                    :series="[{ name: 'Prácticas', data: charts.practicasPorCarrera.series }]"
                    :options="barOptionsPorCarrera(charts.practicasPorCarrera.labels, charts.practicasPorCarrera.colors)"
                    :empty="!hasData(charts.practicasPorCarrera)"
                />
                <ApexChartCard
                    chart-id="practicas-por-laboratorio"
                    title="Prácticas por laboratorio"
                    subtitle="Top 10 laboratorios más usados"
                    type="bar"
                    :series="[{ name: 'Prácticas', data: charts.practicasPorLaboratorio.series }]"
                    :options="barOptions(charts.practicasPorLaboratorio.labels, ['#0ea5e9'])"
                    :empty="!hasData(charts.practicasPorLaboratorio)"
                />
                <ApexChartCard
                    chart-id="equipos-por-estado"
                    title="Equipos por estado"
                    type="pie"
                    :series="charts.equiposPorEstado.series"
                    :options="pieOptions(charts.equiposPorEstado.labels, equipoColores)"
                    :empty="!hasData(charts.equiposPorEstado)"
                />
                <ApexChartCard
                    chart-id="reactivos-por-estado"
                    title="Reactivos por estado"
                    type="pie"
                    :series="charts.reactivosPorEstado.series"
                    :options="pieOptions(charts.reactivosPorEstado.labels, reactivoColores)"
                    :empty="!hasData(charts.reactivosPorEstado)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
