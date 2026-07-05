<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, CheckCircle2, AlertCircle, Clock, UploadCloud, X, ArrowUpRight } from 'lucide-vue-next';

interface DocumentoExpediente {
    tipo: string;
    subido: boolean;
    archivo_url: string | null;
    estado: 'pendiente' | 'aprobado' | 'rechazada' | 'no_subido' | string;
    observacion: string | null;
    updated_at: string | null;
}

defineProps<{
    expediente: DocumentoExpediente[];
}>();

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
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Mis Documentos (Expediente SGA)
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Carga y gestiona tus documentos habilitantes de matrícula para validación por parte de Secretaría.
                </p>
            </div>

            <!-- Dashboard de Avance SGA -->
            <div class="grid gap-6 md:grid-cols-4">
                <div class="md:col-span-3 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h3 class="text-base font-bold text-slate-900 dark:text-white">Estado General de Validación</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Se requiere la aprobación del 100% de los documentos para culminar tu expediente académico.</p>

                    <!-- Lista de Documentos Obligatorios -->
                    <div class="mt-6 space-y-4">
                        <div v-for="doc in expediente" :key="doc.tipo" class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-slate-100 dark:border-slate-800/80 bg-slate-50/30 dark:bg-slate-950 hover:shadow-sm transition-shadow gap-4">
                            <div class="flex items-start gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                                    <FileText class="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">{{ doc.tipo }}</h4>
                                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                        {{ doc.subido ? 'Cargado: ' + doc.updated_at : 'Pendiente de carga' }}
                                    </p>
                                    <!-- Observaciones si es rechazado -->
                                    <div v-if="doc.estado === 'rechazado' && doc.observacion" class="mt-2 text-xs border-l-2 border-rose-500 pl-2 text-rose-600 dark:text-rose-400 font-medium">
                                        Observación: {{ doc.observacion }}
                                    </div>
                                </div>
                            </div>

                            <!-- Estado Badges y Acción -->
                            <div class="flex items-center gap-3 self-end md:self-center">
                                <!-- Badge de Estado -->
                                <span v-if="doc.estado === 'aprobado'" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400">
                                    <CheckCircle2 class="h-3.5 w-3.5" />
                                    Aprobado
                                </span>
                                <span v-else-if="doc.estado === 'pendiente'" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-400">
                                    <Clock class="h-3.5 w-3.5 animate-pulse" />
                                    Pendiente
                                </span>
                                <span v-else-if="doc.estado === 'rechazado'" class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-800 dark:bg-rose-950/30 dark:text-rose-400">
                                    <AlertCircle class="h-3.5 w-3.5" />
                                    Rechazado
                                </span>
                                <span v-else class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-500 dark:bg-slate-900 dark:text-slate-500">
                                    Sin cargar
                                </span>

                                <!-- Ver Archivo -->
                                <a v-if="doc.subido && doc.archivo_url" :href="doc.archivo_url" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-500 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-900 transition-colors" title="Ver documento subido">
                                    <ArrowUpRight class="h-4 w-4" />
                                </a>

                                <!-- Subir/Reemplazar botón -->
                                <button v-if="doc.estado !== 'aprobado'" @click="openUploadModal(doc.tipo)" class="inline-flex h-9 items-center justify-center rounded-lg bg-indigo-600 px-4 text-xs font-semibold text-white hover:bg-indigo-500 transition-colors">
                                    {{ doc.subido ? 'Reemplazar' : 'Cargar' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info panel -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950 text-xs text-slate-500 space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Instrucciones de Carga</h3>
                    <p>1. Los archivos deben cargarse en formato **PDF** o imágenes claras (**JPG, PNG**).</p>
                    <p>2. El tamaño máximo admitido es de **5MB** por archivo.</p>
                    <p>3. Si tu documento es **rechazado**, revisa la observación del evaluador, corrige el archivo y cárgalo de nuevo usando el botón "Reemplazar".</p>
                    <p>4. El estado cambiará a **Aprobado** una vez sea validado por Secretaría de Carrera.</p>
                </div>
            </div>

            <!-- Upload Modal (Drag & Drop) -->
            <div v-if="activeDocType" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
                <div class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl border border-slate-100 dark:border-slate-900 animate-in fade-in zoom-in duration-200">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/80">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Cargar {{ activeDocType }}</h3>
                        <button @click="closeUploadModal" class="rounded-lg p-1 hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-400">
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
                            class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed p-8 text-center cursor-pointer transition-all"
                            :class="[
                                isDragActive 
                                    ? 'border-indigo-500 bg-indigo-50/30 dark:border-indigo-400 dark:bg-indigo-950/10' 
                                    : 'border-slate-300 hover:border-indigo-400 dark:border-slate-800 dark:hover:border-indigo-500'
                            ]"
                        >
                            <input ref="fileInputRef" type="file" class="hidden" accept=".pdf,image/*" @change="onFileSelect" />
                            <UploadCloud class="h-10 w-10 text-indigo-500 dark:text-indigo-400 mb-2" />
                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Arrastra tu archivo aquí</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">O haz clic para explorar en tu equipo (PDF, JPG, PNG)</p>
                            <p class="text-xxs text-slate-400 dark:text-slate-500 mt-2">Peso máximo sugerido: 5MB</p>
                        </div>

                        <!-- Error Messages -->
                        <div v-if="form.errors.archivo" class="text-xs text-rose-600 dark:text-rose-400 font-medium">
                            {{ form.errors.archivo }}
                        </div>

                        <!-- Selected File Preview -->
                        <div v-if="form.archivo" class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <FileText class="h-5 w-5 text-indigo-500 shrink-0" />
                                <div class="text-xs text-left truncate">
                                    <p class="font-semibold text-slate-800 dark:text-slate-300 truncate">{{ form.archivo.name }}</p>
                                    <p class="text-slate-400">{{ (form.archivo.size / 1024 / 1024).toFixed(2) }} MB</p>
                                </div>
                            </div>
                            <button type="button" @click="removeSelectedFile" class="rounded p-1 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400">
                                <X class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Form actions -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800/80">
                            <button type="button" @click="closeUploadModal" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-900">
                                Cancelar
                            </button>
                            <button type="submit" :disabled="form.processing || !form.archivo" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50 transition-all">
                                {{ form.processing ? 'Subiendo...' : 'Confirmar Carga' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
