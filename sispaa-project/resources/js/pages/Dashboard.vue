<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Book, Feather, FlaskConical, GraduationCap, Handshake, Search, AlertTriangle, Users, BookOpen, Layers, CheckCircle2, TrendingDown } from 'lucide-vue-next';
import { computed } from 'vue';
import type { ApexOptions } from 'apexcharts';
import ApexChart from 'vue3-apexcharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard Principal',
        href: '/dashboard',
    },
];

interface Stats {
    cumplimiento_docente: number;
    total_informes: number;
    informes_entregados: number;
    desercion_estudiantil: number;
    total_estudiantes: number;
    total_docentes: number;
    total_carreras: number;
    total_materias: number;
    inventario: {
        total_equipos: number;
        total_reactivos: number;
        reactivos_bajo_stock: number;
    };
}

interface CarreraCumplimiento {
    carrera: string;
    codigo: string;
    porcentaje: number;
}

const props = defineProps<{
    stats?: Stats;
    cumplimientoPorCarrera?: CarreraCumplimiento[];
}>();

// --- LÓGICA MÓDULO ADMIN ---
const isSystemAdmin = computed(() => !!props.stats);

// Apex Chart options for Admin Cumplimiento Docente
const adminChartSeries = computed(() => {
    if (!props.stats) return [0, 100];
    const cumplidos = props.stats.cumplimiento_docente;
    const pendientes = 100 - cumplidos;
    return [cumplidos, pendientes];
});

const adminChartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'donut',
        background: 'transparent',
    },
    labels: ['Cumplido', 'Pendiente'],
    colors: ['#10b981', '#f59e0b'],
    stroke: {
        colors: ['transparent'],
    },
    legend: {
        show: false
    },
    dataLabels: {
        enabled: false
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    value: {
                        formatter: (val) => `${val}%`,
                        fontSize: '24px',
                        fontWeight: 'bold',
                        color: 'var(--sispaa-text)'
                    },
                    total: {
                        show: true,
                        label: 'Entregas',
                        formatter: () => `${props.stats?.cumplimiento_docente}%`
                    }
                }
            }
        }
    }
}));

// --- LÓGICA MÓDULO GENERAL / RESPALDO ---
interface IndicatorDetail {
    label: string;
    value: string | number;
    color?: string;
}

interface Indicator {
    title: string;
    mainValue: string | number;
    mainLabel: string;
    icon: any;
    color: string;
    details?: IndicatorDetail[];
}

const indicators: Indicator[] = [
    {
        title: 'Docencia',
        mainValue: '85%',
        mainLabel: 'Cumplimiento',
        icon: Feather,
        color: 'bg-blue-500',
        details: [
            { label: 'Informes Cumplidos', value: 42, color: 'text-green-600 font-bold' },
            { label: 'Informes Pendientes', value: 6, color: 'text-yellow-600 font-bold' },
            { label: 'Informes Incumplidos', value: 2, color: 'text-red-600 font-bold' },
        ],
    },
    {
        title: 'Investigación',
        mainValue: '78%',
        mainLabel: 'Informes Subidos',
        icon: Search,
        color: 'bg-purple-500',
        details: [
            { label: 'Proyectos Activos', value: 12, color: 'text-slate-700 font-semibold' },
            { label: 'Hitos Avanzados', value: 8, color: 'text-slate-700 font-semibold' },
            { label: 'En Revisión', value: 4, color: 'text-slate-700 font-semibold' },
        ],
    },
    {
        title: 'Estudiantes',
        mainValue: 342,
        mainLabel: 'Matriculados',
        icon: Book,
        color: 'bg-green-500',
        details: [
            { label: 'Activos', value: 318, color: 'text-slate-700 font-semibold' },
            { label: 'Retirados', value: 15, color: 'text-slate-700 font-semibold' },
            { label: 'Faltas Registradas', value: 127, color: 'text-slate-700 font-semibold' },
        ],
    },
    {
        title: 'Prácticas de Laboratorio',
        mainValue: 156,
        mainLabel: 'Realizadas',
        icon: FlaskConical,
        color: 'bg-orange-500',
        details: [
            { label: 'Agropecuaria', value: 52, color: 'text-slate-700 font-semibold' },
            { label: 'Agronegocios', value: 61, color: 'text-slate-700 font-semibold' },
            { label: 'Agroindustria', value: 43, color: 'text-slate-700 font-semibold' },
        ],
    },
];
</script>

<template>
    <Head title="Dashboard Principal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- 1. VISTA DE ADMINISTRADOR -->
            <template v-if="isSystemAdmin && stats">
                <!-- Header -->
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Dashboard de Administración
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        Métricas globales y monitoreo en tiempo real del cumplimiento académico y el inventario institucional.
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Estudiantes</span>
                            <Users class="h-5 w-5 text-indigo-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_estudiantes }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Total matriculados activos</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Docentes</span>
                            <Users class="h-5 w-5 text-blue-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_docentes }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Docentes activos</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Carreras</span>
                            <Layers class="h-5 w-5 text-emerald-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_carreras }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Carreras activas en el sistema</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Asignaturas</span>
                            <BookOpen class="h-5 w-5 text-purple-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_materias }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Malla curricular global</p>
                    </div>
                </div>

                <!-- Secciones de Gráficas de Métricas Globales -->
                <div class="grid gap-6 lg:grid-cols-3">
                    <!-- Cumplimiento Docente -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 pb-3 dark:border-slate-850">
                            Cumplimiento Docente
                        </h3>
                        <div class="flex flex-col items-center justify-center py-6">
                            <ApexChart
                                type="donut"
                                width="100%"
                                height="220"
                                :options="adminChartOptions"
                                :series="adminChartSeries"
                            />
                            <div class="mt-4 text-center">
                                <p class="text-xs text-slate-500 font-medium">
                                    {{ stats.informes_entregados }} de {{ stats.total_informes }} informes subidos
                                </p>
                            </div>
                        </div>

                        <div class="mt-2 space-y-2 border-t border-slate-50 pt-4 dark:border-slate-850">
                            <div v-for="c in cumplimientoPorCarrera" :key="c.carrera" class="flex items-center justify-between text-xs">
                                <span class="font-medium text-slate-600 dark:text-slate-400">{{ c.carrera }} ({{ c.codigo }})</span>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200">{{ c.porcentaje }}%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Deserción Estudiantil -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 pb-3 dark:border-slate-850">
                            Deserción Estudiantil
                        </h3>
                        <div class="flex flex-col items-center justify-center py-10 text-center">
                            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400">
                                <TrendingDown class="h-8 w-8" />
                            </div>
                            <h4 class="text-4xl font-black text-rose-600 dark:text-rose-400 mt-4">
                                {{ stats.desercion_estudiantil }}%
                            </h4>
                            <p class="text-xs text-slate-400 mt-1">Tasa de Deserción del Periodo</p>
                            
                            <div class="mt-6 w-full p-4 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800 text-xs text-left space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Total Matriculados:</span>
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ stats.total_estudiantes }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-500">Estudiantes Retirados:</span>
                                    <span class="font-bold text-rose-600 dark:text-rose-400">{{ stats.total_informes > 0 ? Math.round(stats.total_estudiantes * (stats.desercion_estudiantil/100)) : 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventario Laboratorio -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 pb-3 dark:border-slate-850">
                            Inventario de Laboratorios
                        </h3>
                        
                        <div class="mt-6 space-y-4">
                            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900">
                                <div class="flex items-center gap-3">
                                    <FlaskConical class="h-5 w-5 text-orange-500" />
                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-500">Total Reactivos</h4>
                                        <p class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ stats.inventario.total_reactivos }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900">
                                <div class="flex items-center gap-3">
                                    <Layers class="h-5 w-5 text-blue-500" />
                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-500">Total Equipos</h4>
                                        <p class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ stats.inventario.total_equipos }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Alerta de Bajo Stock -->
                            <div v-if="stats.inventario.reactivos_bajo_stock > 0" class="flex items-start gap-3 p-4 rounded-xl border border-rose-100 bg-rose-50/30 dark:border-rose-950/20 dark:bg-rose-950/10">
                                <AlertTriangle class="h-5 w-5 text-rose-500 shrink-0 mt-0.5" />
                                <div>
                                    <h4 class="text-xs font-bold text-rose-800 dark:text-rose-400">Atención: Stock Bajo</h4>
                                    <p class="text-xxs text-rose-700/80 dark:text-rose-400/80 mt-0.5">
                                        Hay {{ stats.inventario.reactivos_bajo_stock }} reactivos por debajo del stock de seguridad en laboratorio.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- 2. VISTA DE RESPALDO (O ADMINISTRADOR GENERAL ORIGINAL) -->
            <template v-else>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="indicator in indicators"
                        :key="indicator.title"
                        class="border-slate-200/80 overflow-hidden rounded-lg border bg-white dark:border-slate-800 dark:bg-slate-950 transition-shadow"
                    >
                        <div class="flex items-start justify-between p-6 pb-4">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-500">{{ indicator.title }}</p>
                                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white">{{ indicator.mainValue }}</p>
                                <p class="text-xs text-slate-400 mt-2">{{ indicator.mainLabel }}</p>
                            </div>
                            <div :class="[indicator.color, 'flex items-center justify-center rounded-lg p-3 text-white']">
                                <component :is="indicator.icon" class="h-6 w-6" />
                            </div>
                        </div>

                        <div v-if="indicator.details" class="bg-slate-50 dark:bg-slate-900 border-t border-slate-100 dark:border-slate-800/80 px-6 py-4">
                            <div class="space-y-2">
                                <div v-for="(detail, idx) in indicator.details" :key="idx" class="flex items-center justify-between text-xs">
                                    <span class="text-slate-500">{{ detail.label }}</span>
                                    <span :class="[detail.color || 'font-semibold text-slate-700 dark:text-slate-350']">{{ detail.value }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
