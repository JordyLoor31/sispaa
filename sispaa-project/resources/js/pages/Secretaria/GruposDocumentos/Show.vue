<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ArrowLeft, FolderOpen, Plus } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { GrupoDocumento } from './types';

const props = defineProps<{
    grupo: GrupoDocumento;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const nuevoRequisitoForm = useForm({ nombre: '' });

const submitNuevoRequisito = () => {
    nuevoRequisitoForm.post(route('secretaria.grupos-documentos.requisitos.store', props.grupo.id), {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Requisito agregado.');
            nuevoRequisitoForm.reset();
        },
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.grupo.nombre" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                        <FolderOpen class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ grupo.nombre }}</h1>
                        <p v-if="grupo.descripcion" class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ grupo.descripcion }}</p>
                    </div>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('secretaria.grupos-documentos.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="grupo.activo ? 'text-emerald-600' : 'text-slate-400'">
                        {{ grupo.activo ? 'Activo' : 'Inactivo' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Creado por</h4>
                    <p class="mt-2 text-sm font-semibold text-slate-800 dark:text-slate-200">
                        {{ grupo.creator?.name ?? grupo.creadoPor?.name ?? '—' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ formatDate(grupo.created_at) }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Requisitos</h4>
                <ul class="space-y-2">
                    <li v-for="r in grupo.requisitos" :key="r.id" class="text-sm text-slate-700 dark:text-slate-300 flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                        {{ r.nombre }}
                        <span v-if="!r.activo" class="text-xs text-slate-400">(inactivo)</span>
                    </li>
                    <li v-if="grupo.requisitos.length === 0" class="text-sm text-slate-400">Sin requisitos aún.</li>
                </ul>

                <form @submit.prevent="submitNuevoRequisito" class="mt-5 pt-4 border-t border-slate-100 dark:border-slate-800 flex items-end gap-2">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nuevo requisito</label>
                        <input v-model="nuevoRequisitoForm.nombre" type="text" placeholder="Ej: Certificado médico" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="nuevoRequisitoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ nuevoRequisitoForm.errors.nombre }}</p>
                    </div>
                    <Button type="submit" :disabled="nuevoRequisitoForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold shrink-0">
                        <Plus class="h-4 w-4 mr-1" /> Agregar
                    </Button>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
