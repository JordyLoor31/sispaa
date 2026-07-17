<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { FileText, Download } from 'lucide-vue-next';

interface PlantillaItem {
    id: number;
    nombre_doc: string;
    descargar_url: string;
}

defineProps<{
    plantillas: PlantillaItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Plantillas',
        href: '/estudiante/plantillas',
    },
];
</script>

<template>
    <Head title="Plantillas de Documentos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Plantillas de Documentos</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Formatos institucionales publicados por Secretaría. Descarga el que necesites, complétalo y súbelo donde corresponda.
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <a
                    v-for="p in plantillas"
                    :key="p.id"
                    :href="p.descargar_url"
                    target="_blank"
                    class="flex items-center gap-3 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all dark:border-slate-800 dark:bg-slate-950"
                >
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <FileText class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ p.nombre_doc }}</h3>
                        <p class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5 inline-flex items-center gap-1">
                            <Download class="h-3.5 w-3.5" /> Descargar
                        </p>
                    </div>
                </a>

                <div v-if="plantillas.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    Secretaría aún no ha publicado plantillas de documentos.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
