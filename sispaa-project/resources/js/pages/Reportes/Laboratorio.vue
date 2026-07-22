<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FlaskConical, Microscope, Beaker } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
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

const equipoColores: Record<string, string> = { operativo: '#72c184', mantenimiento: '#E4BC57', dañado: '#ef4444' };
const reactivoColores: Record<string, string> = { disponible: '#72c184', agotado: '#E4BC57', vencido: '#ef4444' };
const pieOptions = (labels: string[], palette: Record<string, string>): ApexOptions => ({
    chart: { type: 'pie' },
    labels,
    colors: labels.map((l) => palette[l] ?? '#3c6e71'),
    legend: { position: 'bottom' },
    dataLabels: { enabled: true, formatter: (val: number) => Math.round(val) + '%' },
});

const hasData = (c: ChartData) => c.series.length > 0 && c.series.some((v) => v > 0);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reportes — Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                    <FlaskConical class="h-5 w-5" />
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Reportes — Laboratorio</h1>
                    <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Uso de laboratorios por carrera y estado del inventario.</p>
                </div>
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

            <div class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Prácticas</p>
                        <FlaskConical class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_practicas }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Equipos</p>
                        <Microscope class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_equipos }}</p>
                </div>
                <div class="rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Reactivos</p>
                        <Beaker class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_reactivos }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 xl:grid-cols-2">
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
