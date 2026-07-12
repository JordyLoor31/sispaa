<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { toast } from 'vue-sonner';
import type { Catalogo } from './types';

defineProps<{
    periodos: Catalogo[];
    coordinadores: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        titulo: z.string().min(1, 'El título es obligatorio.'),
        objetivo: z.string().nullable().optional(),
        periodo_id: requiredSelect('Selecciona un período.'),
        coordinador_id: requiredSelect('Selecciona un coordinador.'),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        titulo: '',
        objetivo: '',
        periodo_id: '',
        coordinador_id: '',
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    router.post(route('investigacion.store'), values, {
        preserveScroll: true,
        onSuccess: () => toast.success('Proyecto creado.'),
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            toast.error('Revisa los campos del formulario.');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Proyecto de Investigación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Nuevo Proyecto de Investigación</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Selecciona el coordinador que supervisará tu proyecto.</p>
            </div>

            <div class="max-w-xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ componentField }" name="titulo">
                        <FormItem>
                            <FormLabel>Título *</FormLabel>
                            <FormControl>
                                <input
                                    v-bind="componentField"
                                    type="text"
                                    class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="objetivo">
                        <FormItem>
                            <FormLabel>Objetivo</FormLabel>
                            <FormControl>
                                <textarea
                                    v-bind="componentField"
                                    rows="3"
                                    class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                ></textarea>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="periodo_id">
                        <FormItem>
                            <FormLabel>Período *</FormLabel>
                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un período..." /></SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    <SelectItem v-for="per in periodos" :key="per.id" :value="per.id">{{ per.nombre }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="coordinador_id">
                        <FormItem>
                            <FormLabel>Coordinador supervisor *</FormLabel>
                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un coordinador..." /></SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    <SelectItem v-for="c in coordinadores" :key="c.id" :value="c.id">{{ c.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                            {{ processing ? 'Creando...' : 'Crear proyecto' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
