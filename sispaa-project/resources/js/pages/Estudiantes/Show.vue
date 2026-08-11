<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, XCircle, Clock, FileText, Eye, GraduationCap, Users } from 'lucide-vue-next';
import { ref } from 'vue';
import { toast } from 'vue-sonner';
import { BRAND_GRADIENT, STATUS_COLORS, tintedBadgeStyle, neutralBadgeStyle } from '@/lib/brand';
import { Button } from '@/components/ui/button';

interface ArchivoMeta {
    name: string;
    size: string | null;
}

interface DocumentoRow {
    id: number;
    tipo_documento: string;
    estado: 'pendiente' | 'aprobado' | 'rechazado';
    observacion: string | null;
    reviewed_at: string | null;
    reviewed_at_raw: string | null;
    created_at: string;
    archivo_url: string | null;
    archivo_meta: ArchivoMeta | null;
    revisado_por: string | null;
}

interface EstudianteShow {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    telefono: string | null;
    carrera: string | null;
}

const props = defineProps<{
    estudiante: EstudianteShow;
    documentos: DocumentoRow[];
    stats: { pendientes: number; aprobados: number; rechazados: number; total: number };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoStyles: Record<string, string> = {
    pendiente: tintedBadgeStyle(STATUS_COLORS.advertencia),
    aprobado: tintedBadgeStyle(STATUS_COLORS.exito),
    rechazado: tintedBadgeStyle(STATUS_COLORS.peligro),
};
const estadoLabel: Record<string, string> = {
    pendiente: 'Pendiente',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado',
};
const estadoIcon = { pendiente: Clock, aprobado: CheckCircle2, rechazado: XCircle };

const observaciones = ref<Record<number, string>>({});
const reviewForm = useForm({ accion: '' as '' | 'aprobar' | 'rechazar', observacion: '' });

const submitReview = (doc: DocumentoRow, accion: 'aprobar' | 'rechazar') => {
    reviewForm.accion = accion;
    reviewForm.observacion = observaciones.value[doc.id] ?? '';
    if (accion === 'rechazar' && reviewForm.observacion.trim().length < 5) {
        reviewForm.setError('observacion', 'Debes indicar el motivo del rechazo (mínimo 5 caracteres).');
        return;
    }
    reviewForm.patch(
        route('estudiantes.review', { estudiante: props.estudiante.id, documento: doc.id }),
        {
            preserveScroll: true,
            onError: () => toast.error('Error al procesar la revisión.'),
        },
    );
};

const statCards = [
    { label: 'Pendientes', value: props.stats.pendientes, color: tintedBadgeStyle(STATUS_COLORS.advertencia) },
    { label: 'Aprobados', value: props.stats.aprobados, color: tintedBadgeStyle(STATUS_COLORS.exito) },
    { label: 'Rechazados', value: props.stats.rechazados, color: tintedBadgeStyle(STATUS_COLORS.peligro) },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${estudiante.name} — Expediente`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ estudiante.name }}</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                            {{ estudiante.cedula ?? estudiante.email }}
                            <span v-if="estudiante.carrera" class="mx-1.5 opacity-40">·</span>
                            {{ estudiante.carrera }}
                        </p>
                    </div>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('estudiantes.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver a Estudiantes
                    </Link>
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid w-full grid-cols-3 gap-3 sm:gap-4">
                <div v-for="c in statCards" :key="c.label" class="rounded-2xl p-4 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">{{ c.label }}</p>
                    <span class="mt-2 inline-flex rounded-full px-2.5 py-1 text-sm font-bold" :style="c.color">{{ c.value }}</span>
                </div>
            </div>

            <!-- Documentos del expediente -->
            <div class="w-full space-y-3">
                <h2 class="text-sm font-bold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">
                    Documentos del expediente ({{ stats.total }})
                </h2>

                <div v-if="documentos.length" class="space-y-3">
                    <div
                        v-for="doc in documentos"
                        :key="doc.id"
                        class="rounded-2xl p-4 sm:p-5 shadow-sm bg-[var(--sispaa-surface)]"
                    >
                        <!-- Encabezado del documento -->
                        <div class="flex flex-wrap items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                <FileText class="h-4 w-4 text-[var(--sispaa-primary)]" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-[var(--sispaa-text)]">{{ doc.tipo_documento }}</p>
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Subido el {{ doc.created_at }}</p>
                            </div>
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold"
                                :style="estadoStyles[doc.estado] ?? neutralBadgeStyle()"
                            >
                                <component :is="estadoIcon[doc.estado]" class="h-3.5 w-3.5" />
                                {{ estadoLabel[doc.estado] ?? doc.estado }}
                            </span>
                        </div>

                        <!-- Archivo -->
                        <div class="mt-3 flex items-center gap-3 rounded-lg p-3 bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                            <div class="min-w-0 flex-1 text-xs">
                                <p class="truncate font-semibold text-[var(--sispaa-text)]">{{ doc.archivo_meta?.name ?? 'Documento sin archivo' }}</p>
                                <p v-if="doc.archivo_meta?.size" class="opacity-50 text-[var(--sispaa-text)]">{{ doc.archivo_meta.size }}</p>
                            </div>
                            <Button v-if="doc.archivo_url" as-child variant="outline" size="sm" class="shrink-0 inline-flex items-center gap-1.5">
                                <a :href="doc.archivo_url" target="_blank" rel="noopener noreferrer">
                                    <Eye class="h-4 w-4" /> Ver documento
                                </a>
                            </Button>
                            <span v-else class="shrink-0 text-xs font-semibold text-rose-500">Archivo no disponible</span>
                        </div>

                        <!-- Revisión pendiente -->
                        <div v-if="doc.estado === 'pendiente'" class="mt-3 space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Observación</label>
                                <textarea
                                    v-model="observaciones[doc.id]"
                                    rows="2"
                                    placeholder="Escribe una observación para el estudiante (obligatoria si vas a rechazar)..."
                                    class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]"
                                />
                                <p v-if="reviewForm.errors.observacion" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.observacion }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <Button :disabled="reviewForm.processing" @click="submitReview(doc, 'aprobar')" class="bg-[var(--sispaa-secondary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)] text-white font-semibold">
                                    <CheckCircle2 class="h-4 w-4 mr-1.5" /> Aprobar
                                </Button>
                                <Button :disabled="reviewForm.processing" @click="submitReview(doc, 'rechazar')" class="bg-rose-600 hover:bg-rose-500 text-white font-semibold">
                                    <XCircle class="h-4 w-4 mr-1.5" /> Rechazar
                                </Button>
                            </div>
                        </div>

                        <!-- Ya revisado -->
                        <div v-else class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Revisado por</p>
                                <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ doc.revisado_por ?? '—' }}</p>
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ doc.reviewed_at_raw ?? doc.reviewed_at ?? '—' }}</p>
                            </div>
                            <div v-if="doc.observacion">
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Observación</p>
                                <p class="text-sm text-[var(--sispaa-text)]">{{ doc.observacion }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vacío -->
                <div v-else class="rounded-2xl border border-dashed p-10 text-center bg-[var(--sispaa-surface)]">
                    <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                        <Users class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                    </div>
                    <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">Este estudiante aún no ha subido documentos.</p>
                    <p class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">Cuando suba archivos desde su portal, aparecerán aquí para revisión.</p>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
