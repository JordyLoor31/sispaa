<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header Section -->
            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        ¡Hola de nuevo!
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">
                        Aquí tienes el resumen de tu expediente, rendimiento académico y asistencias en tiempo real.
                    </p>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                    <UserCheck class="h-3.5 w-3.5" />
                    Rol: Estudiante Activo
                </div>
            </div>

            <!-- Alerta Faltas Pendientes -->
            <div v-if="kpis.faltas_sin_justificar > 0" class="relative overflow-hidden rounded-xl border border-rose-100 bg-gradient-to-r from-rose-50 to-red-50/30 p-5 dark:border-rose-950/20 dark:from-rose-950/10 dark:to-red-950/5">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-500 text-white shadow-md shadow-rose-500/20">
                        <AlertTriangle class="h-5 w-5 animate-pulse" />
                    </div>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-rose-900 dark:text-rose-400">Tienes {{ kpis.faltas_sin_justificar }} faltas sin justificar</h3>
                        <p class="mt-1 text-sm text-rose-700/80 dark:text-rose-400/80">
                            Recuerda que tienes un límite de tiempo para solicitar la justificación de tus faltas. Puedes hacerlo adjuntando tu justificativo en formato PDF o imagen.
                        </p>
                        <div class="mt-4">
                            <Link :href="route('student.justificaciones')" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-rose-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600 transition-colors">
                                Justificar Faltas
                                <ArrowRight class="h-3.5 w-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KPIs Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Promedio Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Promedio General</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-600 dark:bg-violet-950/40 dark:text-violet-400">
                            <BookOpen class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-slate-900 dark:text-white">{{ kpis.promedio }}</span>
                        <span class="text-sm text-slate-400 dark:text-slate-500">/ 10.0</span>
                    </div>
                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Rendimiento académico actual</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-violet-500 to-indigo-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                </div>

                <!-- Semestre Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Periodo / Nivel</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                            <Calendar class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="text-3xl font-extrabold text-slate-900 dark:text-white">{{ kpis.semestre }}</span>
                    </div>
                    <p class="mt-4 text-xs text-slate-400 dark:text-slate-500">Matrícula vigente activa</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-500 to-cyan-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                </div>

                <!-- Faltas Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Total de Faltas</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400" :class="{'bg-red-100 text-red-600 dark:bg-red-950/40 dark:text-red-400': kpis.faltas_sin_justificar > 0}">
                            <AlertTriangle class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-slate-900 dark:text-white">{{ kpis.total_faltas }}</span>
                        <span v-if="kpis.faltas_sin_justificar > 0" class="rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-800 dark:bg-rose-950/50 dark:text-rose-400 animate-pulse">
                            {{ kpis.faltas_sin_justificar }} sin justificar
                        </span>
                    </div>
                    <p class="mt-2 text-xs text-slate-400 dark:text-slate-500">Historial completo en periodo actual</p>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                </div>

                <!-- Expediente SGA Card -->
                <div class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-500 dark:text-slate-400">Expediente SGA</span>
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                            <FileCheck class="h-5 w-5" />
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold text-slate-900 dark:text-white">{{ kpis.avance_sga }}%</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500">de avance</span>
                        </div>
                        <div class="mt-3 h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all duration-500" :style="{ width: kpis.avance_sga + '%' }"></div>
                        </div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
                </div>
            </div>

            <!-- Secciones Secundarias: Faltas Recientes y Accesos -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Faltas Recientes List -->
                <div class="lg:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/80">
                        <h2 class="text-lg font-bold text-slate-950 dark:text-white flex items-center gap-2">
                            <AlertTriangle class="h-5 w-5 text-amber-500" />
                            Faltas Recientes por Justificar
                        </h2>
                        <Link :href="route('student.asistencias')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                            Ver todo el historial
                        </Link>
                    </div>

                    <div class="mt-4">
                        <div v-if="faltasRecientes.length === 0" class="flex flex-col items-center justify-center py-10 text-center">
                            <CheckCircle2 class="h-12 w-12 text-emerald-500 mb-2" />
                            <p class="text-sm font-bold text-slate-900 dark:text-white">¡Al día!</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">No tienes faltas pendientes de justificar.</p>
                        </div>
                        <div v-else class="divide-y divide-slate-100 dark:divide-slate-800/60">
                            <div v-for="falta in faltasRecientes" :key="falta.id" class="flex items-center justify-between py-4 first:pt-0 last:pb-0">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ falta.materia.nombre }}</p>
                                    <div class="flex items-center gap-3 mt-1.5">
                                        <span class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <Calendar class="h-3.5 w-3.5" />
                                            {{ falta.fecha }}
                                        </span>
                                        <span class="rounded bg-rose-50 px-1.5 py-0.5 text-[10px] font-medium text-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
                                            Sin justificar
                                        </span>
                                    </div>
                                </div>
                                <Link :href="route('student.justificaciones')" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 dark:hover:bg-slate-900 transition-colors">
                                    Justificar
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enlaces Rápidos / Accesos -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-lg font-bold text-slate-950 dark:text-white border-b border-slate-100 pb-4 dark:border-slate-800/80">
                        Accesos Rápidos
                    </h2>
                    <div class="mt-4 space-y-3">
                        <Link :href="route('student.documentos')" class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50 dark:border-slate-800/50 dark:hover:bg-slate-900/60 transition-colors">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 dark:bg-emerald-950/40 dark:text-emerald-400">
                                <FileCheck class="h-5 w-5" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Cargar Expediente</p>
                                <p class="text-xs text-slate-500">Sube tus documentos SGA</p>
                            </div>
                        </Link>

                        <Link :href="route('student.titulacion')" class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50 dark:border-slate-800/50 dark:hover:bg-slate-900/60 transition-colors">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                <BookOpen class="h-5 w-5" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Mi Titulación</p>
                                <p class="text-xs text-slate-500">Monitorea tu tema y tutor</p>
                            </div>
                        </Link>

                        <Link :href="route('student.perfil')" class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 hover:bg-slate-50 dark:border-slate-800/50 dark:hover:bg-slate-900/60 transition-colors">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-950/40 dark:text-blue-400">
                                <UserCheck class="h-5 w-5" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">Ver Mi Perfil</p>
                                <p class="text-xs text-slate-500">Consulta tus datos personales</p>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
