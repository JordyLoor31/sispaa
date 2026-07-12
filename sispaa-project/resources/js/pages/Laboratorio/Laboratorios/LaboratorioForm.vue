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
import type { Catalogo, LaboratorioItem, Persona } from './types';

const props = defineProps<{
    laboratorio?: LaboratorioItem;
    carreras: Catalogo[];
    responsables: Persona[];
}>();

const isEdit = !!props.laboratorio;

const formSchema = toTypedSchema(
    z.object({
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        ubicacion: z.string().max(255, 'La ubicación no puede superar los 255 caracteres.').nullable().optional(),
        carrera_id: z.union([z.string(), z.number()]).nullable(),
        capacidad: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int().min(1, 'Debe ser al menos 1.').nullable(),
        responsable_id: z.union([z.string(), z.number()]).nullable(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.laboratorio?.nombre ?? '',
        ubicacion: props.laboratorio?.ubicacion ?? '',
        carrera_id: props.laboratorio?.carrera_id ?? null,
        capacidad: props.laboratorio?.capacidad ?? null,
        responsable_id: props.laboratorio?.responsable?.id ?? null,
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const payload = {
        ...values,
        carrera_id: values.carrera_id === '' ? null : values.carrera_id,
        responsable_id: values.responsable_id === '' ? null : values.responsable_id,
    };

    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success(isEdit ? 'Laboratorio actualizado.' : 'Laboratorio registrado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit && props.laboratorio) {
        router.put(route('laboratorio.laboratorios.update', props.laboratorio.id), payload, options);
    } else {
        router.post(route('laboratorio.laboratorios.store'), payload, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre *</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="ubicacion">
            <FormItem>
                <FormLabel>Ubicación</FormLabel>
                <FormControl>
                    <Input type="text" placeholder="Bloque A - Piso 2" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="carrera_id">
            <FormItem>
                <FormLabel>Carrera</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="capacidad">
            <FormItem>
                <FormLabel>Capacidad</FormLabel>
                <FormControl>
                    <Input type="number" min="1" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="responsable_id">
            <FormItem>
                <FormLabel>Responsable</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="r in responsables" :key="r.id" :value="r.id">{{ r.name }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
