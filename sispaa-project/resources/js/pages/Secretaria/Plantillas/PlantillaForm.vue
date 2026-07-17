<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { FileText, UploadCloud, X } from 'lucide-vue-next';
import type { PlantillaItem } from './types';

const props = defineProps<{
    plantilla?: PlantillaItem | null;
}>();

const isEditing = !!props.plantilla;

const form = useForm({
    nombre_doc: props.plantilla?.nombre_doc ?? '',
    archivo: null as File | null,
});

const onFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.archivo = target.files[0];
        form.clearErrors('archivo');
    }
};

const removeFile = () => {
    form.archivo = null;
};

const submit = () => {
    if (!isEditing && !form.archivo) {
        form.setError('archivo', 'Selecciona un archivo.');
        return;
    }

    const options = { preserveScroll: true };

    // Los archivos se suben siempre por POST (multipart); para editar se
    // spoofea el método PUT vía _method, que es lo que recomienda Inertia
    // para file uploads en updates (PHP no puebla $_FILES en PUT/PATCH).
    if (isEditing && props.plantilla) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(route('secretaria.plantillas.update', props.plantilla!.id), options);
    } else {
        form.post(route('secretaria.plantillas.store'), options);
    }
};
</script>

<template>
    <form class="space-y-5" @submit.prevent="submit">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre del documento *</label>
            <input
                v-model="form.nombre_doc"
                type="text"
                placeholder="Ej. Solicitud de certificado de matrícula"
                class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
            />
            <p v-if="form.errors.nombre_doc" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre_doc }}</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                Archivo {{ isEditing ? '(deja vacío para conservar el actual)' : '*' }}
            </label>

            <div v-if="isEditing && plantilla && !form.archivo" class="mb-2 flex items-center gap-1.5 text-xs text-slate-500">
                <FileText class="h-3.5 w-3.5" />
                <a :href="plantilla.ver_url" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-500 dark:text-indigo-400">
                    Ver archivo actual
                </a>
            </div>

            <label
                class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 hover:border-indigo-400 dark:border-slate-800 p-6 text-center cursor-pointer transition-colors"
            >
                <input type="file" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx" @change="onFileSelect" />
                <UploadCloud class="h-8 w-8 text-indigo-500 mb-1.5" />
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Haz clic para seleccionar el archivo</p>
                <p class="text-xs text-slate-400 mt-1">PDF, DOC, DOCX, XLS o XLSX — máximo 10MB</p>
            </label>

            <div
                v-if="form.archivo"
                class="mt-2 flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800/80 text-xs"
            >
                <span class="font-semibold text-slate-700 dark:text-slate-300 truncate">{{ form.archivo.name }}</span>
                <button type="button" @click="removeFile" class="rounded p-1 hover:bg-slate-200 dark:hover:bg-slate-800 text-slate-400">
                    <X class="h-4 w-4" />
                </button>
            </div>
            <p v-if="form.errors.archivo" class="text-xs text-rose-500 mt-1">{{ form.errors.archivo }}</p>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ form.processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Crear plantilla' }}
            </Button>
        </div>
    </form>
</template>
