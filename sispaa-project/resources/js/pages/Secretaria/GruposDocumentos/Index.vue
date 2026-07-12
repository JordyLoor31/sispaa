<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, FolderOpen, Power, Eye } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import type { GrupoDocumento } from './types';

const props = defineProps<{
    grupos: GrupoDocumento[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const toggleGrupo = (grupo: GrupoDocumento) => {
    router.post(route('secretaria.grupos-documentos.toggle', grupo.id), {}, {
        preserveScroll: true,
        onSuccess: () => toast.success(grupo.activo ? 'Grupo desactivado.' : 'Grupo activado.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Grupos de Documentos" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Grupos de Documentos</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Define catálogos de requisitos documentales. Al crear un grupo, se notifica a todos los estudiantes.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('secretaria.grupos-documentos.create')">
                        <Plus class="h-4 w-4" />
                        Nuevo Grupo
                    </Link>
                </Button>
            </div>

            <div class="max-w-5xl w-full grid gap-4 md:grid-cols-2">
                <div v-for="g in props.grupos" :key="g.id"
                    class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950 flex flex-col gap-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-950/30 dark:text-indigo-400">
                                <FolderOpen class="h-4.5 w-4.5" />
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ g.nombre }}</h3>
                                <p v-if="g.descripcion" class="text-xs text-slate-400">{{ g.descripcion }}</p>
                            </div>
                        </div>
                        <button @click="toggleGrupo(g)"
                            :class="['inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold transition-colors', g.activo
                                ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 hover:bg-emerald-100'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500 hover:bg-slate-200']">
                            <Power class="h-3.5 w-3.5" />
                            {{ g.activo ? 'Activo' : 'Inactivo' }}
                        </button>
                    </div>

                    <ul class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <li v-for="r in g.requisitos" :key="r.id" class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2">
                            <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                            {{ r.nombre }}
                            <span v-if="!r.activo" class="text-slate-400">(inactivo)</span>
                        </li>
                        <li v-if="g.requisitos.length === 0" class="text-xs text-slate-400">Sin requisitos aún.</li>
                    </ul>

                    <Link :href="route('secretaria.grupos-documentos.show', g.id)"
                        class="mt-1 inline-flex items-center gap-1 self-start text-xs text-indigo-600 hover:text-indigo-500 font-semibold">
                        <Eye class="h-3.5 w-3.5" /> Ver detalle / agregar requisito
                    </Link>
                </div>

                <div v-if="props.grupos.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay grupos de documentos creados.
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
