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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Empresas Beneficiadas</h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Catálogo de empresas vinculadas a las actividades.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('vinculacion.empresas.create')">
                        <Plus class="h-4 w-4" /> Nueva Empresa
                    </Link>
                </Button>
            </div>

            <div class="grid w-full max-w-5xl gap-4 md:grid-cols-2">
                <div v-for="e in empresas" :key="e.id"
                    class="flex flex-col gap-3 rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-start justify-between">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                            <Building2 class="h-4.5 w-4.5" />
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold text-[var(--sispaa-text)] bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            {{ e.actividades_count ?? 0 }} actividad(es)
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ e.nombre }}</h3>
                        <p v-if="e.ruc" class="mt-0.5 text-xs opacity-70 text-[var(--sispaa-text)]">RUC: {{ e.ruc }}</p>
                        <p v-if="e.sector" class="mt-1 text-xs opacity-50 text-[var(--sispaa-text)]">{{ e.sector }}</p>
                        <p v-if="e.contacto" class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ e.contacto }}</p>
                    </div>
                    <div class="flex items-center gap-2 border-t pt-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <Link :href="route('vinculacion.empresas.show', e.id)" class="inline-flex items-center gap-1 text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)]">
                            <Eye class="h-3.5 w-3.5" /> Ver
                        </Link>
                        <Link :href="route('vinculacion.empresas.edit', e.id)" class="inline-flex items-center gap-1 text-xs font-semibold opacity-70 text-[var(--sispaa-text)] hover:opacity-100 hover:text-[var(--sispaa-primary)]">
                            <Pencil class="h-3.5 w-3.5" /> Editar
                        </Link>
                        <button @click="deleteTarget = e" class="ml-auto inline-flex items-center gap-1 text-xs font-semibold text-rose-500 hover:text-rose-600">
                            <Trash2 class="h-3.5 w-3.5" /> Eliminar
                        </button>
                    </div>
                </div>

                <div v-if="empresas.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
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
