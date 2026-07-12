<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { toast } from 'vue-sonner';
import type { Catalogo, ReactivoItem } from './types';

const props = defineProps<{
    reactivo?: ReactivoItem;
    laboratorios: Catalogo[];
}>();

const isEdit = !!props.reactivo;

const formSchema = toTypedSchema(
    z.object({
        laboratorio_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, {
            message: 'Selecciona un laboratorio.',
        }),
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        formula: z.string().max(255, 'La fórmula no puede superar los 255 caracteres.').nullable().optional(),
        cantidad: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int('Debe ser un número entero.').min(0, 'No puede ser negativo.'),
        unidad: z.string().max(30, 'La unidad no puede superar los 30 caracteres.').nullable().optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        laboratorio_id: props.reactivo?.laboratorio_id ?? '',
        nombre: props.reactivo?.nombre ?? '',
        formula: props.reactivo?.formula ?? '',
        cantidad: props.reactivo?.cantidad ?? 0,
        unidad: props.reactivo?.unidad ?? '',
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success(isEdit ? 'Reactivo actualizado.' : 'Reactivo registrado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit && props.reactivo) {
        router.put(route('laboratorio.reactivos.update', props.reactivo.id), values, options);
    } else {
        router.post(route('laboratorio.reactivos.store'), values, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="laboratorio_id">
            <FormItem>
                <FormLabel>Laboratorio *</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="l in laboratorios" :key="l.id" :value="l.id">{{ l.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre *</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="formula">
            <FormItem>
                <FormLabel>Fórmula</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-2 gap-4">
            <FormField v-slot="{ componentField }" name="cantidad">
                <FormItem>
                    <FormLabel>Cantidad *</FormLabel>
                    <FormControl>
                        <Input type="number" min="0" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="unidad">
                <FormItem>
                    <FormLabel>Unidad</FormLabel>
                    <FormControl>
                        <Input type="text" placeholder="ml, gr, L..." v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
