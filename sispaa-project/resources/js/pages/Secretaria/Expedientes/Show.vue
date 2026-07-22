<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, XCircle, Clock, FileText, Eye } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { DocumentoRow } from './columns';

const props = defineProps<{
    documento: DocumentoRow;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoClasses: Record<string, string> = {
    pendiente: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
    aprobado: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
    rechazado: 'bg-rose-50 text-rose-800',
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <FileText class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                            {{ documento.tipo_documento }}
                        </h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
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

            <div class="max-w-2xl w-full mx-auto grid gap-4 sm:gap-6 sm:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoClasses[documento.estado]]">
                        <component :is="estadoIcon[documento.estado]" class="h-3.5 w-3.5" />
                        {{ estadoLabel[documento.estado] }}
                    </span>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Carrera</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ documento.estudiante.carrera ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Archivo</h4>
                <div v-if="documento.archivo_meta" class="flex items-center gap-3 rounded-lg p-3 bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                    <FileText class="h-8 w-8 shrink-0 text-[var(--sispaa-primary)] opacity-70" />
                    <div class="min-w-0 flex-1 text-xs">
                        <p class="truncate font-semibold text-[var(--sispaa-text)]">{{ documento.archivo_meta.name }}</p>
                        <p class="opacity-50 text-[var(--sispaa-text)]">{{ documento.archivo_meta.size }}</p>
                    </div>
                    <Button v-if="documento.archivo_url" as-child variant="outline" size="sm" class="shrink-0 inline-flex items-center gap-1.5">
                        <a :href="documento.archivo_url" target="_blank" rel="noopener noreferrer">
                            <Eye class="h-4 w-4" /> Ver documento
                        </a>
                    </Button>
                    <span v-else class="shrink-0 text-xs font-semibold text-rose-500">Archivo no disponible</span>
                </div>
                <p v-else class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin archivo adjunto.</p>
            </div>

            <!-- Revisión: solo si está pendiente -->
            <div v-if="documento.estado === 'pendiente'" class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Revisar documento</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Observación</label>
                        <textarea
                            v-model="reviewForm.observacion"
                            rows="3"
                            placeholder="Escribe una observación para el estudiante (obligatoria si vas a rechazar)..."
                            class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]"
                        />
                        <p v-if="reviewForm.errors.observacion" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.observacion }}</p>
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

            <!-- Ya revisado: histórico -->
            <div v-else class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Resultado de la revisión</h4>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Revisado por</p>
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ documento.revisado_por ?? '—' }}</p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ documento.reviewed_at_raw ?? documento.reviewed_at ?? '—' }}</p>
                    </div>
                    <div v-if="documento.observacion">
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Observación</p>
                        <p class="text-sm text-[var(--sispaa-text)]">{{ documento.observacion }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
