<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { UserPlus } from 'lucide-vue-next';
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="mx-auto w-full max-w-2xl">
                <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <!-- Cabecera de la tarjeta -->
                    <div class="flex items-center gap-3.5 border-b p-5 sm:p-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm"
                            style="background: linear-gradient(135deg, var(--sispaa-primary), color-mix(in srgb, var(--sispaa-primary) 45%, var(--sispaa-secondary)))"
                        >
                            <UserPlus class="h-5 w-5" />
                        </div>
                        <div>
                            <h1 class="text-lg font-bold tracking-tight text-[var(--sispaa-text)] sm:text-xl">
                                Registrar Nuevo Usuario
                            </h1>
                            <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                                Ingrese los datos para registrar la nueva cuenta.
                            </p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <UserForm :roles="roles" :carreras="carreras" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
