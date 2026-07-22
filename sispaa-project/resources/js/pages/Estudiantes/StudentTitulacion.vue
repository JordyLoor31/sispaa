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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Seguimiento de Titulación
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Monitorea el avance de tu tema de grado, tutorías y etapas correspondientes al proceso de titulación.
                </p>
            </div>

            <div v-if="!titulacion" class="mx-auto w-full max-w-2xl rounded-2xl p-12 text-center shadow-sm bg-[var(--sispaa-surface)]">
                <GraduationCap class="mx-auto mb-4 h-16 w-16 opacity-30 text-[var(--sispaa-text)]" />
                <h3 class="text-lg font-bold text-[var(--sispaa-text)]">Sin proceso de titulación activo</h3>
                <p class="mt-2 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Actualmente no registras ningún tema de grado o tutor asignado.
                    Si te encuentras en periodo de titulación, comunícate con el Coordinador de tu Carrera para formalizar tu registro.
                </p>
            </div>

            <div v-else class="grid gap-4 sm:gap-6 lg:grid-cols-3">
                <!-- Tarjeta Principal del Tema y Tutor -->
                <div class="space-y-4 sm:space-y-6 lg:col-span-2">
                    <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                        <div class="flex items-center gap-3 border-b pb-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[color:color-mix(in_srgb,var(--sispaa-primary)_20%,transparent)] text-[var(--sispaa-primary)]">
                                <GraduationCap class="h-5 w-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-[var(--sispaa-text)]">Tema de Grado Asignado</h3>
                                <span class="text-xxs rounded-full px-2 py-0.5 font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]">
                                    {{ titulacion.estado.toUpperCase().replace('_', ' ') }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-lg font-extrabold leading-relaxed text-[var(--sispaa-text)]">
                                "{{ titulacion.tema }}"
                            </p>

                            <!-- Detalles -->
                            <div class="mt-6 grid gap-4 border-t pt-6 md:grid-cols-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                <div class="flex items-start gap-3">
                                    <User class="mt-0.5 h-5 w-5 shrink-0 opacity-40 text-[var(--sispaa-text)]" />
                                    <div>
                                        <p class="text-xs font-medium opacity-50 text-[var(--sispaa-text)]">Tutor Asignado</p>
                                        <p class="text-sm font-semibold opacity-90 text-[var(--sispaa-text)]">
                                            {{ titulacion.tutor.name }}
                                        </p>
                                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">
                                            {{ titulacion.tutor.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-3">
                                    <Calendar class="mt-0.5 h-5 w-5 shrink-0 opacity-40 text-[var(--sispaa-text)]" />
                                    <div>
                                        <p class="text-xs font-medium opacity-50 text-[var(--sispaa-text)]">Fecha de Registro</p>
                                        <p class="text-sm font-semibold opacity-90 text-[var(--sispaa-text)]">
                                            {{ titulacion.fecha_inicio ?? 'No registrada' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Avance General Progress Bar -->
                    <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Avance Global</h3>
                            <span class="text-lg font-black text-[var(--sispaa-primary)]">{{ progreso }}%</span>
                        </div>
                        <Progress :model-value="progreso" class="mt-3 h-3" />
                        <p class="mt-2 text-[10px] opacity-50 text-[var(--sispaa-text)]">
                            El avance se incrementa automáticamente a medida que el tutor valida cada etapa aprobada en la plataforma.
                        </p>
                    </div>
                </div>

                <!-- Checklist de Etapas -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h3 class="border-b pb-4 text-base font-bold text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        Checklist de Etapas
                    </h3>

                    <div class="mt-6 space-y-6">
                        <div v-for="etapa in etapas" :key="etapa.id" class="flex items-start gap-3">
                            <div class="mt-0.5 shrink-0">
                                <CheckCircle2 v-if="etapa.completado" class="h-5 w-5 text-[var(--sispaa-secondary)]" />
                                <Clock v-else class="h-5 w-5 opacity-30 text-[var(--sispaa-text)]" />
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold transition-colors" :class="[etapa.completado ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]' : 'opacity-80 text-[var(--sispaa-text)]']">
                                    {{ etapa.nombre }}
                                </h4>
                                <p class="mt-0.5 text-xs opacity-50 text-[var(--sispaa-text)]">
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
