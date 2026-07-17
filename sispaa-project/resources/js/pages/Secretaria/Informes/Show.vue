<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, CheckCircle2, XCircle, FileText, ArrowUpRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { InformeItem } from './types';

const props = defineProps<{
    informe: InformeItem;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        aprobado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        subido: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        pendiente: 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500',
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
    reviewForm.patch(route('secretaria.informes.review', props.informe.id), {
        preserveScroll: true,
        onSuccess: () => toast.success(accion === 'aprobar' ? 'Informe aprobado.' : 'Informe rechazado.'),
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`${informe.materia} — ${informe.docente.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <FileText class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ informe.materia }}</h1>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                            {{ informe.carrera }} — {{ informe.periodo }} · {{ informe.docente.name }}
                        </p>
                    </div>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('secretaria.informes.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <span :class="['mt-2 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(informe.estado)]">
                        {{ informe.estado.charAt(0).toUpperCase() + informe.estado.slice(1) }}
                    </span>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Docente</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ informe.docente.name }}</p>
                    <p class="text-xs text-slate-400">{{ informe.docente.email }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Archivo</h4>
                <p v-if="informe.fecha_subida" class="text-xs text-slate-400 mb-2">Subido: {{ informe.fecha_subida }}</p>
                <a v-if="informe.ver_url" :href="informe.ver_url" target="_blank"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg px-2.5 py-1.5">
                    <ArrowUpRight class="h-3.5 w-3.5" /> Ver informe
                </a>
                <p v-else class="text-sm text-slate-400">Sin archivo adjunto.</p>
            </div>

            <!-- Revisión: solo si no está aprobado -->
            <div v-if="informe.estado !== 'aprobado'" class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Revisar informe</h4>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Observaciones</label>
                        <textarea
                            v-model="reviewForm.observaciones"
                            rows="3"
                            placeholder="Escribe una observación para el docente (obligatoria si vas a rechazar)..."
                            class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                        ></textarea>
                        <p v-if="reviewForm.errors.observaciones" class="text-xs text-rose-500 mt-1">{{ reviewForm.errors.observaciones }}</p>
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

            <!-- Ya aprobado -->
            <div v-else-if="informe.observaciones" class="max-w-2xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Observaciones</h4>
                <p class="text-sm text-slate-700 dark:text-slate-300">{{ informe.observaciones }}</p>
            </div>
        </div>
    </AppSidebarLayout>
</template>
