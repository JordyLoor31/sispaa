<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Feather, Search, Book, FlaskConical, Handshake, GraduationCap } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Sistema Integral de Seguimiento de Procesos Académicos y Administrativos',
        href: '/dashboard',
    },
];

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
            { label: 'Informes Cumplidos', value: 42, color: 'text-green-600' },
            { label: 'Informes Pendientes', value: 6, color: 'text-yellow-600' },
            { label: 'Informes Incumplidos', value: 2, color: 'text-red-600' },
        ],
    },
    {
        title: 'Investigación',
        mainValue: '78%',
        mainLabel: 'Informes Subidos',
        icon: Search,
        color: 'bg-purple-500',
        details: [
            { label: 'Proyectos Activos', value: 12 },
            { label: 'Hitos Avanzados', value: 8 },
            { label: 'En Revisión', value: 4 },
        ],
    },
    {
        title: 'Estudiantes',
        mainValue: 342,
        mainLabel: 'Matriculados',
        icon: Book,
        color: 'bg-green-500',
        details: [
            { label: 'Activos', value: 318 },
            { label: 'Retirados', value: 15 },
            { label: 'Faltas Registradas', value: 127 },
        ],
    },
    {
        title: 'Prácticas de Laboratorio',
        mainValue: 156,
        mainLabel: 'Realizadas',
        icon: FlaskConical,
        color: 'bg-orange-500',
        details: [
            { label: 'Agropecuaria', value: 52 },
            { label: 'Agronegocios', value: 61 },
            { label: 'Agroindustria', value: 43 },
        ],
    },
    {
        title: 'Vinculación',
        mainValue: 18,
        mainLabel: 'Actividades',
        icon: Handshake,
        color: 'bg-red-500',
        details: [
            { label: 'Ejecutadas', value: 12 },
            { label: 'Pendientes', value: 6 },
            { label: 'Empresas Beneficiadas', value: 8 },
        ],
    },
    {
        title: 'Titulación',
        mainValue: 45,
        mainLabel: 'En Progreso',
        icon: GraduationCap,
        color: 'bg-indigo-500',
        details: [
            { label: 'Temas en Desarrollo', value: 45 },
            { label: 'Graduados', value: 32 },
            { label: 'Pendientes de Revisión', value: 8 },
        ],
    },
];

defineProps<{
    name?: string;
}>();
</script>

<template>
    <Head title="Sistema Integral de Seguimiento de Procesos Académicos y Administrativos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div v-for="indicator in indicators" :key="indicator.title" class="rounded-lg border border-sidebar-border/70 dark:border-sidebar-border bg-white dark:bg-slate-900/35 overflow-hidden hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between p-6 pb-4">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-slate-600 dark:text-slate-400">{{ indicator.title }}</p>
                            <p class="text-3xl font-bold text-slate-900 dark:text-white mt-2">{{ indicator.mainValue }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">{{ indicator.mainLabel }}</p>
                        </div>
                        <div :class="[indicator.color, 'rounded-lg p-3 text-white']">
                            <component :is="indicator.icon" class="w-6 h-6" />
                        </div>
                    </div>
                    
                    <div v-if="indicator.details" class="border-t border-sidebar-border/50 px-6 py-4 bg-slate-50 dark:bg-slate-800/35">
                        <div class="space-y-2">
                            <div v-for="(detail, idx) in indicator.details" :key="idx" class="flex justify-between items-center text-sm">
                                <span class="text-slate-600 dark:text-slate-400">{{ detail.label }}</span>
                                <span :class="[detail.color || 'text-slate-700 dark:text-white', 'font-semibold']">{{ detail.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
