<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookOpen, Feather, GraduationCap, Handshake, Layers, Search, Users } from 'lucide-vue-next';
import { computed } from 'vue';

// Paleta SISPAA (resources/css/app.css)
const SISPAA = {
    primary: 'var(--sispaa-primary)',
    secondary: 'var(--sispaa-secondary)',
    accent: 'var(--sispaa-accent)',
    surface: 'var(--sispaa-surface)',
    background: 'var(--sispaa-background)',
    text: 'var(--sispaa-text)',
};

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sistema Integral de Seguimiento de Procesos Académicos y Administrativos',
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
    vinculacion: {
        total_actividades: number;
        ejecutadas: number;
        pendientes: number;
        empresas_beneficiadas: number;
    };
    titulacion: {
        total: number;
        en_proceso: number;
        defendido: number;
        graduado: number;
    };
    docencia: {
        informes_cumplidos: number;
        informes_pendientes: number;
        informes_incumplidos: number;
    };
    estudiantes: {
        matriculados: number;
        activos: number;
        retirados: number;
        egresados: number;
    };
}

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
    details: IndicatorDetail[];
}

const props = defineProps<{
    stats?: Stats;
}>();

// --- LÓGICA MÓDULO ADMIN ---
const isSystemAdmin = computed(() => !!props.stats);

const porcentaje = (parte: number, total: number) => (total > 0 ? Math.round((parte / total) * 100) : 0);

// Indicadores por módulo (tarjetas con datos reales del backend)
const indicators = computed<Indicator[]>(() => {
    const s = props.stats;
    if (!s) return [];

    return [
        {
            title: 'Docencia',
            mainValue: `${s.cumplimiento_docente}%`,
            mainLabel: 'Cumplimiento',
            icon: Feather,
            color: 'bg-blue-500',
            details: [
                { label: 'Informes Cumplidos', value: s.docencia.informes_cumplidos, color: 'text-green-600 dark:text-green-400' },
                { label: 'Informes Pendientes', value: s.docencia.informes_pendientes, color: 'text-yellow-600 dark:text-yellow-400' },
                { label: 'Informes Incumplidos', value: s.docencia.informes_incumplidos, color: 'text-red-600 dark:text-red-400' },
            ],
        },
        {
            title: 'Investigación',
            mainValue: s.investigacion.total_proyectos,
            mainLabel: 'Proyectos Registrados',
            icon: Search,
            color: 'bg-purple-500',
            details: [
                { label: 'En Curso', value: s.investigacion.en_curso, color: 'text-yellow-600 dark:text-yellow-400' },
                { label: 'Finalizados', value: s.investigacion.finalizados, color: 'text-green-600 dark:text-green-400' },
                { label: 'Hitos Completados', value: `${porcentaje(s.investigacion.hitos_completados, s.investigacion.total_hitos)}%`, color: 'text-blue-600 dark:text-blue-400' },
            ],
        },
        {
            title: 'Estudiantes',
            mainValue: s.estudiantes.matriculados,
            mainLabel: 'Matriculados',
            icon: BookOpen,
            color: 'bg-green-500',
            details: [
                { label: 'Activos', value: s.estudiantes.activos, color: 'text-green-600 dark:text-green-400' },
                { label: 'Retirados', value: s.estudiantes.retirados, color: 'text-red-600 dark:text-red-400' },
                { label: 'Egresados', value: s.estudiantes.egresados, color: 'text-blue-600 dark:text-blue-400' },
            ],
        },
        {
            title: 'Vinculación',
            mainValue: s.vinculacion.total_actividades,
            mainLabel: 'Actividades',
            icon: Handshake,
            color: 'bg-red-500',
            details: [
                { label: 'Ejecutadas', value: s.vinculacion.ejecutadas, color: 'text-green-600 dark:text-green-400' },
                { label: 'Pendientes', value: s.vinculacion.pendientes, color: 'text-yellow-600 dark:text-yellow-400' },
                { label: 'Beneficiarios', value: s.vinculacion.empresas_beneficiadas },
            ],
        },
        {
            title: 'Titulación',
            mainValue: s.titulacion.total,
            mainLabel: 'Procesos Registrados',
            icon: GraduationCap,
            color: 'bg-indigo-500',
            details: [
                { label: 'Temas en Desarrollo', value: s.titulacion.en_proceso },
                { label: 'Graduados', value: s.titulacion.graduado, color: 'text-green-600 dark:text-green-400' },
                { label: 'Pendientes de Revisión', value: s.titulacion.defendido, color: 'text-yellow-600 dark:text-yellow-400' },
            ],
        },
    ];
});
</script>

<template>
    <Head title="Dashboard Principal" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-5" :style="{ backgroundColor: SISPAA.background }">
            <!-- 1. VISTA DE ADMINISTRADOR -->
            <template v-if="isSystemAdmin && stats">
                <!-- Header -->
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight" :style="{ color: SISPAA.text }">Dashboard de Administración</h1>
                    <p class="mt-1 text-sm opacity-70" :style="{ color: SISPAA.text }">
                        Métricas globales y monitoreo en tiempo real del cumplimiento académico y el inventario institucional.
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl p-6 shadow-sm" :style="{ backgroundColor: SISPAA.surface }">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold opacity-70" :style="{ color: SISPAA.text }">Estudiantes</span>
                            <Users class="h-5 w-5" :style="{ color: SISPAA.primary }" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold" :style="{ color: SISPAA.text }">{{ stats.total_estudiantes }}</span>
                        </div>
                        <p class="mt-2 text-xs opacity-60" :style="{ color: SISPAA.text }">Total matriculados activos</p>
                    </div>

                    <div class="rounded-2xl p-6 shadow-sm" :style="{ backgroundColor: SISPAA.surface }">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold opacity-70" :style="{ color: SISPAA.text }">Docentes</span>
                            <Users class="h-5 w-5" :style="{ color: SISPAA.secondary }" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold" :style="{ color: SISPAA.text }">{{ stats.total_docentes }}</span>
                        </div>
                        <p class="mt-2 text-xs opacity-60" :style="{ color: SISPAA.text }">Docentes activos</p>
                    </div>

                    <div class="rounded-2xl p-6 shadow-sm" :style="{ backgroundColor: SISPAA.surface }">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold opacity-70" :style="{ color: SISPAA.text }">Carreras</span>
                            <Layers class="h-5 w-5" :style="{ color: SISPAA.accent }" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold" :style="{ color: SISPAA.text }">{{ stats.total_carreras }}</span>
                        </div>
                        <p class="mt-2 text-xs opacity-60" :style="{ color: SISPAA.text }">Carreras activas en el sistema</p>
                    </div>

                    <div class="rounded-2xl p-6 shadow-sm" :style="{ backgroundColor: SISPAA.surface }">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold opacity-70" :style="{ color: SISPAA.text }">Asignaturas</span>
                            <BookOpen class="h-5 w-5" :style="{ color: SISPAA.primary }" />
                        </div>
                        <div class="mt-4">
                            <span class="text-3xl font-extrabold" :style="{ color: SISPAA.text }">{{ stats.total_materias }}</span>
                        </div>
                        <p class="mt-2 text-xs opacity-60" :style="{ color: SISPAA.text }">Malla curricular global</p>
                    </div>
                </div>

                <!-- Indicadores agregados por módulo -->
                <div class="grid gap-4" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr))">
                    <div
                        v-for="indicator in indicators"
                        :key="indicator.title"
                        class="overflow-hidden rounded-lg transition-shadow hover:shadow-lg"
                        :style="{ backgroundColor: SISPAA.surface }"
                    >
                        <div class="flex items-start justify-between p-6 pb-4">
                            <div class="flex-1">
                                <p class="text-sm font-medium opacity-70" :style="{ color: SISPAA.text }">{{ indicator.title }}</p>
                                <p class="mt-2 text-3xl font-bold" :style="{ color: SISPAA.text }">{{ indicator.mainValue }}</p>
                                <p class="mt-2 text-xs opacity-60" :style="{ color: SISPAA.text }">{{ indicator.mainLabel }}</p>
                            </div>
                            <div :class="[indicator.color, 'rounded-lg p-3 text-white']">
                                <component :is="indicator.icon" class="h-6 w-6" />
                            </div>
                        </div>

                        <div
                            v-if="indicator.details.length"
                            class="px-6 py-4"
                        >
                            <div class="space-y-2">
                                <div v-for="(detail, idx) in indicator.details" :key="idx" class="flex items-center justify-between text-sm">
                                    <span class="opacity-70" :style="{ color: SISPAA.text }">{{ detail.label }}</span>
                                    <span :class="[detail.color, 'font-semibold']" :style="!detail.color ? { color: SISPAA.text } : undefined">{{
                                        detail.value
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
