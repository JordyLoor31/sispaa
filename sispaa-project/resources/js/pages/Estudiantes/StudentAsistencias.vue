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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Registro de Asistencias
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Consulta el desglose completo de faltas e inasistencias registradas en el periodo académico vigente.
                </p>
            </div>

            <!-- Tabla de historial -->
            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <div class="flex items-center justify-between border-b pb-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    <h3 class="text-base font-bold text-[var(--sispaa-text)]">Historial de Faltas y Atrasos</h3>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table v-if="faltas.length > 0" class="w-full border-collapse text-left">
                        <thead>
                            <tr class="border-b text-xs font-semibold uppercase opacity-50 text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                <th class="px-4 py-3">Asignatura</th>
                                <th class="px-4 py-3">Fecha</th>
                                <th class="px-4 py-3">Tipo</th>
                                <th class="px-4 py-3">Evaluación</th>
                                <th class="px-4 py-3 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                            <tr v-for="falta in faltas" :key="falta.id" class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]">
                                <td class="flex items-center gap-2 px-4 py-4 font-semibold text-[var(--sispaa-text)]">
                                    <BookOpen class="h-4 w-4 shrink-0 text-[var(--sispaa-primary)]" />
                                    {{ falta.materia.nombre }}
                                </td>
                                <td class="px-4 py-4 opacity-70 text-[var(--sispaa-text)]">
                                    <div class="flex items-center gap-1.5">
                                        <Calendar class="h-4 w-4 shrink-0" />
                                        {{ falta.fecha }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="rounded px-2 py-0.5 text-xs font-semibold" :class="[getTipo(falta.id) === 'Atraso' ? 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]' : 'bg-red-50 text-red-800']">
                                        {{ getTipo(falta.id) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span v-if="falta.justificada" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Justificada
                                    </span>
                                    <span v-else-if="falta.justificacion?.estado === 'pendiente'" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]">
                                        <Clock class="h-3.5 w-3.5" />
                                        En revisión
                                    </span>
                                    <span v-else-if="falta.justificacion?.estado === 'rechazada'" class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                        <AlertTriangle class="h-3.5 w-3.5" />
                                        Rechazada
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_55%,transparent)]">
                                        Sin justificar
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <Link v-if="!falta.justificada && !falta.justificacion" :href="route('student.justificaciones')" class="inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:text-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                                        Justificar
                                        <ArrowRight class="h-3.5 w-3.5" />
                                    </Link>
                                    <span v-else class="text-xs font-medium opacity-50 text-[var(--sispaa-text)]">-</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div v-else class="flex flex-col items-center justify-center py-12 text-center">
                        <CheckCircle2 class="mb-2 h-12 w-12 text-[var(--sispaa-secondary)]" />
                        <p class="text-sm font-bold opacity-80 text-[var(--sispaa-text)]">¡Asistencia Perfecta!</p>
                        <p class="mt-1 text-xs opacity-60 text-[var(--sispaa-text)]">No registras ninguna falta ni atraso en tu expediente.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
