<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, FileCheck, UserCheck, Calendar } from 'lucide-vue-next';

interface KPIProps {
    promedio: number;
    semestre: string;
    avance_sga: number;
}

defineProps<{
    kpis: KPIProps;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Portal de Estudiantes',
        href: '/dashboard',
    },
    {
        title: 'Vista General',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Panel del Estudiante" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header Section -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        ¡Hola de nuevo!
                    </h1>
                    <p class="text-sm opacity-60 text-[var(--sispaa-text)]">
                        Aquí tienes el resumen de tu expediente y rendimiento académico en tiempo real.
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start rounded-full px-3 py-1 text-xs font-medium bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">
                    <UserCheck class="h-3.5 w-3.5" />
                    Rol: Estudiante Activo
                </div>
            </div>

            <!-- KPIs Grid -->
            <div class="grid gap-4 sm:gap-6 md:grid-cols-3">
                <!-- Promedio Card -->
                <div class="group relative overflow-hidden rounded-2xl p-5 shadow-sm transition-all hover:shadow-md sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold opacity-70 text-[var(--sispaa-text)]">Promedio General</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]">
                            <BookOpen class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.promedio }}</span>
                        <span class="text-sm opacity-50 text-[var(--sispaa-text)]">/ 10.0</span>
                    </div>
                    <p class="mt-2 text-xs opacity-50 text-[var(--sispaa-text)]">Rendimiento académico actual</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 origin-left scale-x-0 transform bg-[var(--sispaa-accent)] transition-transform duration-300 group-hover:scale-x-100"></div>
                </div>

                <!-- Semestre Card -->
                <div class="group relative overflow-hidden rounded-2xl p-5 shadow-sm transition-all hover:shadow-md sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold opacity-70 text-[var(--sispaa-text)]">Periodo / Nivel</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:color-mix(in_srgb,var(--sispaa-primary)_20%,transparent)] text-[var(--sispaa-primary)]">
                            <Calendar class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.semestre }}</span>
                    </div>
                    <p class="mt-4 text-xs opacity-50 text-[var(--sispaa-text)]">Matrícula vigente activa</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 origin-left scale-x-0 transform bg-[var(--sispaa-primary)] transition-transform duration-300 group-hover:scale-x-100"></div>
                </div>

                <!-- Expediente SGA Card -->
                <div class="group relative overflow-hidden rounded-2xl p-5 shadow-sm transition-all hover:shadow-md sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold opacity-70 text-[var(--sispaa-text)]">Expediente SGA</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">
                            <FileCheck class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.avance_sga }}%</span>
                            <span class="text-xs opacity-50 text-[var(--sispaa-text)]">de avance</span>
                        </div>
                        <Progress :model-value="kpis.avance_sga" class="mt-3 h-2" />
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 origin-left scale-x-0 transform bg-[var(--sispaa-secondary)] transition-transform duration-300 group-hover:scale-x-100"></div>
                </div>
            </div>

            <!-- Enlaces Rápidos / Accesos -->
            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <h2 class="border-b pb-4 text-lg font-bold text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    Accesos Rápidos
                </h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <Link :href="route('student.documentos')" class="flex items-center gap-3 rounded-xl border p-3 transition-colors border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">
                            <FileCheck class="h-5 w-5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-[var(--sispaa-text)]">Cargar Expediente</p>
                            <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Sube tus documentos SGA</p>
                        </div>
                    </Link>

                    <Link :href="route('student.titulacion')" class="flex items-center gap-3 rounded-xl border p-3 transition-colors border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_20%,transparent)] text-[var(--sispaa-primary)]">
                            <BookOpen class="h-5 w-5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-[var(--sispaa-text)]">Mi Titulación</p>
                            <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Monitorea tu tema y tutor</p>
                        </div>
                    </Link>

                    <Link :href="route('student.perfil')" class="flex items-center gap-3 rounded-xl border p-3 transition-colors border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]">
                            <UserCheck class="h-5 w-5" />
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-[var(--sispaa-text)]">Ver Mi Perfil</p>
                            <p class="text-xs opacity-60 text-[var(--sispaa-text)]">Consulta tus datos personales</p>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
