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
    pendiente: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
    aprobada: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    rechazada: 'bg-rose-50 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400',
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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ justificacion.estudiante.name }}
                    </h1>
                    <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                        {{ justificacion.estudiante.cedula ?? justificacion.estudiante.email }} · {{ justificacion.estudiante.carrera ?? '—' }}
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('secretaria.justificaciones.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoClasses[justificacion.estado]]">
                        <component :is="estadoIcon[justificacion.estado]" class="h-3.5 w-3.5" />
                        {{ estadoLabel(justificacion.estado) }}
                    </span>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Falta registrada</h4>
                    <div class="mt-2 space-y-1">
                        <div class="flex items-center gap-1.5 text-sm text-slate-700 dark:text-slate-300">
                            <Calendar class="h-3.5 w-3.5 text-slate-400" /> {{ justificacion.falta.fecha }}
                        </div>
                        <div class="flex items-center gap-1.5 text-sm text-slate-500">
                            <BookOpen class="h-3.5 w-3.5 text-slate-400" /> {{ justificacion.falta.materia ?? 'Sin materia' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Motivo del estudiante</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ justificacion.motivo_estudiante }}</p>
                <a v-if="justificacion.archivo_adjunto" :href="justificacion.archivo_adjunto" target="_blank"
                    class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg px-2.5 py-1.5">
                    <FileText class="h-3.5 w-3.5" /> Ver adjunto
                </a>
            </div>

            <!-- Revisión: solo si está pendiente -->
            <div v-if="justificacion.estado === 'pendiente'" class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Revisar solicitud</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Comentario para el estudiante</label>
                        <textarea
                            v-model="reviewForm.comentario_revisor"
                            rows="3"
                            placeholder="Escribe un comentario que verá el estudiante (obligatorio si vas a rechazar)..."
                            class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <p v-if="reviewForm.errors.comentario_revisor" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.comentario_revisor }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button :disabled="reviewForm.processing" @click="submitReview('aprobar')" class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold">
                            <CheckCircle2 class="h-4 w-4 mr-1.5" /> Aprobar
                        </Button>
                        <Button :disabled="reviewForm.processing" @click="submitReview('rechazar')" class="bg-rose-600 hover:bg-rose-500 text-white font-semibold">
                            <XCircle class="h-4 w-4 mr-1.5" /> Rechazar
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Ya revisado -->
            <div v-else class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Comentario del revisor</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ justificacion.comentario_revisor ?? '—' }}</p>
            </div>
        </div>
    </AppSidebarLayout>
</template>
