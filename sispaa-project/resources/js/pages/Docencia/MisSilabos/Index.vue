<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { BookOpen, CheckCircle2, Clock, UploadCloud, X, ArrowUpRight } from 'lucide-vue-next';
import { toast } from 'vue-sonner';

interface SilaboItem {
    materia_id: number;
    periodo_id: number;
    materia: string;
    carrera: string | null;
    periodo: string;
    grupo: string | null;
    estado: 'pendiente' | 'subido' | 'aprobado' | string;
    archivo_url: string | null;
    ver_url: string | null;
    observaciones: string | null;
    fecha_subida: string | null;
    subido_por: string | null;
}

const props = defineProps<{
    silabos: SilaboItem[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const totalSilabos = computed(() => props.silabos.length);
const silabosAprobados = computed(() => props.silabos.filter((s) => s.estado === 'aprobado').length);
const silabosFaltantes = computed(() => totalSilabos.value - silabosAprobados.value);
const porcentajeAvance = computed(() => (totalSilabos.value === 0 ? 0 : Math.round((silabosAprobados.value / totalSilabos.value) * 100)));

const activeItem = ref<SilaboItem | null>(null);

const form = useForm({
    materia_id: 0,
    periodo_id: 0,
    archivo: null as File | null,
});

const openUpload = (item: SilaboItem) => {
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

    form.post(route('docencia.mis-silabos.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Sílabo subido correctamente.');
            closeUpload();
        },
        onError: () => toast.error('No se pudo subir el sílabo.'),
    });
};

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        aprobado: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        subido: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        pendiente: 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]',
    };
    return map[estado] ?? map.pendiente;
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Mis Sílabos" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Mis Sílabos</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Sube el sílabo de cada materia que tienes asignada en el período activo.
                </p>
            </div>

            <!-- Progreso de sílabos aprobados -->
            <div v-if="totalSilabos > 0" class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-semibold opacity-70 text-[var(--sispaa-text)]">
                        {{ silabosAprobados }} de {{ totalSilabos }} sílabos aprobados
                    </span>
                    <span class="font-bold text-[var(--sispaa-primary)]">{{ porcentajeAvance }}%</span>
                </div>
                <Progress :model-value="porcentajeAvance" class="mt-2 h-2" />
                <p v-if="silabosFaltantes > 0" class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">
                    Te falta{{ silabosFaltantes === 1 ? '' : 'n' }} {{ silabosFaltantes }} sílabo{{ silabosFaltantes === 1 ? '' : 's' }} por subir o aprobar.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div v-for="item in silabos" :key="item.materia_id + '-' + item.periodo_id"
                    class="flex flex-col gap-3 rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                            <BookOpen class="h-4.5 w-4.5" />
                        </div>
                        <span :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(item.estado)]">
                            <CheckCircle2 v-if="item.estado === 'aprobado'" class="h-3.5 w-3.5" />
                            <Clock v-else class="h-3.5 w-3.5" />
                            {{ item.estado.charAt(0).toUpperCase() + item.estado.slice(1) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ item.materia }}</h3>
                        <p class="mt-0.5 text-xs opacity-60 text-[var(--sispaa-text)]">{{ item.carrera }} — {{ item.periodo }}<span v-if="item.grupo"> · Grupo {{ item.grupo }}</span></p>
                        <p class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">{{ item.fecha_subida ? 'Subido: ' + item.fecha_subida : 'Aún no subido' }}</p>
                        <p v-if="item.subido_por" class="mt-1 text-xs text-[var(--sispaa-primary)]">
                            Subido por: {{ item.subido_por }} (otro paralelo)
                        </p>
                        <div v-if="item.estado !== 'aprobado' && item.observaciones" class="mt-2 border-l-2 border-rose-500 pl-2 text-xs font-medium text-rose-600">
                            Observación: {{ item.observaciones }}
                        </div>
                    </div>
                    <div class="flex items-center gap-2 border-t pt-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <a v-if="item.ver_url" :href="item.ver_url" target="_blank"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg opacity-70 text-[var(--sispaa-text)] hover:opacity-100 border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <ArrowUpRight class="h-4 w-4" />
                        </a>
                        <button v-if="item.estado !== 'aprobado'" @click="openUpload(item)"
                            class="inline-flex h-9 items-center justify-center rounded-lg px-4 text-xs font-semibold text-white transition-colors bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ item.archivo_url ? 'Reemplazar' : 'Subir sílabo' }}
                        </button>
                    </div>
                </div>

                <div v-if="silabos.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                    No tienes materias asignadas en el período activo.
                </div>
            </div>
        </div>

        <!-- Modal de carga -->
        <div v-if="activeItem" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
            <div class="w-full max-w-lg rounded-2xl p-6 shadow-xl bg-[var(--sispaa-background)]">
                <div class="flex items-center justify-between border-b pb-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    <h3 class="text-lg font-bold text-[var(--sispaa-text)]">Subir sílabo — {{ activeItem.materia }}</h3>
                    <button @click="closeUpload" class="rounded-lg p-1 opacity-50 hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <label class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center transition-colors border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] hover:border-[color:color-mix(in_srgb,var(--sispaa-primary)_50%,transparent)]">
                        <input type="file" class="hidden" accept=".pdf,.doc,.docx" @change="onFileSelect" />
                        <UploadCloud class="h-10 w-10 mb-2 text-[var(--sispaa-primary)]" />
                        <p class="text-sm font-semibold text-[var(--sispaa-text)]">Haz clic para seleccionar el archivo</p>
                        <p class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">PDF, DOC o DOCX — máximo 10MB</p>
                    </label>

                    <div v-if="form.archivo" class="flex items-center justify-between rounded-lg p-3 text-xs bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                        <span class="truncate font-semibold text-[var(--sispaa-text)]">{{ form.archivo.name }}</span>
                        <span class="opacity-50 text-[var(--sispaa-text)]">{{ (form.archivo.size / 1024 / 1024).toFixed(2) }} MB</span>
                    </div>
                    <p v-if="form.errors.archivo" class="text-xs text-rose-500">{{ form.errors.archivo }}</p>

                    <div class="flex items-center justify-end gap-3 border-t pt-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <button type="button" @click="closeUpload" class="rounded-lg px-4 py-2 text-sm font-semibold text-[var(--sispaa-text)] opacity-70 hover:opacity-100 border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            Cancelar
                        </button>
                        <button type="submit" :disabled="form.processing || !form.archivo" class="rounded-lg px-4 py-2 text-sm font-semibold text-white disabled:opacity-50 bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ form.processing ? 'Subiendo...' : 'Confirmar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
