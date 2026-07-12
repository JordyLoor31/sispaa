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
import type { Persona } from './columns';

defineProps<{
    estudiantes: Persona[];
    tutores: Persona[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        estudiante_id: requiredSelect('Selecciona un estudiante.'),
        tutor_id: requiredSelect('Selecciona un tutor.'),
        tema: z.string().min(1, 'El tema es obligatorio.'),
        fecha_inicio: z.string().nullable().optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        estudiante_id: '',
        tutor_id: '',
        tema: '',
        fecha_inicio: '',
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    router.post(route('titulacion.store'), values, {
        preserveScroll: true,
        onSuccess: () => toast.success('Proceso de titulación registrado.'),
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
        <Head title="Registrar Tema de Titulación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Registrar Tema de Titulación</h1>
            </div>

            <div class="max-w-xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ componentField }" name="estudiante_id">
                        <FormItem>
                            <FormLabel>Estudiante *</FormLabel>
                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un estudiante..." /></SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    <SelectItem v-for="e in estudiantes" :key="e.id" :value="e.id">{{ e.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="tutor_id">
                        <FormItem>
                            <FormLabel>Tutor *</FormLabel>
                            <Select v-bind="componentField">
                                <FormControl>
                                    <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un tutor..." /></SelectTrigger>
                                </FormControl>
                                <SelectContent>
                                    <SelectItem v-for="t in tutores" :key="t.id" :value="t.id">{{ t.name }}</SelectItem>
                                </SelectContent>
                            </Select>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="tema">
                        <FormItem>
                            <FormLabel>Tema *</FormLabel>
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

                    <FormField v-slot="{ componentField }" name="fecha_inicio">
                        <FormItem>
                            <FormLabel>Fecha de inicio</FormLabel>
                            <FormControl>
                                <input
                                    v-bind="componentField"
                                    type="date"
                                    class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                                />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                            {{ processing ? 'Guardando...' : 'Registrar' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
