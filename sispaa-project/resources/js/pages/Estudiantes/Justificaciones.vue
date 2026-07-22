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
    comentario_revisor?: string | null;
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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Justificaciones de Inasistencia
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Gestiona y solicita justificaciones para tus inasistencias a clases.
                    </p>
                </div>
                <button
                    v-if="!showRequestForm && faltasPorJustificar.length > 0"
                    @click="showRequestForm = true"
                    class="inline-flex items-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                >
                    <Upload class="h-4 w-4" />
                    Solicitar Justificación
                </button>
            </div>

            <!-- Formulario de Solicitud -->
            <div
                v-if="showRequestForm"
                class="animate-in fade-in slide-in-from-top-4 duration-300 rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]"
            >
                <div class="flex items-center justify-between border-b pb-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    <h3 class="text-base font-bold text-[var(--sispaa-text)]">Nueva Solicitud de Justificación</h3>
                    <button
                        @click="showRequestForm = false"
                        class="text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100"
                    >
                        Cancelar
                    </button>
                </div>

                <form @submit.prevent="submitRequest" class="mx-auto mt-6 max-w-xl space-y-4">
                    <!-- Seleccionar Falta -->
                    <div>
                        <label for="falta" class="block text-sm font-semibold text-[var(--sispaa-text)]">
                            1. Selecciona la falta registrada *
                        </label>
                        <select
                            id="falta"
                            v-model="form.falta_id"
                            required
                            class="mt-1.5 block w-full rounded-lg text-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:border-[var(--sispaa-primary)] focus:ring-[var(--sispaa-primary)]"
                        >
                            <option value="" disabled>Selecciona una falta del historial...</option>
                            <option v-for="falta in faltasPorJustificar" :key="falta.id" :value="falta.id">
                                {{ falta.fecha }} - {{ falta.materia.nombre }}
                            </option>
                        </select>
                        <div v-if="form.errors.falta_id" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.falta_id }}
                        </div>
                    </div>

                    <!-- Motivo -->
                    <div>
                        <label for="motivo" class="block text-sm font-semibold text-[var(--sispaa-text)]">
                            2. Detalla el motivo de tu inasistencia *
                        </label>
                        <textarea
                            id="motivo"
                            v-model="form.motivo_estudiante"
                            required
                            rows="4"
                            placeholder="Escribe aquí los motivos detallados de tu falta. Ejemplo: Citas médicas, calamidad doméstica, etc."
                            class="mt-1.5 block w-full rounded-lg text-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:border-[var(--sispaa-primary)] focus:ring-[var(--sispaa-primary)]"
                        ></textarea>
                        <div v-if="form.errors.motivo_estudiante" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.motivo_estudiante }}
                        </div>
                    </div>

                    <!-- Archivo de Respaldo -->
                    <div>
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)]">
                            3. Adjunta un documento de respaldo (opcional)
                        </label>
                        <p class="mt-0.5 text-xs opacity-60 text-[var(--sispaa-text)]">Certificado médico, ticket de transporte, etc. Formatos PDF, JPG, PNG hasta 5MB.</p>
                        <input
                            type="file"
                            accept=".pdf,image/*"
                            @change="onFileChange"
                            class="mt-2 block w-full text-xs opacity-70 text-[var(--sispaa-text)] file:mr-4 file:rounded-lg file:border-0 file:px-4 file:py-2 file:text-xs file:font-semibold file:text-white file:bg-[var(--sispaa-primary)] hover:file:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                        />
                        <div v-if="form.errors.archivo" class="mt-1 text-xs text-rose-600">
                            {{ form.errors.archivo }}
                        </div>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center gap-3 pt-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition-all disabled:opacity-50 bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                        >
                            {{ form.processing ? 'Enviando...' : 'Enviar Solicitud' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- Listado de Solicitudes Enviadas -->
            <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h3 class="border-b pb-4 text-base font-bold text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    Historial de Solicitudes
                </h3>

                <div class="mt-6">
                    <div v-if="solicitudes.length === 0" class="flex flex-col items-center justify-center py-12 text-center">
                        <Clock class="mb-2 h-12 w-12 opacity-30 text-[var(--sispaa-text)]" />
                        <p class="text-sm font-bold opacity-80 text-[var(--sispaa-text)]">No has enviado solicitudes aún</p>
                        <p class="mt-1 text-xs opacity-60 text-[var(--sispaa-text)]">
                            Si tienes faltas sin justificar, inicia una nueva solicitud arriba.
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <div
                            v-for="sol in solicitudes"
                            :key="sol.id"
                            class="rounded-xl p-4 transition-shadow hover:shadow-sm bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"
                        >
                            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <BookOpen class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                        <span class="text-sm font-semibold text-[var(--sispaa-text)]">
                                            {{ sol.falta.materia.nombre }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3 text-xs opacity-60 text-[var(--sispaa-text)]">
                                        <span class="flex items-center gap-1">
                                            <Calendar class="h-3.5 w-3.5" />
                                            Falta del {{ sol.falta.fecha }}
                                        </span>
                                        <span class="hidden md:inline">•</span>
                                        <span>Solicitada el {{ new Date(sol.created_at).toLocaleDateString() }}</span>
                                    </div>
                                    <p class="rounded-lg p-2.5 text-xs bg-[var(--sispaa-surface)] text-[var(--sispaa-text)] opacity-90 border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                        <span class="font-semibold opacity-100">Mi motivo:</span>
                                        {{ sol.motivo_estudiante }}
                                    </p>

                                    <!-- Comentario del Revisor -->
                                    <div v-if="sol.comentario_revisor" class="border-l-2 pl-2 text-xs font-medium border-[var(--sispaa-primary)] text-[var(--sispaa-primary)]">
                                        Observación secretaría: {{ sol.comentario_revisor }}
                                    </div>
                                </div>

                                <!-- Estado y Archivo -->
                                <div class="flex shrink-0 items-center gap-3 self-end md:self-center">
                                    <!-- Adjunto -->
                                    <a
                                        v-if="sol.archivo_adjunto"
                                        :href="sol.archivo_adjunto"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:text-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]"
                                    >
                                        <FileText class="h-4 w-4" />
                                        Ver justificativo
                                    </a>

                                    <!-- Badge Estado -->
                                    <span
                                        v-if="sol.estado === 'aprobada'"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]"
                                    >
                                        <CheckCircle2 class="h-3.5 w-3.5" />
                                        Aprobada
                                    </span>
                                    <span
                                        v-else-if="sol.estado === 'pendiente'"
                                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]"
                                    >
                                        <Clock class="h-3.5 w-3.5" />
                                        Pendiente
                                    </span>
                                    <span
                                        v-else-if="sol.estado === 'rechazada'"
                                        class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800"
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
