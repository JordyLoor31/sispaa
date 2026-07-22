<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import UserForm from './UserForm.vue';
import type { Role, Carrera } from './columns';

defineProps<{
    roles: Role[];
    carreras: Carrera[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Usuario" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Registrar Nuevo Usuario
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Ingrese los datos para registrar la nueva cuenta.
                </p>
            </div>

            <div class="w-full max-w-2xl mx-auto rounded-2xl p-4 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <UserForm :roles="roles" :carreras="carreras" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
