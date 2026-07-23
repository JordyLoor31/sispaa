<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import BeneficiarioForm from './BeneficiarioForm.vue';
import type { Beneficiario } from './types';

const props = defineProps<{
    beneficiario: Beneficiario;
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.beneficiario.nombre}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Editar Beneficiario</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.beneficiario.nombre }}</p>
            </div>

            <div class="mx-auto w-full max-w-xl rounded-2xl border p-5 shadow-sm sm:p-6 bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <BeneficiarioForm :beneficiario="beneficiario" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
