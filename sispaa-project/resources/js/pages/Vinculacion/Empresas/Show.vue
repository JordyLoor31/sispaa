<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Pencil, Building2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import type { Empresa } from './types';

const props = defineProps<{
    empresa: Empresa;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.empresa.nombre" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <Building2 class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ empresa.nombre }}</h1>
                        <p v-if="empresa.sector" class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ empresa.sector }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('vinculacion.empresas')">
                            <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        <Link :href="route('vinculacion.empresas.edit', empresa.id)">
                            <Pencil class="h-4 w-4 mr-1.5" /> Editar
                        </Link>
                    </Button>
                </div>
            </div>

            <div class="max-w-2xl w-full grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">RUC</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ empresa.ruc ?? '—' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Contacto</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ empresa.contacto ?? '—' }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Actividades vinculadas</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">{{ empresa.actividades_count ?? 0 }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Auditoría</h4>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs text-slate-400">Creado por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ empresa.creator?.name ?? '—' }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ formatDate(empresa.created_at) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Última actualización por</p>
                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ empresa.updater?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
