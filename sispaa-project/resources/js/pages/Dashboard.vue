<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Book, Feather, FlaskConical, GraduationCap, Handshake, Search } from 'lucide-vue-next';

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
            { label: 'Proyectos Activos', value: 12, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Hitos Avanzados', value: 8, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'En Revisión', value: 4, color: 'text-[var(--sispaa-text)] font-semibold' },
        ],
    },
    {
        title: 'Estudiantes',
        mainValue: 342,
        mainLabel: 'Matriculados',
        icon: Book,
        color: 'bg-green-500',
        details: [
            { label: 'Activos', value: 318, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Retirados', value: 15, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Faltas Registradas', value: 127, color: 'text-[var(--sispaa-text)] font-semibold' },
        ],
    },
    {
        title: 'Prácticas de Laboratorio',
        mainValue: 156,
        mainLabel: 'Realizadas',
        icon: FlaskConical,
        color: 'bg-orange-500',
        details: [
            { label: 'Agropecuaria', value: 52, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Agronegocios', value: 61, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Agroindustria', value: 43, color: 'text-[var(--sispaa-text)] font-semibold' },
        ],
    },
    {
        title: 'Vinculación',
        mainValue: 18,
        mainLabel: 'Actividades',
        icon: Handshake,
        color: 'bg-red-500',
        details: [
            { label: 'Ejecutadas', value: 12, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Pendientes', value: 6, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Empresas Beneficiadas', value: 8, color: 'text-[var(--sispaa-text)] font-semibold' },
        ],
    },
    {
        title: 'Titulación',
        mainValue: 45,
        mainLabel: 'En Progreso',
        icon: GraduationCap,
        color: 'bg-indigo-500',
        details: [
            { label: 'Temas en Desarrollo', value: 45, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Graduados', value: 32, color: 'text-[var(--sispaa-text)] font-semibold' },
            { label: 'Pendientes de Revisión', value: 8, color: 'text-[var(--sispaa-text)] font-semibold' },
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
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="indicator in indicators"
                    :key="indicator.title"
                    class="border-[var(--sispaa-text)]/30 overflow-hidden rounded-lg border bg-[var(--sispaa-surface)] transition-shadow"
                >
                    <div class="flex items-start justify-between p-6 pb-4">
                        <div class="flex-1">
                            <p class="text-[var(--sispaa-text)]/80 text-sm font-semibold">{{ indicator.title }}</p>
                            <p class="mt-2 text-3xl font-bold text-[var(--sispaa-text)]">{{ indicator.mainValue }}</p>
                            <p class="text-[var(--sispaa-text)]/60 mt-2 text-xs">{{ indicator.mainLabel }}</p>
                        </div>
                        <div :class="[indicator.color, 'flex items-center justify-center rounded-lg p-3 text-white']">
                            <component :is="indicator.icon" class="h-6 w-6" />
                        </div>
                    </div>

                    <div v-if="indicator.details" class="border-[var(--sispaa-text)]/30 bg-[var(--sispaa-surface)] px-6 py-4">
                        <div class="space-y-2">
                            <div v-for="(detail, idx) in indicator.details" :key="idx" class="flex items-center justify-between text-sm">
                                <span class="text-[var(--sispaa-text)]/80">{{ detail.label }}</span>
                                <span :class="[detail.color || 'font-semibold text-[var(--sispaa-text)]']">{{ detail.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
