<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Building2, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { AlertDialog, AlertDialogAction, AlertDialogCancel, AlertDialogContent, AlertDialogDescription, AlertDialogFooter, AlertDialogHeader, AlertDialogTitle } from '@/components/ui/alert-dialog';
import { toast } from 'vue-sonner';
import type { Empresa } from './types';

defineProps<{
    empresas: Empresa[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const deleteTarget = ref<Empresa | null>(null);
const confirmDelete = () => {
    if (!deleteTarget.value) return;
    router.delete(route('vinculacion.empresas.destroy', deleteTarget.value.id), {
        preserveScroll: true,
        onSuccess: () => { toast.success('Empresa eliminada.'); deleteTarget.value = null; },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Empresas Beneficiadas" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Empresas Beneficiadas</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Catálogo de empresas vinculadas a las actividades.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('vinculacion.empresas.create')">
                        <Plus class="h-4 w-4" /> Nueva Empresa
                    </Link>
                </Button>
            </div>

            <div class="max-w-5xl w-full grid gap-4 md:grid-cols-2">
                <div v-for="e in empresas" :key="e.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                            <Building2 class="h-4.5 w-4.5" />
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                            {{ e.actividades_count ?? 0 }} actividad(es)
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ e.nombre }}</h3>
                        <p v-if="e.ruc" class="text-xs text-slate-500 mt-0.5">RUC: {{ e.ruc }}</p>
                        <p v-if="e.sector" class="text-xs text-slate-400 mt-1">{{ e.sector }}</p>
                        <p v-if="e.contacto" class="text-xs text-slate-400">{{ e.contacto }}</p>
                    </div>
                    <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <Link :href="route('vinculacion.empresas.show', e.id)" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold">
                            <Eye class="h-3.5 w-3.5" /> Ver
                        </Link>
                        <Link :href="route('vinculacion.empresas.edit', e.id)" class="inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 font-semibold">
                            <Pencil class="h-3.5 w-3.5" /> Editar
                        </Link>
                        <button @click="deleteTarget = e" class="ml-auto inline-flex items-center gap-1 text-xs text-rose-500 hover:text-rose-600 font-semibold">
                            <Trash2 class="h-3.5 w-3.5" /> Eliminar
                        </button>
                    </div>
                </div>

                <div v-if="empresas.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay empresas registradas.
                </div>
            </div>
        </div>

        <AlertDialog :open="!!deleteTarget" @update:open="val => { if (!val) deleteTarget = null; }">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>¿Eliminar empresa?</AlertDialogTitle>
                    <AlertDialogDescription>Se eliminará "{{ deleteTarget?.nombre }}". Esta acción no se puede deshacer.</AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction @click="confirmDelete" class="bg-rose-600 hover:bg-rose-500">Eliminar</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppSidebarLayout>
</template>
