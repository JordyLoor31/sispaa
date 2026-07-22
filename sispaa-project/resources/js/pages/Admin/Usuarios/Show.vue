<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, FileText, Pencil, Shield } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { Usuario } from './columns';

const props = defineProps<{
    usuario: Usuario;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const esEstudiante = props.usuario.roles.some((rol) => rol.name === 'estudiante');
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.usuario.name" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        {{ usuario.name }}
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        {{ usuario.email }}
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('admin.usuarios.index')">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                        </Link>
                    </Button>
                    <Button v-if="esEstudiante" as-child variant="outline">
                        <Link :href="route('admin.estudiantes.perfiles.show', usuario.id)">
                            <FileText class="mr-1.5 h-4 w-4" /> Ver Datos Adicionales
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('admin.usuarios.edit', usuario.id)">
                            <Pencil class="mr-1.5 h-4 w-4" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="usuario.is_active ? 'text-[var(--sispaa-secondary)]' : 'opacity-50 text-[var(--sispaa-text)]'">
                        {{ usuario.is_active ? 'Activo' : 'Inactivo' }}
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Identificación / Teléfono</h4>
                    <p class="mt-2 text-sm font-semibold opacity-90 text-[var(--sispaa-text)]">
                        {{ usuario.cedula ?? '—' }} · {{ usuario.telefono ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Carrera</h4>
                    <p class="mt-2 text-sm font-semibold opacity-90 text-[var(--sispaa-text)]">
                        {{ usuario.carrera?.nombre ?? 'General / Institucional' }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <h4 class="mb-3 text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Roles</h4>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="rol in usuario.roles"
                        :key="rol.id"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]"
                    >
                        <Shield class="h-3 w-3" />
                        {{ rol.name.charAt(0).toUpperCase() + rol.name.slice(1) }}
                    </span>
                    <span v-if="usuario.roles.length === 0" class="text-xs opacity-50 text-[var(--sispaa-text)]">Sin roles asignados</span>
                </div>
            </div>

            <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Cuenta creada</h4>
                <p class="mt-2 text-sm font-semibold opacity-90 text-[var(--sispaa-text)]">
                    {{ formatDate(usuario.created_at) }}
                </p>
            </div>
        </div>
    </AppSidebarLayout>
</template>
