<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { FlagTriangleRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import PeriodoForm from './Form.vue';
import type { Periodo } from './columns';

const props = defineProps<{
    periodo: Periodo;
    breadcrumbs?: BreadcrumbItemType[];
}>();

// Acción rápida para finalizar sin pasar por el combobox de Estado + Guardar
// (antes vivía como botón en la tabla del listado; ahora solo aquí).
const finalizar = () => {
    router.post(route('admin.periodos.finalizar', props.periodo.id), {}, { preserveScroll: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.periodo.nombre}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Editar Periodo
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ props.periodo.nombre }}
                    </p>
                </div>
                <Button v-if="periodo.estado === 'activo'" @click="finalizar" variant="outline" class="text-rose-500 border-rose-200 hover:bg-rose-50">
                    <FlagTriangleRight class="h-4 w-4 mr-1.5" /> Finalizar Periodo
                </Button>
            </div>

            <div class="max-w-xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <PeriodoForm :periodo="periodo" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
