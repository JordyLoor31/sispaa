<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Progress } from '@/components/ui/progress';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { FileText, CheckCircle2, AlertCircle, Clock, UploadCloud, X, ArrowUpRight } from 'lucide-vue-next';

interface DocumentoExpediente {
    tipo: string;
    subido: boolean;
    archivo_url: string | null;
    estado: 'pendiente' | 'aprobado' | 'rechazada' | 'no_subido' | string;
    observacion: string | null;
    updated_at: string | null;
}

const props = defineProps<{
    expediente: DocumentoExpediente[];
}>();

const totalDocumentos = computed(() => props.expediente.length);
const documentosAprobados = computed(() => props.expediente.filter((d) => d.estado === 'aprobado').length);
const documentosFaltantes = computed(() => totalDocumentos.value - documentosAprobados.value);
const porcentajeAvance = computed(() => (totalDocumentos.value === 0 ? 0 : Math.round((documentosAprobados.value / totalDocumentos.value) * 100)));

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Mis Documentos',
        href: '/estudiante/documentos',
    },
];

// Modal & Form State
const activeDocType = ref<string | null>(null);
const isDragActive = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

const form = useForm({
    tipo_documento: '',
    archivo: null as File | null,
});

const openUploadModal = (docType: string) => {
    activeDocType.value = docType;
    form.tipo_documento = docType;
    form.archivo = null;
    form.clearErrors();
};

const closeUploadModal = () => {
    activeDocType.value = null;
    form.tipo_documento = '';
    form.archivo = null;
};

// Drag and drop handlers
const onDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragActive.value = true;
};

const onDragLeave = () => {
    isDragActive.value = false;
};

const onDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragActive.value = false;
    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        handleFile(e.dataTransfer.files[0]);
    }
};

const onFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        handleFile(target.files[0]);
    }
};

const handleFile = (file: File) => {
    // Validate size (max 5MB)
    if (file.size > 5 * 1024 * 1024) {
        form.setError('archivo', 'El archivo no debe pesar más de 5MB.');
        return;
    }
    // Validate type (pdf, images)
    const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        form.setError('archivo', 'Tipo de archivo no permitido. Solo se aceptan PDFs e imágenes (JPG, PNG).');
        return;
    }

    form.archivo = file;
    form.clearErrors('archivo');
};

const removeSelectedFile = () => {
    form.archivo = null;
};

const submitUpload = () => {
    if (!form.archivo) {
        form.setError('archivo', 'Debes seleccionar o arrastrar un archivo.');
        return;
    }

    form.post(route('student.documentos.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            closeUploadModal();
        },
    });
};
</script>

<template>
    <Head title="Expediente SGA" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Mis Documentos (Expediente SGA)
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Carga y gestiona tus documentos habilitantes de matrícula para validación por parte de Secretaría.
                </p>
            </div>

            <!-- Dashboard de Avance SGA -->
            <div class="grid gap-4 sm:gap-6 md:grid-cols-4">
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 md:col-span-3 bg-[var(--sispaa-surface)]">
                    <h3 class="text-base font-bold text-[var(--sispaa-text)]">Estado General de Validación</h3>
                    <p class="mt-0.5 text-xs opacity-60 text-[var(--sispaa-text)]">Se requiere la aprobación del 100% de los documentos para culminar tu expediente académico.</p>

                    <!-- Progreso de documentos aprobados -->
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold opacity-80 text-[var(--sispaa-text)]">
                                {{ documentosAprobados }} de {{ totalDocumentos }} documentos aprobados
                            </span>
                            <span class="font-bold text-[var(--sispaa-primary)]">{{ porcentajeAvance }}%</span>
                        </div>
                        <Progress :model-value="porcentajeAvance" class="mt-2 h-2" />
                        <p v-if="documentosFaltantes > 0" class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">
                            Te falta{{ documentosFaltantes === 1 ? '' : 'n' }} {{ documentosFaltantes }} documento{{ documentosFaltantes === 1 ? '' : 's' }} por aprobar.
                        </p>
                    </div>

                    <!-- Lista de Documentos Obligatorios -->
                    <div class="mt-6 space-y-4">
                        <div v-for="doc in expediente" :key="doc.tipo" class="flex flex-col justify-between gap-4 rounded-xl border p-4 transition-shadow hover:shadow-sm md:flex-row md:items-center bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[var(--sispaa-primary)]">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-[var(--sispaa-text)]">{{ doc.tipo }}</h4>
                                    <p class="mt-0.5 text-xs opacity-50 text-[var(--sispaa-text)]">
                                        {{ doc.subido ? 'Cargado: ' + doc.updated_at : 'Pendiente de carga' }}
                                    </p>
                                    <!-- Observaciones si es rechazado -->
                                    <div v-if="doc.estado === 'rechazado' && doc.observacion" class="mt-2 border-l-2 border-rose-500 pl-2 text-xs font-medium text-rose-600">
                                        Observación: {{ doc.observacion }}
                                    </div>
                                </div>
                            </div>

                            <!-- Estado Badges y Acción -->
                            <div class="flex flex-wrap items-center gap-3 self-end md:self-center">
                                <!-- Badge de Estado -->
                                <span v-if="doc.estado === 'aprobado'" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    Aprobado
                                </span>
                                <span v-else-if="doc.estado === 'pendiente'" class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]">
                                    <Clock class="h-3.5 w-3.5 animate-pulse" />
                                    Pendiente
                                </span>
                                <span v-else-if="doc.estado === 'rechazado'" class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800">
                                    <AlertCircle class="h-3.5 w-3.5" />
                                    Rechazado
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_55%,transparent)]">
                                    Sin cargar
                                </span>

                                <!-- Ver Archivo -->
                                <a v-if="doc.subido && doc.archivo_url" :href="doc.archivo_url" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border transition-colors text-[var(--sispaa-text)] opacity-70 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]" title="Ver documento subido">
                                    <ArrowUpRight class="h-4 w-4" />
                                </a>

                                <!-- Subir/Reemplazar botón -->
                                <button v-if="doc.estado !== 'aprobado'" @click="openUploadModal(doc.tipo)" class="inline-flex h-9 items-center justify-center rounded-lg px-4 text-xs font-semibold text-white transition-colors bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                                    {{ doc.subido ? 'Reemplazar' : 'Cargar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info panel -->
                <div class="space-y-4 rounded-2xl p-5 text-xs opacity-70 shadow-sm sm:p-6 bg-[var(--sispaa-surface)] text-[var(--sispaa-text)]">
                    <h3 class="text-sm font-bold opacity-100 text-[var(--sispaa-text)]">Instrucciones de Carga</h3>
                    <p>1. Los archivos deben cargarse en formato **PDF** o imágenes claras (**JPG, PNG**).</p>
                    <p>2. El tamaño máximo admitido es de **5MB** por archivo.</p>
                    <p>3. Si tu documento es **rechazado**, revisa la observación del evaluador, corrige el archivo y cárgalo de nuevo usando el botón "Reemplazar".</p>
                    <p>4. El estado cambiará a **Aprobado** una vez sea validado por Secretaría de Carrera.</p>
                </div>
            </div>

            <!-- Upload Modal (Drag & Drop) -->
            <div v-if="activeDocType" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
                <div class="animate-in fade-in zoom-in w-full max-w-lg rounded-2xl border p-6 shadow-xl duration-200 bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div class="flex items-center justify-between border-b pb-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <h3 class="text-lg font-bold text-[var(--sispaa-text)]">Cargar {{ activeDocType }}</h3>
                        <button @click="closeUploadModal" class="rounded-lg p-1 opacity-50 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <X class="h-5 w-5" />
                        </button>
                    </div>

                    <form @submit.prevent="submitUpload" class="mt-6 space-y-4">
                        <!-- Drag & Drop Zone -->
                        <div
                            @dragover="onDragOver"
                            @dragleave="onDragLeave"
                            @drop="onDrop"
                            @click="fileInputRef?.click()"
                            class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center transition-all"
                            :class="[
                                isDragActive
                                    ? 'border-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_8%,transparent)]'
                                    : 'border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] hover:border-[var(--sispaa-primary)]'
                            ]"
                        >
                            <input ref="fileInputRef" type="file" class="hidden" accept=".pdf,image/*" @change="onFileSelect" />
                            <UploadCloud class="mb-2 h-10 w-10 text-[var(--sispaa-primary)]" />
                            <p class="text-sm font-semibold opacity-80 text-[var(--sispaa-text)]">Arrastra tu archivo aquí</p>
                            <p class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">O haz clic para explorar en tu equipo (PDF, JPG, PNG)</p>
                            <p class="text-xxs mt-2 opacity-50 text-[var(--sispaa-text)]">Peso máximo sugerido: 5MB</p>
                        </div>

                        <!-- Error Messages -->
                        <div v-if="form.errors.archivo" class="text-xs font-medium text-rose-600">
                            {{ form.errors.archivo }}
                        </div>

                        <!-- Selected File Preview -->
                        <div v-if="form.archivo" class="flex items-center justify-between rounded-lg border p-3 bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <FileText class="h-5 w-5 shrink-0 text-[var(--sispaa-primary)]" />
                                <div class="truncate text-left text-xs">
                                    <p class="truncate font-semibold opacity-80 text-[var(--sispaa-text)]">{{ form.archivo.name }}</p>
                                    <p class="opacity-50 text-[var(--sispaa-text)]">{{ (form.archivo.size / 1024 / 1024).toFixed(2) }} MB</p>
                                </div>
                            </div>
                            <button type="button" @click="removeSelectedFile" class="rounded p-1 opacity-50 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Form actions -->
                        <div class="flex items-center justify-end gap-3 border-t pt-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <button type="button" @click="closeUploadModal" class="rounded-lg border px-4 py-2 text-sm font-semibold text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_5%,transparent)]">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing || !form.archivo" class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition-all disabled:opacity-50 bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                                {{ form.processing ? 'Subiendo...' : 'Confirmar Carga' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
