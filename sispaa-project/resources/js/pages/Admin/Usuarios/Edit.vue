<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import UserForm from './UserForm.vue';
import { getInitials, type Usuario, type Role, type Carrera } from './columns';

const props = defineProps<{
    usuario: Usuario;
    roles: Role[];
    carreras: Carrera[];
    breadcrumbs?: BreadcrumbItemType[];
}>();
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.usuario.name}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="mx-auto w-full max-w-2xl">
                <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <!-- Cabecera de la tarjeta: avatar + usuario que se edita -->
                    <div class="flex items-center gap-3.5 border-b p-5 sm:p-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-bold text-white shadow-sm"
                            style="background: linear-gradient(135deg, var(--sispaa-primary), color-mix(in srgb, var(--sispaa-primary) 45%, var(--sispaa-secondary)))"
                        >
                            {{ getInitials(usuario.name) }}
                        </div>
                        <div class="min-w-0">
                            <h1 class="text-lg font-bold tracking-tight text-[var(--sispaa-text)] sm:text-xl">
                                Editar Usuario
                            </h1>
                            <p class="mt-0.5 truncate text-sm opacity-60 text-[var(--sispaa-text)]">
                                {{ props.usuario.name }} · {{ props.usuario.email }}
                            </p>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6">
                        <UserForm :usuario="usuario" :roles="roles" :carreras="carreras" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
