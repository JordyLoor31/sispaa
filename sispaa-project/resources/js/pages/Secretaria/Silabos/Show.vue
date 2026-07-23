<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, XCircle, BookOpen, ArrowUpRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { SilaboItem } from './types';

const props = defineProps<{
    silabo: SilaboItem;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        aprobado: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        subido: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        pendiente: 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]',
    };
    return map[estado] ?? map.pendiente;
};

const reviewForm = useForm({ accion: '' as '' | 'aprobar' | 'rechazar', observaciones: '' });

const submitReview = (accion: 'aprobar' | 'rechazar') => {
    reviewForm.accion = accion;
    if (accion === 'rechazar' && reviewForm.observaciones.trim().length < 5) {
        reviewForm.setError('observaciones', 'Debes indicar el motivo del rechazo (mínimo 5 caracteres).');
        return;
    }
    reviewForm.patch(route('coordinador.silabos.review', props.silabo.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(accion === 'aprobar' ? 'Sílabo aprobado.' : 'Sílabo rechazado.'),
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${silabo.materia} — ${silabo.docente.name}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <BookOpen class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ silabo.materia }}</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                            {{ silabo.carrera }} — {{ silabo.periodo }} · {{ silabo.docente.name }}
                        </p>
                    </div>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('coordinador.silabos.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-4 sm:gap-6 sm:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(silabo.estado)]">
                        {{ silabo.estado.charAt(0).toUpperCase() + silabo.estado.slice(1) }}
                    </span>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Docente</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">{{ silabo.docente.name }}</p>
                    <p class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ silabo.docente.email }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-2">Archivo</h4>
                <p v-if="silabo.fecha_subida" class="text-xs opacity-50 text-[var(--sispaa-text)] mb-2">Subido: {{ silabo.fecha_subida }}</p>
                <a v-if="silabo.archivo_url" :href="silabo.archivo_url" target="_blank"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80 rounded-lg px-2.5 py-1.5 border border-[color:color-mix(in_srgb,var(--sispaa-primary)_35%,transparent)]">
                    <ArrowUpRight class="h-3.5 w-3.5" /> Ver sílabo
                </a>
                <p v-else class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin archivo adjunto.</p>
            </div>

            <!-- Revisión: solo si no está aprobado -->
            <div v-if="silabo.estado !== 'aprobado'" class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Revisar sílabo</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Observaciones</label>
                        <textarea
                            v-model="reviewForm.observaciones"
                            rows="3"
                            placeholder="Escribe una observación para el docente (obligatoria si vas a rechazar)..."
                            class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]"
                        ></textarea>
                        <p v-if="reviewForm.errors.observaciones" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.observaciones }}</p>
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

            <!-- Ya aprobado -->
            <div v-else-if="silabo.observaciones" class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-2">Observaciones</h4>
                <p class="text-sm text-[var(--sispaa-text)]">{{ silabo.observaciones }}</p>
            </div>
        </div>
    </AppSidebarLayout>
</template>
