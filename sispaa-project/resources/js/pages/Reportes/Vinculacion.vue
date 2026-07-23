<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Handshake, Building2, Percent } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ApexChartCard from '@/components/charts/ApexChartCard.vue';
import ReporteEstadisticoButton from '@/components/charts/ReporteEstadisticoButton.vue';

const reportCharts = [
    { id: 'vinculacion-por-estado', title: 'Actividades por estado' },
    { id: 'vinculacion-por-carrera', title: 'Actividades por carrera' },
    { id: 'empresas-por-sector', title: 'Beneficiarios por sector' },
];
import type { ApexOptions } from 'apexcharts';

interface Catalogo { id: number; nombre: string }
interface ChartData { labels: string[]; series: number[]; colors?: (string | null)[] }

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
    xaxis: { categories: labels, labels: { style: { colors: '#353535' } } },
    yaxis: { labels: { style: { colors: '#353535' } } },
    colors: colors ?? ['#3c6e71'],
    grid: { borderColor: '#d9d9d9' },
});

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

const estadoColores: Record<string, string> = { ejecutado: '#72c184', pendiente: '#E4BC57' };
const pieOptions = (labels: string[], fallback?: string[]): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: labels.map((l, i) => estadoColores[l] ?? fallback?.[i] ?? '#3c6e71'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Vinculación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                        <Handshake class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Reportes — Vinculación</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Actividades de vinculación y beneficiarios.</p>
                    </div>
                </div>
                <ReporteEstadisticoButton
                    titulo="Reporte Estadístico — Vinculación"
                    subtitulo="Actividades de vinculación y beneficiarios"
                    :kpis="[
                        { label: 'Actividades', value: kpis.total_actividades },
                        { label: 'Ejecutadas', value: kpis.ejecutadas },
                        { label: '% Ejecutadas', value: kpis.porcentaje_ejecutadas + '%' },
                        { label: 'Beneficiarios', value: kpis.total_empresas },
                    ]"
                    :charts="reportCharts"
                />
            </div>

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

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Actividades</p>
                        <Handshake class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_actividades }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Ejecutadas</p>
                        <Handshake class="h-5 w-5 text-[var(--sispaa-secondary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.ejecutadas }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">% Ejecución</p>
                        <Percent class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.porcentaje_ejecutadas }}%</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Beneficiarios</p>
                        <Building2 class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_empresas }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 xl:grid-cols-2">
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
                    :options="barOptionsPorCarrera(charts.porCarrera.labels, charts.porCarrera.colors)"
                    :empty="!hasData(charts.porCarrera)"
                />
                <ApexChartCard
                    chart-id="empresas-por-sector"
                    title="Beneficiarios por sector"
                    type="bar"
                    class="xl:col-span-2"
                    :series="[{ name: 'Beneficiarios', data: charts.empresasPorSector.series }]"
                    :options="barOptions(charts.empresasPorSector.labels, ['#0ea5e9'])"
                    :empty="!hasData(charts.empresasPorSector)"
                />
            </div>
        </div>
    </AppSidebarLayout>
</template>
