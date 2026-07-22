<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { BookOpen, AlertTriangle, FileCheck, CheckCircle2, ArrowRight, UserCheck, Calendar } from 'lucide-vue-next';

interface KPIProps {
    promedio: number;
    semestre: string;
    total_faltas: number;
    faltas_sin_justificar: number;
    avance_sga: number;
}

interface Falta {
    id: number;
    materia: {
        id: number;
        nombre: string;
    };
    fecha: string;
    justificada: boolean;
    motivo: string;
}

defineProps<{
    kpis: KPIProps;
    faltasRecientes: Falta[];
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
                        Aquí tienes el resumen de tu expediente, rendimiento académico y asistencias en tiempo real.
                    </p>
                </div>
                <div class="flex items-center gap-2 self-start rounded-full px-3 py-1 text-xs font-medium bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">
                    <UserCheck class="h-3.5 w-3.5" />
                    Rol: Estudiante Activo
                </div>
            </div>

            <!-- Alerta Faltas Pendientes -->
            <div v-if="kpis.faltas_sin_justificar > 0" class="relative overflow-hidden rounded-xl border border-rose-100 bg-gradient-to-r from-rose-50 to-red-50/30 p-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white shadow-md shadow-rose-500/20">
                        <AlertTriangle class="h-5 w-5 animate-pulse" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-rose-900">Tienes {{ kpis.faltas_sin_justificar }} faltas sin justificar</h3>
                        <p class="mt-1 text-sm text-rose-700/80">
                            Recuerda que tienes un límite de tiempo para solicitar la justificación de tus faltas. Puedes hacerlo adjuntando tu justificativo en formato PDF o imagen.
                        </p>
                        <div class="mt-4">
                            <Link :href="route('student.justificaciones')" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-rose-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">
                                Justificar Faltas
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Grid -->
            <div class="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-4">
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

                <!-- Faltas Card -->
                <div class="group relative overflow-hidden rounded-2xl p-5 shadow-sm transition-all hover:shadow-md sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold opacity-70 text-[var(--sispaa-text)]">Total de Faltas</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:color-mix(in_srgb,#E4BC57_35%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_65%,black)]" :class="{'!bg-red-100 !text-red-600': kpis.faltas_sin_justificar > 0}">
                            <AlertTriangle class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-[var(--sispaa-text)]">{{ kpis.total_faltas }}</span>
                        <span v-if="kpis.faltas_sin_justificar > 0" class="animate-pulse rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800">
                            {{ kpis.faltas_sin_justificar }} sin justificar
                        </span>
                    </div>
                    <p class="mt-2 text-xs opacity-50 text-[var(--sispaa-text)]">Historial completo en periodo actual</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 origin-left scale-x-0 transform bg-[color:color-mix(in_srgb,#E4BC57_70%,black)] transition-transform duration-300 group-hover:scale-x-100"></div>
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

            <!-- Secciones Secundarias: Faltas Recientes y Accesos -->
            <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
                <!-- Faltas Recientes List -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 lg:col-span-2 bg-[var(--sispaa-surface)]">
                    <div class="flex flex-col gap-2 border-b pb-4 sm:flex-row sm:items-center sm:justify-between border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-[var(--sispaa-text)]">
                            <AlertTriangle class="h-5 w-5 text-[color:color-mix(in_srgb,#E4BC57_70%,black)]" />
                            Faltas Recientes por Justificar
                        </h2>
                        <Link :href="route('student.asistencias')" class="text-xs font-semibold text-[var(--sispaa-primary)] hover:text-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            Ver todo el historial
                        </Link>
                    </div>

                    <div class="mt-4">
                        <div v-if="faltasRecientes.length === 0" class="flex flex-col items-center justify-center py-10 text-center">
                            <CheckCircle2 class="mb-2 h-12 w-12 text-[var(--sispaa-secondary)]" />
                            <p class="text-sm font-bold text-[var(--sispaa-text)]">¡Al día!</p>
                            <p class="mt-1 text-xs opacity-60 text-[var(--sispaa-text)]">No tienes faltas pendientes de justificar.</p>
                        </div>
                        <div v-else class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <div v-for="falta in faltasRecientes" :key="falta.id" class="flex flex-col gap-2 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ falta.materia.nombre }}</p>
                                    <div class="mt-1.5 flex items-center gap-3">
                                        <span class="flex items-center gap-1 text-xs opacity-60 text-[var(--sispaa-text)]">
                                            <Calendar class="h-3.5 w-3.5" />
                                            {{ falta.fecha }}
                                        </span>
                                        <span class="rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-medium text-rose-800">
                                            Sin justificar
                                        </span>
                                    </div>
                                </div>
                                <Link :href="route('student.justificaciones')" class="self-start rounded-lg border px-3 py-1.5 text-xs font-semibold transition-colors bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)]">
                                    Justificar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enlaces Rápidos / Accesos -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h2 class="border-b pb-4 text-lg font-bold text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        Accesos Rápidos
                    </h2>
                    <div class="mt-4 space-y-3">
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
        </div>
    </AppLayout>
</template>
