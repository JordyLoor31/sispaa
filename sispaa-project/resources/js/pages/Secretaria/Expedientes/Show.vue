<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, XCircle, Clock, FileText } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { DocumentoRow } from './columns';

const props = defineProps<{
    documento: DocumentoRow;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoClasses: Record<string, string> = {
    pendiente: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
    aprobado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    rechazado: 'bg-rose-50 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400',
};
const estadoLabel: Record<string, string> = {
    pendiente: 'Pendiente',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado',
};
const estadoIcon = { pendiente: Clock, aprobado: CheckCircle2, rechazado: XCircle };

const reviewForm = useForm({ accion: '' as '' | 'aprobar' | 'rechazar', observacion: '' });

const submitReview = (accion: 'aprobar' | 'rechazar') => {
    reviewForm.accion = accion;
    if (accion === 'rechazar' && reviewForm.observacion.trim().length < 5) {
        reviewForm.setError('observacion', 'Debes indicar el motivo del rechazo (mínimo 5 caracteres).');
        return;
    }
    reviewForm.patch(route('secretaria.expediente.review', props.documento.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(accion === 'aprobar'
            ? '✓ Documento aprobado correctamente.'
            : 'Documento rechazado. Estudiante notificado.'),
        onError: () => toast.error('Error al procesar la revisión.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${documento.tipo_documento} — ${documento.estudiante.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <FileText class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                            {{ documento.tipo_documento }}
                        </h1>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            {{ documento.estudiante.name }} · {{ documento.estudiante.cedula ?? documento.estudiante.email }}
                        </p>
                    </div>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('secretaria.expediente.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoClasses[documento.estado]]">
                        <component :is="estadoIcon[documento.estado]" class="h-3.5 w-3.5" />
                        {{ estadoLabel[documento.estado] }}
                    </span>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Carrera</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ documento.estudiante.carrera ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Archivo</h4>
                <div v-if="documento.archivo_meta" class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                    <FileText class="h-8 w-8 text-indigo-400 shrink-0" />
                    <div class="flex-1 min-w-0 text-xs">
                        <p class="font-semibold text-slate-700 dark:text-slate-200 truncate">{{ documento.archivo_meta.name }}</p>
                        <p class="text-slate-400">{{ documento.archivo_meta.size }}</p>
                    </div>
                    <a v-if="documento.archivo_url" :href="documento.archivo_url" target="_blank" class="shrink-0 text-xs font-semibold text-indigo-600 hover:underline">Ver PDF</a>
                </div>
                <p v-else class="text-sm text-slate-400">Sin archivo adjunto.</p>
            </div>

            <!-- Revisión: solo si está pendiente -->
            <div v-if="documento.estado === 'pendiente'" class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Revisar documento</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observación</label>
                        <textarea
                            v-model="reviewForm.observacion"
                            rows="3"
                            placeholder="Escribe una observación para el estudiante (obligatoria si vas a rechazar)..."
                            class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <p v-if="reviewForm.errors.observacion" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.observacion }}</p>
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

            <!-- Ya revisado: histórico -->
            <div v-else class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Resultado de la revisión</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Revisado por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ documento.revisado_por ?? '—' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ documento.reviewed_at_raw ?? documento.reviewed_at ?? '—' }}</p>
                    </div>
                    <div v-if="documento.observacion">
                        <p class="text-xs text-slate-400">Observación</p>
                        <p class="text-sm text-slate-700 dark:text-slate-300">{{ documento.observacion }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
