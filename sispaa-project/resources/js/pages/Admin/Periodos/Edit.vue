<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { FlagTriangleRight, PlayCircle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useSubmitToast } from '@/composables/useSubmitToast';
import PeriodoForm from './Form.vue';
import type { Periodo } from './columns';

const props = defineProps<{
    periodo: Periodo;
    breadcrumbs?: BreadcrumbItemType[];
}>();

// Acción rápida según el estado: activar cuando está planificado, desactivar
// cuando está activo (equivalente al antiguo "Finalizar", mejor nombrado).
const activar = () => {
    const { withToast } = useSubmitToast(
        'Activando periodo...',
        (errors) => errors.estado ?? 'No se pudo activar el periodo.',
    );
    router.post(route('admin.periodos.activar', props.periodo.id), {}, withToast({ preserveScroll: true }));
};

const desactivar = () => {
    const { withToast } = useSubmitToast(
        'Desactivando periodo...',
        (errors) => errors.estado ?? 'No se pudo desactivar el periodo.',
    );
    router.post(route('admin.periodos.finalizar', props.periodo.id), {}, withToast({ preserveScroll: true }));
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.periodo.nombre}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Editar Periodo
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        {{ props.periodo.nombre }}
                    </p>
                </div>
                <Button v-if="periodo.estado === 'planificado'" @click="activar" class="text-white bg-[var(--sispaa-secondary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)]">
                    <PlayCircle class="h-4 w-4 mr-1.5" /> Activar Periodo
                </Button>
                <Button v-else-if="periodo.estado === 'activo'" @click="desactivar" variant="outline" class="text-rose-500 border-rose-200 hover:bg-rose-50">
                    <FlagTriangleRight class="h-4 w-4 mr-1.5" /> Desactivar Periodo
                </Button>
            </div>

            <div class="w-full max-w-5xl mx-auto rounded-2xl p-4 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <PeriodoForm :periodo="periodo" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
