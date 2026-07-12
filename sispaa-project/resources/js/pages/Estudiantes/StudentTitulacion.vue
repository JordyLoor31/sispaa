<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { GraduationCap, User, Calendar, CheckCircle2, Clock, Info } from 'lucide-vue-next';

interface Tutor {
    id: number;
    name: string;
    email: string;
}

interface Titulacion {
    id: number;
    tema: string;
    tutor: Tutor;
    estado: 'en_proceso' | 'defendido' | 'graduado' | string;
    fecha_inicio: string | null;
}

interface Etapa {
    id: number;
    nombre: string;
    completado: boolean;
    descripcion: string;
}

defineProps<{
    titulacion: Titulacion | null;
    etapas: Etapa[];
    progreso: number;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Mi Titulación',
        href: '/estudiante/titulacion',
    },
];
</script>

<template>
    <Head title="Mi Titulación" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Seguimiento de Titulación
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Monitorea el avance de tu tema de grado, tutorías y etapas correspondientes al proceso de titulación.
                </p>
            </div>

            <div v-if="!titulacion" class="rounded-2xl border border-slate-200/80 bg-white p-12 shadow-sm text-center dark:border-slate-800 dark:bg-slate-950 max-w-2xl w-full mx-auto">
                <GraduationCap class="h-16 w-16 text-slate-300 dark:text-slate-700 mx-auto mb-4" />
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Sin proceso de titulación activo</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Actualmente no registras ningún tema de grado o tutor asignado. 
                    Si te encuentras en periodo de titulación, comunícate con el Coordinador de tu Carrera para formalizar tu registro.
                </p>
            </div>

            <div v-else class="grid gap-6 lg:grid-cols-3">
                <!-- Tarjeta Principal del Tema y Tutor -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center gap-3 border-b border-slate-100 pb-4 dark:border-slate-800/80">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-400">
                                <GraduationCap class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-white">Tema de Grado Asignado</h3>
                                <span class="rounded-full bg-indigo-50 px-2 py-0.5 text-xxs font-semibold text-indigo-800 dark:bg-indigo-950/30 dark:text-indigo-400">
                                    {{ titulacion.estado.toUpperCase().replace('_', ' ') }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-lg font-extrabold text-slate-900 dark:text-white leading-relaxed">
                                "{{ titulacion.tema }}"
                            </p>

                            <!-- Detalles -->
                            <div class="grid gap-4 md:grid-cols-2 mt-6 pt-6 border-t border-slate-100 dark:border-slate-800/80">
                                <div class="flex items-start gap-3">
                                    <User class="h-5 w-5 text-slate-400 mt-0.5 shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-400 font-medium">Tutor Asignado</p>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-300">
                                            {{ titulacion.tutor.name }}
                                        </p>
                                        <p class="text-xs text-slate-400 dark:text-slate-500">
                                            {{ titulacion.tutor.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <Calendar class="h-5 w-5 text-slate-400 mt-0.5 shrink-0" />
                                    <div>
                                        <p class="text-xs text-slate-400 font-medium">Fecha de Registro</p>
                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-300">
                                            {{ titulacion.fecha_inicio ?? 'No registrada' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Avance General Progress Bar -->
                    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white">Avance Global</h3>
                            <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">{{ progreso }}%</span>
                        </div>
                        <Progress :model-value="progreso" class="mt-3 h-3" />
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-2">
                            El avance se incrementa automáticamente a medida que el tutor valida cada etapa aprobada en la plataforma.
                        </p>
                    </div>
                </div>

                <!-- Checklist de Etapas -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 pb-4 dark:border-slate-800/80">
                        Checklist de Etapas
                    </h3>

                    <div class="mt-6 space-y-6">
                        <div v-for="etapa in etapas" :key="etapa.id" class="flex items-start gap-3">
                            <div class="shrink-0 mt-0.5">
                                <CheckCircle2 v-if="etapa.completado" class="h-5 w-5 text-emerald-500" />
                                <Clock v-else class="h-5 w-5 text-slate-300 dark:text-slate-700" />
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold transition-colors" :class="[etapa.completado ? 'text-emerald-800 dark:text-emerald-400' : 'text-slate-800 dark:text-slate-300']">
                                    {{ etapa.nombre }}
                                </h4>
                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                    {{ etapa.descripcion }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
