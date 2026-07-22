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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                        <FolderOpen class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">{{ grupo.nombre }}</h1>
                        <p v-if="grupo.descripcion" class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ grupo.descripcion }}</p>
                    </div>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('secretaria.grupos-documentos.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div class="max-w-2xl w-full mx-auto grid gap-4 sm:gap-6 sm:grid-cols-2">
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Estado</h4>
                    <p class="mt-2 text-sm font-semibold" :class="grupo.activo ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]' : 'opacity-50 text-[var(--sispaa-text)]'">
                        {{ grupo.activo ? 'Activo' : 'Inactivo' }}
                    </p>
                </div>
                <div class="rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                    <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Creado por</h4>
                    <p class="mt-2 text-sm font-semibold text-[var(--sispaa-text)]">
                        {{ grupo.creator?.name ?? grupo.creadoPor?.name ?? '—' }}
                    </p>
                    <p class="text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5">{{ formatDate(grupo.created_at) }}</p>
                </div>
            </div>

            <div class="max-w-2xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)] mb-3">Requisitos</h4>
                <ul class="space-y-2">
                    <li v-for="r in grupo.requisitos" :key="r.id" class="text-sm text-[var(--sispaa-text)] flex items-center gap-2">
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--sispaa-primary)]"></span>
                        {{ r.nombre }}
                        <span v-if="!r.activo" class="text-xs opacity-50 text-[var(--sispaa-text)]">(inactivo)</span>
                    </li>
                    <li v-if="grupo.requisitos.length === 0" class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin requisitos aún.</li>
                </ul>

                <form @submit.prevent="submitNuevoRequisito" class="mt-5 pt-4 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] flex flex-col gap-2 sm:flex-row sm:items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-[var(--sispaa-text)] mb-1.5">Nuevo requisito</label>
                        <input v-model="nuevoRequisitoForm.nombre" type="text" placeholder="Ej: Certificado médico" class="w-full rounded-lg border-0 bg-[var(--sispaa-background)] text-sm text-[var(--sispaa-text)] focus:ring-2 focus:ring-[var(--sispaa-primary)]" />
                        <p v-if="nuevoRequisitoForm.errors.nombre" class="text-xs text-rose-500 mt-1">{{ nuevoRequisitoForm.errors.nombre }}</p>
                    </div>
                    <Button type="submit" :disabled="nuevoRequisitoForm.processing" class="font-semibold text-white shrink-0 bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Plus class="h-4 w-4 mr-1" /> Agregar
                    </Button>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
