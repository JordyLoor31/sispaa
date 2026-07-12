<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import type { Empresa } from './types';

const props = defineProps<{
    empresa?: Empresa | null;
}>();

const isEditing = !!props.empresa;

const formSchema = toTypedSchema(
    z.object({
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        ruc: z.string().max(13, 'El RUC no puede superar los 13 caracteres.').nullable().optional(),
        sector: z.string().max(255, 'El sector no puede superar los 255 caracteres.').nullable().optional(),
        contacto: z.string().max(255, 'El contacto no puede superar los 255 caracteres.').nullable().optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.empresa?.nombre ?? '',
        ruc: props.empresa?.ruc ?? '',
        sector: props.empresa?.sector ?? '',
        contacto: props.empresa?.contacto ?? '',
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing && props.empresa) {
        router.put(route('vinculacion.empresas.update', props.empresa.id), values, options);
    } else {
        router.post(route('vinculacion.empresas.store'), values, options);
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

        <FormField v-slot="{ componentField }" name="ruc">
            <FormItem>
                <FormLabel>RUC</FormLabel>
                <FormControl>
                    <Input type="text" maxlength="13" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="sector">
            <FormItem>
                <FormLabel>Sector</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="contacto">
            <FormItem>
                <FormLabel>Contacto</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Registrar empresa' }}
            </Button>
        </div>
    </form>
</template>
