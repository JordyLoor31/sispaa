<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import type { Persona } from './columns';

defineProps<{
    estudiantes: Persona[];
    tutores: Persona[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const form = useForm({
    estudiante_id: '' as number | '',
    tutor_id: '' as number | '',
    tema: '',
    fecha_inicio: '',
});

const submit = () => {
    form.post(route('titulacion.store'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Proceso de titulación registrado.'),
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Registrar Tema de Titulación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Registrar Tema de Titulación</h1>
            </div>

            <div class="max-w-xl rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Estudiante *</label>
                        <Select v-model="form.estudiante_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un estudiante..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="e in estudiantes" :key="e.id" :value="e.id">{{ e.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.estudiante_id" class="text-xs text-rose-500 mt-1">{{ form.errors.estudiante_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tutor *</label>
                        <Select v-model="form.tutor_id">
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un tutor..." /></SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="t in tutores" :key="t.id" :value="t.id">{{ t.name }}</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.tutor_id" class="text-xs text-rose-500 mt-1">{{ form.errors.tutor_id }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tema *</label>
                        <textarea v-model="form.tema" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                        <p v-if="form.errors.tema" class="text-xs text-rose-500 mt-1">{{ form.errors.tema }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha de inicio</label>
                        <input v-model="form.fecha_inicio" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                    </div>
                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Guardando...' : 'Registrar' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
