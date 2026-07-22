<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Users, UserCheck, AlertTriangle, ShieldCheck } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
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
    xaxis: { categories: labels, labels: { style: { colors: '#353535' } } },
    yaxis: { labels: { style: { colors: '#353535' } } },
    colors: colors ?? ['#3c6e71'],
    grid: { borderColor: '#d9d9d9' },
});

// Variante para barras "por carrera": cada barra toma el color de etiqueta
// asignado a su carrera (plotOptions.bar.distributed colorea cada punto
// individualmente dentro de una sola serie).
const barOptionsPorCarrera = (labels: string[], colors?: (string | null)[]): ApexOptions => ({
    chart: { type: 'bar' },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', distributed: true } },
    dataLabels: { enabled: true },
    legend: { show: false },
    xaxis: { categories: labels, labels: { style: { colors: '#353535' } } },
    yaxis: { labels: { style: { colors: '#353535' } } },
    colors: colors?.length ? colors.map((c) => c ?? '#3c6e71') : ['#3c6e71'],
    grid: { borderColor: '#d9d9d9' },
});

const pieOptions = (labels: string[], colors?: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: colors ?? ['#3c6e71', '#E4BC57', '#72c184', '#ef4444', '#536493'],
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Estudiantes" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                    <Users class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Reportes — Estudiantes</h1>
                    <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Matrículas, faltas y justificaciones del período seleccionado.</p>
                </div>
            </div>

            <!-- Filtros -->
            <div class="flex flex-wrap items-end gap-3 rounded-2xl border p-4 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Período</label>
                    <Select v-model="periodoId" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[200px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Carrera</label>
                    <Select v-model="carreraId" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[200px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- KPIs -->
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Matriculados</p>
                        <Users class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_matriculados }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Total faltas</p>
                        <AlertTriangle class="h-5 w-5 text-[color:color-mix(in_srgb,#E4BC57_60%,var(--sispaa-text))]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_faltas }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Justificadas</p>
                        <ShieldCheck class="h-5 w-5 text-[var(--sispaa-secondary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.faltas_justificadas }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">% Justificadas</p>
                        <UserCheck class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.porcentaje_justificadas }}%</p>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="grid gap-4 sm:gap-6 xl:grid-cols-2">
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
                    :options="barOptions(charts.faltasPorMateria.labels, ['#E4BC57'])"
                    :empty="!hasData(charts.faltasPorMateria)"
                />
                <ApexChartCard
                    chart-id="faltas-justificadas"
                    title="Faltas justificadas vs. sin justificar"
                    type="pie"
                    :series="charts.faltasJustificadas.series"
                    :options="pieOptions(charts.faltasJustificadas.labels, ['#72c184', '#ef4444'])"
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
