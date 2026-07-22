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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Plantillas de Documentos</h1>
                <p class="mt-1 text-sm opacity-70 text-[var(--sispaa-text)]">
                    Formatos institucionales publicados por Secretaría. Descarga el que necesites, complétalo y súbelo donde corresponda.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <a
                    v-for="p in plantillas"
                    :key="p.id"
                    :href="p.descargar_url"
                    target="_blank"
                    class="flex items-center gap-3 rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)] hover:shadow-md transition-all"
                >
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <FileText class="h-5 w-5" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold truncate text-[var(--sispaa-text)]">{{ p.nombre_doc }}</h3>
                        <p class="text-xs mt-0.5 inline-flex items-center gap-1 text-[var(--sispaa-primary)]">
                            <Download class="h-3.5 w-3.5" /> Descargar
                        </p>
                    </div>
                </a>

                <div v-if="plantillas.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                    Secretaría aún no ha publicado plantillas de documentos.
                </div>
            </div>
        </div>
    </AppLayout>
</template>
