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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Registrar Nuevo Usuario
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Ingrese los datos para registrar la nueva cuenta.
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <UserForm :roles="roles" :carreras="carreras" />
            </div>
        </div>
    </AppSidebarLayout>
</template>
