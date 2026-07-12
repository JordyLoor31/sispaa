<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, CheckCircle2, AlertCircle, Clock, Calendar, BookOpen, Upload, User, ChevronRight } from 'lucide-vue-next';

interface Materia {
    id: number;
    nombre: string;
}

interface Falta {
    id: number;
    materia: Materia;
    fecha: string;
    justificada: boolean;
    motivo: string;
}

interface SolicitudJustificacion {
    id: number;
    falta_id: number;
    falta: Falta;
    motivo_estudiante: string;
    archivo_adjunto: string | null;
    estado: 'pendiente' | 'aprobada' | 'rechazada' | string;
    comentario_docente: string | null;
    created_at: string;
}

defineProps<{
    solicitudes: SolicitudJustificacion[];
    faltasPorJustificar: Falta[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Mis Justificaciones',
        href: '/estudiante/justificaciones',
    },
];

const showRequestForm = ref(false);

const form = useForm({
    falta_id: '',
    motivo_estudiante: '',
    archivo: null as File | null,
});

const onFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        if (file.size > 5 * 1024 * 1024) {
            form.setError('archivo', 'El archivo no debe pesar más de 5MB.');
            return;
        }
        form.archivo = file;
        form.clearErrors('archivo');
    }
};

const submitRequest = () => {
    form.post(route('student.justificaciones.store'), {
        preserveScroll: true,
        onSuccess: () => {
            showRequestForm.value = false;
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Mis Justificaciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Justificaciones de Inasistencia
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Gestiona y solicita justificaciones para tus inasistencias a clases.
                    </p>
                </div>
                <button
                    v-if="!showRequestForm && faltasPorJustificar.length > 0"
                    @click="showRequestForm = true"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                >
                    <Upload class="h-4 w-4" />
                    Solicitar Justificación
                </button>
            </div>

            <!-- Formulario de Solicitud -->
            <div
                v-if="showRequestForm"
                class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 animate-in fade-in slide-in-from-top-4 duration-300"
            >
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/80">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Nueva Solicitud de Justificación</h3>
                    <button
                        @click="showRequestForm = false"
                        class="text-xs font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300"
                    >
                        Cancelar
                    </button>
                </div>

                <form @submit.prevent="submitRequest" class="mt-6 space-y-4 max-w-xl mx-auto">
                    <!-- Seleccionar Falta -->
                    <div>
                        <label for="falta" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            1. Selecciona la falta registrada *
                        </label>
                        <select
                            id="falta"
                            v-model="form.falta_id"
                            required
                            class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="" disabled>Selecciona una falta del historial...</option>
                            <option v-for="falta in faltasPorJustificar" :key="falta.id" :value="falta.id">
                                {{ falta.fecha }} - {{ falta.materia.nombre }}
                            </option>
                        </select>
                        <div v-if="form.errors.falta_id" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                            {{ form.errors.falta_id }}
                        </div>
                    </div>

                    <!-- Motivo -->
                    <div>
                        <label for="motivo" class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            2. Detalla el motivo de tu inasistencia *
                        </label>
                        <textarea
                            id="motivo"
                            v-model="form.motivo_estudiante"
                            required
                            rows="4"
                            placeholder="Escribe aquí los motivos detallados de tu falta. Ejemplo: Citas médicas, calamidad doméstica, etc."
                            class="mt-1.5 block w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>
                        <div v-if="form.errors.motivo_estudiante" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                            {{ form.errors.motivo_estudiante }}
                        </div>
                    </div>

                    <!-- Archivo de Respaldo -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300">
                            3. Adjunta un documento de respaldo (opcional)
                        </label>
                        <p class="text-xs text-slate-400 mt-0.5">Certificado médico, ticket de transporte, etc. Formatos PDF, JPG, PNG hasta 5MB.</p>
                        <input
                            type="file"
                            accept=".pdf,image/*"
                            @change="onFileChange"
                            class="mt-2 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-150 dark:file:bg-indigo-950/40 dark:file:text-indigo-400"
                        />
                        <div v-if="form.errors.archivo" class="text-xs text-rose-600 dark:text-rose-400 mt-1">
                            {{ form.errors.archivo }}
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="pt-4 flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50 transition-all"
                        >
                            {{ form.processing ? 'Enviando...' : 'Enviar Solicitud' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado de Solicitudes Enviadas -->
            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h3 class="text-base font-bold text-slate-900 dark:text-white border-b border-slate-100 pb-4 dark:border-slate-800/80">
                    Historial de Solicitudes
                </h3>

                <div class="mt-6">
                    <div v-if="solicitudes.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <Clock class="h-12 w-12 text-slate-300 dark:text-slate-700 mb-2" />
                        <p class="text-sm font-bold text-slate-700 dark:text-slate-300">No has enviado solicitudes aún</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            Si tienes faltas sin justificar, inicia una nueva solicitud arriba.
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="sol in solicitudes"
                            :key="sol.id"
                            class="p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 hover:shadow-sm transition-shadow bg-slate-50/30 dark:bg-slate-950/20"
                        >
                            <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <BookOpen class="h-4 w-4 text-indigo-500" />
                                        <span class="text-sm font-semibold text-slate-900 dark:text-white">
                                            {{ sol.falta.materia.nombre }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-400 dark:text-slate-500">
                                        <span class="flex items-center gap-1">
                                            <Calendar class="h-3.5 w-3.5" />
                                            Falta del {{ sol.falta.fecha }}
                                        </span>
                                        <span class="hidden md:inline">•</span>
                                        <span>Solicitada el {{ new Date(sol.created_at).toLocaleDateString() }}</span>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-900 p-2.5 rounded-lg border border-slate-100/50 dark:border-slate-800/40">
                                        <span class="font-semibold text-slate-700 dark:text-slate-300">Mi motivo:</span>
                                        {{ sol.motivo_estudiante }}
                                    </p>

                                    <!-- Comentario del Revisor -->
                                    <div v-if="sol.comentario_revisor" class="text-xs border-l-2 border-indigo-500 pl-2 text-indigo-700 dark:text-indigo-400 font-medium">
                                        Observación secretaría: {{ sol.comentario_revisor }}
                                    </div>
                                </div>

                                <!-- Estado y Archivo -->
                                <div class="flex items-center gap-3 self-end md:self-center shrink-0">
                                    <!-- Adjunto -->
                                    <a
                                        v-if="sol.archivo_adjunto"
                                        :href="sol.archivo_adjunto"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400"
                                    >
                                        <FileText class="h-4 w-4" />
                                        Ver justificativo
                                    </a>

                                    <!-- Badge Estado -->
                                    <span
                                        v-if="sol.estado === 'aprobada'"
                                        class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400"
                                    >
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Aprobada
                                    </span>
                                    <span
                                        v-else-if="sol.estado === 'pendiente'"
                                        class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-400"
                                    >
                                        <Clock class="h-3.5 w-3.5" />
                                        Pendiente
                                    </span>
                                    <span
                                        v-else-if="sol.estado === 'rechazada'"
                                        class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-950/30 dark:text-rose-400"
                                    >
                                        <AlertCircle class="h-3.5 w-3.5" />
                                        Rechazada
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
