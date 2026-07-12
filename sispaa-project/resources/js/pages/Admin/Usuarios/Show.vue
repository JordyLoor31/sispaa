<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Shield } from 'lucide-vue-next';
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
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.usuario.name" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ usuario.name }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ usuario.email }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('admin.usuarios.index')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        <Link :href="route('admin.usuarios.edit', usuario.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="usuario.is_active ? 'text-emerald-600' : 'text-slate-400'">
                        {{ usuario.is_active ? 'Activo' : 'Inactivo' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Identificación / Teléfono</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ usuario.cedula ?? '—' }} · {{ usuario.telefono ?? '—' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Carrera</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ usuario.carrera?.nombre ?? 'General / Institucional' }}
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Roles</h4>
                <div class="flex flex-wrap gap-2">
                    <span
                        v-for="rol in usuario.roles"
                        :key="rol.id"
                        class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-400"
                    >
                        <Shield class="h-3 w-3" />
                        {{ rol.name.charAt(0).toUpperCase() + rol.name.slice(1) }}
                    </span>
                    <span v-if="usuario.roles.length === 0" class="text-xs text-slate-400">Sin roles asignados</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Cuenta creada</h4>
                <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                    {{ formatDate(usuario.created_at) }}
                </p>
            </div>
        </div>
    </AppSidebarLayout>
</template>
