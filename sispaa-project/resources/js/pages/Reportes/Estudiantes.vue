<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Users, UserCheck, AlertTriangle, ShieldCheck } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ApexChartCard from '@/components/charts/ApexChartCard.vue';
import type { ApexOptions } from 'apexcharts';

interface Catalogo { id: number; nombre: string }
interface ChartData { labels: string[]; series: number[]; colors?: (string | null)[] }

const props = defineProps<{
    kpis: {
        total_matriculados: number;
        total_faltas: number;
        faltas_justificadas: number;
        faltas_sin_justificar: number;
        porcentaje_justificadas: number;
    };
    charts: {
        matriculadosPorCarrera: ChartData;
        matriculadosPorEstado: ChartData;
        faltasPorMateria: ChartData;
        faltasJustificadas: ChartData;
        justificacionesPorEstado: ChartData;
    };
    periodos: Catalogo[];
    carreras: Catalogo[];
    filters: { periodo_id?: string; carrera_id?: string };
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Reportes', href: route('reportes.index') },
    { title: 'Estudiantes', href: route('reportes.estudiantes') },
];

const periodoId = ref(props.filters.periodo_id || 'all');
const carreraId = ref(props.filters.carrera_id || 'all');

const aplicar = () => {
    router.get(route('reportes.estudiantes'), {
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

// Variante para barras "por carrera": cada barra toma el color de etiqueta
// asignado a su carrera (plotOptions.bar.distributed colorea cada punto
// individualmente dentro de una sola serie).
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

const pieOptions = (labels: string[], colors?: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: colors ?? ['#6366f1', '#f59e0b', '#10b981', '#ef4444', '#0ea5e9'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Estudiantes" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reportes — Estudiantes</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Matrículas, faltas y justificaciones del período seleccionado.</p>
            </div>

            <!-- Filtros -->
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

            <!-- KPIs -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Matriculados</p>
                        <Users class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_matriculados }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Total faltas</p>
                        <AlertTriangle class="h-5 w-5 text-amber-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_faltas }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Justificadas</p>
                        <ShieldCheck class="h-5 w-5 text-emerald-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.faltas_justificadas }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">% Justificadas</p>
                        <UserCheck class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.porcentaje_justificadas }}%</p>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid gap-6 xl:grid-cols-2">
                <ApexChartCard
                    chart-id="matriculados-por-carrera"
                    title="Matriculados por carrera"
                    type="bar"
                    :series="[{ name: 'Matriculados', data: charts.matriculadosPorCarrera.series }]"
                    :options="barOptionsPorCarrera(charts.matriculadosPorCarrera.labels, charts.matriculadosPorCarrera.colors)"
                    :empty="!hasData(charts.matriculadosPorCarrera)"
                />
                <ApexChartCard
                    chart-id="matriculados-por-estado"
                    title="Matriculados por estado"
                    type="pie"
                    :series="charts.matriculadosPorEstado.series"
                    :options="pieOptions(charts.matriculadosPorEstado.labels)"
                    :empty="!hasData(charts.matriculadosPorEstado)"
                />
                <ApexChartCard
                    chart-id="faltas-por-materia"
                    title="Faltas por materia"
                    subtitle="Top 10 materias con más faltas registradas"
                    type="bar"
                    :series="[{ name: 'Faltas', data: charts.faltasPorMateria.series }]"
                    :options="barOptions(charts.faltasPorMateria.labels, ['#f59e0b'])"
                    :empty="!hasData(charts.faltasPorMateria)"
                />
                <ApexChartCard
                    chart-id="faltas-justificadas"
                    title="Faltas justificadas vs. sin justificar"
                    type="pie"
                    :series="charts.faltasJustificadas.series"
                    :options="pieOptions(charts.faltasJustificadas.labels, ['#10b981', '#ef4444'])"
                    :empty="!hasData(charts.faltasJustificadas)"
                />
                <ApexChartCard
                    chart-id="justificaciones-por-estado"
                    title="Solicitudes de justificación por estado"
                    type="pie"
                    :series="charts.justificacionesPorEstado.series"
                    :options="pieOptions(charts.justificacionesPorEstado.labels)"
                    :empty="!hasData(charts.justificacionesPorEstado)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
