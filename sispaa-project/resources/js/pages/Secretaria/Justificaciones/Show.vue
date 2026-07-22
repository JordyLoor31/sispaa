<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, XCircle, Clock, FileText, Calendar, BookOpen } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { JustificacionRow } from './columns';

const props = defineProps<{
    justificacion: JustificacionRow;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoClasses: Record<string, string> = {
    pendiente: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
    aprobada: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
    rechazada: 'bg-rose-50 text-rose-800',
};
const estadoIcon = { pendiente: Clock, aprobada: CheckCircle2, rechazada: XCircle };
const estadoLabel = (e: string) => e.charAt(0).toUpperCase() + e.slice(1);

const reviewForm = useForm({ accion: '' as '' | 'aprobar' | 'rechazar', comentario_revisor: '' });

const submitReview = (accion: 'aprobar' | 'rechazar') => {
    reviewForm.accion = accion;
    if (accion === 'rechazar' && reviewForm.comentario_revisor.trim().length < 5) {
        reviewForm.setError('comentario_revisor', 'Debes indicar el motivo del rechazo (mínimo 5 caracteres).');
        return;
    }
    reviewForm.patch(route('secretaria.justificacion.review', props.justificacion.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(accion === 'aprobar'
            ? '✓ Justificación aprobada. Falta marcada como justificada.'
            : 'Justificación rechazada. Estudiante notificado.'),
        onError: () => toast.error('Error al procesar la revisión.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Justificación — ${justificacion.estudiante.name}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        {{ justificacion.estudiante.name }}
                    </h1>
                    <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                        {{ justificacion.estudiante.cedula ?? justificacion.estudiante.email }} · {{ justificacion.estudiante.carrera ?? '—' }}
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('secretaria.justificaciones.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-4 sm:gap-6 sm:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoClasses[justificacion.estado]]">
                        <component :is="estadoIcon[justificacion.estado]" class="h-3.5 w-3.5" />
                        {{ estadoLabel(justificacion.estado) }}
                    </span>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Falta registrada</h4>
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center gap-1.5 text-sm text-[var(--sispaa-text)]">
                            <Calendar class="h-3.5 w-3.5 opacity-50" /> {{ justificacion.falta.fecha }}
                        </div>
                        <div class="flex items-center gap-1.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                            <BookOpen class="h-3.5 w-3.5 opacity-50" /> {{ justificacion.falta.materia ?? 'Sin materia' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-2">Motivo del estudiante</h4>
                <p class="text-sm text-[var(--sispaa-text)]">{{ justificacion.motivo_estudiante }}</p>
                <a v-if="justificacion.archivo_url" :href="justificacion.archivo_url" target="_blank"
                    class="mt-3 inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80 border border-[color:color-mix(in_srgb,var(--sispaa-primary)_35%,transparent)]">
                    <FileText class="h-3.5 w-3.5" /> Ver adjunto
                </a>
            </div>

            <!-- Revisión: solo si está pendiente -->
            <div v-if="justificacion.estado === 'pendiente'" class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Revisar solicitud</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Comentario para el estudiante</label>
                        <textarea
                            v-model="reviewForm.comentario_revisor"
                            rows="3"
                            placeholder="Escribe un comentario que verá el estudiante (obligatorio si vas a rechazar)..."
                            class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]"
                        />
                        <p v-if="reviewForm.errors.comentario_revisor" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.comentario_revisor }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <Button :disabled="reviewForm.processing" @click="submitReview('aprobar')" class="bg-[var(--sispaa-secondary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)] text-white font-semibold">
                            <CheckCircle2 class="h-4 w-4 mr-1.5" /> Aprobar
                        </Button>
                        <Button :disabled="reviewForm.processing" @click="submitReview('rechazar')" class="bg-rose-600 hover:bg-rose-500 text-white font-semibold">
                            <XCircle class="h-4 w-4 mr-1.5" /> Rechazar
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Ya revisado -->
            <div v-else class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-2">Comentario del revisor</h4>
                <p class="text-sm text-[var(--sispaa-text)]">{{ justificacion.comentario_revisor ?? '—' }}</p>
            </div>
        </div>
    </AppSidebarLayout>
</template>
