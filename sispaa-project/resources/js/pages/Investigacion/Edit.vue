<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';

const props = defineProps<{
    proyecto: { id: number; titulo: string; objetivo: string | null };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const form = useForm({
    titulo: props.proyecto.titulo,
    objetivo: props.proyecto.objetivo ?? '',
});

const submit = () => {
    form.put(route('investigacion.update', props.proyecto.id), {
        preserveScroll: true,
        onSuccess: () => toast.success('Proyecto actualizado.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.proyecto.titulo}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Editar Proyecto</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ props.proyecto.titulo }}</p>
            </div>

            <div class="max-w-xl rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Título *</label>
                        <input v-model="form.titulo" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.titulo" class="text-xs text-rose-500 mt-1">{{ form.errors.titulo }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Objetivo</label>
                        <textarea v-model="form.objetivo" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Guardando...' : 'Guardar cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
