<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Files } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import PlantillaForm from './PlantillaForm.vue';
import type { PlantillaItem } from './types';

defineProps<{
    plantilla: PlantillaItem;
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Editar Plantilla" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="mx-auto w-full max-w-xl">
                <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <!-- Cabecera de la tarjeta -->
                    <div class="flex items-center gap-3.5 border-b p-5 sm:p-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                            <Files class="h-5 w-5" />
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-lg font-bold tracking-tight text-[var(--sispaa-text)] sm:text-xl">Editar Plantilla</h1>
                            <p class="mt-0.5 truncate text-sm opacity-60 text-[var(--sispaa-text)]">{{ plantilla.nombre_doc }}</p>
                        </div>
                    </div>

                    <div class="p-5 sm:p-6">
                        <PlantillaForm :plantilla="plantilla" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
