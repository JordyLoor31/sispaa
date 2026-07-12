<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import type { Catalogo } from './types';

defineProps<{
    periodos: Catalogo[];
    coordinadores: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const form = useForm({
    titulo: '',
    objetivo: '',
    periodo_id: '' as number | '',
    coordinador_id: '' as number | '',
});

const submit = () => {
    form.post(route('investigacion.store'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Proyecto creado.'),
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Proyecto de Investigación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Nuevo Proyecto de Investigación</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Selecciona el coordinador que supervisará tu proyecto.</p>
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
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Período *</label>
                        <Select v-model="form.periodo_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un período..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="per in periodos" :key="per.id" :value="per.id">{{ per.nombre }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.periodo_id" class="text-xs text-rose-500 mt-1">{{ form.errors.periodo_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Coordinador supervisor *</label>
                        <Select v-model="form.coordinador_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un coordinador..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="c in coordinadores" :key="c.id" :value="c.id">{{ c.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.coordinador_id" class="text-xs text-rose-500 mt-1">{{ form.errors.coordinador_id }}</p>
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Creando...' : 'Crear proyecto' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
