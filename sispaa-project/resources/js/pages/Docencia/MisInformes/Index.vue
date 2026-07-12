<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, CheckCircle2, Clock, UploadCloud, X, ArrowUpRight } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface InformeItem {
    materia_id: number;
    periodo_id: number;
    materia: string;
    carrera: string | null;
    periodo: string;
    grupo: string | null;
    estado: 'pendiente' | 'subido' | 'aprobado' | string;
    archivo_url: string | null;
    fecha_subida: string | null;
}

defineProps<{
    informes: InformeItem[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const activeItem = ref<InformeItem | null>(null);

const form = useForm({
    materia_id: 0,
    periodo_id: 0,
    archivo: null as File | null,
});

const openUpload = (item: InformeItem) => {
    activeItem.value = item;
    form.materia_id = item.materia_id;
    form.periodo_id = item.periodo_id;
    form.archivo = null;
    form.clearErrors();
};

const closeUpload = () => {
    activeItem.value = null;
    form.reset();
};

const onFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.archivo = target.files[0];
        form.clearErrors('archivo');
    }
};

const submit = () => {
    if (!form.archivo) {
        form.setError('archivo', 'Selecciona un archivo.');
        return;
    }

    form.post(route('docencia.mis-informes.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Informe subido correctamente.');
            closeUpload();
        },
        onError: () => toast.error('No se pudo subir el informe.'),
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        aprobado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        subido: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        pendiente: 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500',
    };
    return map[estado] ?? map.pendiente;
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Mis Informes de Asignatura" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Mis Informes de Asignatura</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Sube el informe de cada materia que tienes asignada en el período activo.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="item in informes" :key="item.materia_id + '-' + item.periodo_id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <FileText class="h-4.5 w-4.5" />
                        </div>
                        <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(item.estado)]">
                            <CheckCircle2 v-if="item.estado === 'aprobado'" class="h-3.5 w-3.5" />
                            <Clock v-else class="h-3.5 w-3.5" />
                            {{ item.estado.charAt(0).toUpperCase() + item.estado.slice(1) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ item.materia }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ item.carrera }} — {{ item.periodo }}<span v-if="item.grupo"> · Grupo {{ item.grupo }}</span></p>
                        <p class="text-xs text-slate-400 mt-1">{{ item.fecha_subida ? 'Subido: ' + item.fecha_subida : 'Aún no subido' }}</p>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <a v-if="item.archivo_url" :href="item.archivo_url" target="_blank"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-500 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-900">
                            <ArrowUpRight class="h-4 w-4" />
                        </a>
                        <button v-if="item.estado !== 'aprobado'" @click="openUpload(item)"
                            class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-500 transition-colors">
                            {{ item.archivo_url ? 'Reemplazar' : 'Subir informe' }}
                        </button>
                    </div>
                </div>

                <div v-if="informes.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No tienes materias asignadas en el período activo.
                </div>
            </div>
        </div>

        <!-- Modal de carga -->
        <div v-if="activeItem" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl border border-slate-100 dark:border-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/80">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Subir informe — {{ activeItem.materia }}</h3>
                    <button @click="closeUpload" class="rounded-lg p-1 hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 hover:border-indigo-400 dark:border-slate-800 p-8 text-center cursor-pointer transition-colors">
                        <input type="file" class="hidden" accept=".pdf,.doc,.docx" @change="onFileSelect" />
                        <UploadCloud class="h-10 w-10 text-indigo-500 mb-2" />
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Haz clic para seleccionar el archivo</p>
                        <p class="text-xs text-slate-400 mt-1">PDF, DOC o DOCX — máximo 10MB</p>
                    </label>

                    <div v-if="form.archivo" class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 text-xs">
                        <span class="font-semibold text-slate-700 dark:text-slate-300 truncate">{{ form.archivo.name }}</span>
                        <span class="text-slate-400">{{ (form.archivo.size / 1024 / 1024).toFixed(2) }} MB</span>
                    </div>
                    <p v-if="form.errors.archivo" class="text-xs text-rose-500">{{ form.errors.archivo }}</p>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                        <button type="button" @click="closeUpload" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-900">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing || !form.archivo" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
                            {{ form.processing ? 'Subiendo...' : 'Confirmar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
