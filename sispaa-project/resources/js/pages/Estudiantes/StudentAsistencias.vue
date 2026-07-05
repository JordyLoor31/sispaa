<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Calendar, BookOpen, CheckCircle2, AlertTriangle, Clock, ArrowRight } from 'lucide-vue-next';

interface Materia {
    id: number;
    nombre: string;
}

interface Justificacion {
    id: number;
    estado: 'pendiente' | 'aprobada' | 'rechazada' | string;
}

interface Falta {
    id: number;
    materia: Materia;
    fecha: string;
    justificada: boolean;
    motivo: string;
    justificacion?: Justificacion | null;
}

defineProps<{
    faltas: Falta[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Mis Asistencias',
        href: '/estudiante/asistencias',
    },
];

// Determinar el tipo de inasistencia de forma simulada para el historial
const getTipo = (faltaId: number) => {
    return faltaId % 3 === 0 ? 'Atraso' : 'Falta';
};
</script>

<template>
    <Head title="Mis Asistencias" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Registro de Asistencias
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Consulta el desglose completo de faltas e inasistencias registradas en el periodo académico vigente.
                </p>
            </div>

            <!-- Tabla de historial -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/80">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Historial de Faltas y Atrasos</h3>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table v-if="faltas.length > 0" class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-850 text-xs text-slate-400 dark:text-slate-500 font-semibold uppercase">
                                <th class="py-3 px-4">Asignatura</th>
                                <th class="py-3 px-4">Fecha</th>
                                <th class="py-3 px-4">Tipo</th>
                                <th class="py-3 px-4">Evaluación</th>
                                <th class="py-3 px-4 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                            <tr v-for="falta in faltas" :key="falta.id" class="hover:bg-slate-50/50 dark:hover:bg-slate-900/20 transition-colors">
                                <td class="py-4 px-4 font-semibold text-slate-900 dark:text-white flex items-center gap-2">
                                    <BookOpen class="h-4 w-4 text-indigo-500 shrink-0" />
                                    {{ falta.materia.nombre }}
                                </td>
                                <td class="py-4 px-4 text-slate-500 dark:text-slate-400">
                                    <div class="flex items-center gap-1.5">
                                        <Calendar class="h-4 w-4 shrink-0" />
                                        {{ falta.fecha }}
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="rounded px-2 py-0.5 text-xs font-semibold" :class="[getTipo(falta.id) === 'Atraso' ? 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400' : 'bg-red-50 text-red-800 dark:bg-red-950/30 dark:text-red-400']">
                                        {{ getTipo(falta.id) }}
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span v-if="falta.justificada" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Justificada
                                    </span>
                                    <span v-else-if="falta.justificacion?.estado === 'pendiente'" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-400">
                                        <Clock class="h-3.5 w-3.5" />
                                        En revisión
                                    </span>
                                    <span v-else-if="falta.justificacion?.estado === 'rechazada'" class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
                                        <AlertTriangle class="h-3.5 w-3.5" />
                                        Rechazada
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-900 dark:text-slate-500">
                                        Sin justificar
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right">
                                    <Link v-if="!falta.justificada && !falta.justificacion" :href="route('student.justificaciones')" class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                                        Justificar
                                        <ArrowRight class="h-3.5 w-3.5" />
                                    </Link>
                                    <span v-else class="text-xs text-slate-400 dark:text-slate-500 font-medium">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <CheckCircle2 class="h-12 w-12 text-emerald-500 mb-2" />
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">¡Asistencia Perfecta!</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">No registras ninguna falta ni atraso en tu expediente.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
