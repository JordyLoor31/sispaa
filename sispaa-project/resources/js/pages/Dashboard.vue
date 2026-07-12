<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FlaskConical, GraduationCap, Handshake, Search, AlertTriangle, Users, BookOpen, Layers, TrendingDown } from 'lucide-vue-next';
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
    investigacion: {
        total_proyectos: number;
        en_curso: number;
        finalizados: number;
        hitos_completados: number;
        total_hitos: number;
    };
    laboratorio: {
        total_practicas: number;
        estudiantes_atendidos: number;
    };
    vinculacion: {
        total_actividades: number;
        ejecutadas: number;
        pendientes: number;
    };
    titulacion: {
        total: number;
        en_proceso: number;
        defendido: number;
        graduado: number;
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

const porcentaje = (parte: number, total: number) => (total > 0 ? Math.round((parte / total) * 100) : 0);
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

                <!-- Indicadores agregados por módulo -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Investigación -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Investigación</span>
                            <Search class="h-5 w-5 text-purple-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.investigacion.total_proyectos }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Proyectos registrados</p>
                        <div class="mt-4 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3 text-xs">
                            <div class="flex justify-between"><span class="text-slate-500">En curso</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ stats.investigacion.en_curso }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Finalizados</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ stats.investigacion.finalizados }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Hitos completados</span><span class="font-bold text-emerald-600 dark:text-emerald-400">{{ porcentaje(stats.investigacion.hitos_completados, stats.investigacion.total_hitos) }}%</span></div>
                        </div>
                    </div>

                    <!-- Prácticas de Laboratorio -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Prácticas de Laboratorio</span>
                            <FlaskConical class="h-5 w-5 text-orange-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.laboratorio.total_practicas }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Prácticas registradas</p>
                        <div class="mt-4 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3 text-xs">
                            <div class="flex justify-between"><span class="text-slate-500">Estudiantes atendidos</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ stats.laboratorio.estudiantes_atendidos }}</span></div>
                        </div>
                    </div>

                    <!-- Vinculación -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Vinculación</span>
                            <Handshake class="h-5 w-5 text-emerald-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.vinculacion.total_actividades }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Actividades registradas</p>
                        <div class="mt-4 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3 text-xs">
                            <div class="flex justify-between"><span class="text-slate-500">Ejecutadas</span><span class="font-bold text-emerald-600 dark:text-emerald-400">{{ stats.vinculacion.ejecutadas }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Pendientes</span><span class="font-bold text-amber-600 dark:text-amber-400">{{ stats.vinculacion.pendientes }}</span></div>
                        </div>
                    </div>

                    <!-- Titulación -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-slate-500">Titulación</span>
                            <GraduationCap class="h-5 w-5 text-indigo-500" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.titulacion.total }}</span>
                        </div>
                        <p class="mt-2 text-xs text-slate-400">Procesos registrados</p>
                        <div class="mt-4 space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-3 text-xs">
                            <div class="flex justify-between"><span class="text-slate-500">En proceso</span><span class="font-bold text-amber-600 dark:text-amber-400">{{ stats.titulacion.en_proceso }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Defendidos</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ stats.titulacion.defendido }}</span></div>
                            <div class="flex justify-between"><span class="text-slate-500">Graduados</span><span class="font-bold text-emerald-600 dark:text-emerald-400">{{ stats.titulacion.graduado }}</span></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
