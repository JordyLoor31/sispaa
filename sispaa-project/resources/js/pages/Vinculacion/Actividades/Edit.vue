<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import ActividadForm from './ActividadForm.vue';
import type { Actividad, BeneficiarioRef, Catalogo, RepresentanteRef } from './types';

const props = defineProps<{
    actividad: Actividad;
    docentes: { id: number; name: string }[];
    carreras: Catalogo[];
    periodos: Catalogo[];
    beneficiarios: BeneficiarioRef[];
    representantes: RepresentanteRef[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.actividad.nombre}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,white)]">
            <div>
                <h2 class="text-base font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Actividad</h2>
                <p class="mt-1 text-sm opacity-70 text-[var(--sispaa-text)]">{{ props.actividad.nombre }}</p>
            </div>

            <div class="mx-auto w-full max-w-xl rounded-2xl border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] p-4 shadow-sm sm:p-6">
                <ActividadForm :actividad="actividad" :docentes="docentes" :carreras="carreras" :periodos="periodos" :beneficiarios="beneficiarios" :representantes="representantes" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
