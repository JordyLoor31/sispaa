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
import type { Catalogo, EquipoItem } from './types';

const props = defineProps<{
    equipo?: EquipoItem;
    laboratorios: Catalogo[];
}>();

const isEdit = !!props.equipo;

const formSchema = toTypedSchema(
    z.object({
        laboratorio_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, {
            message: 'Selecciona un laboratorio.',
        }),
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        codigo: z.string().min(1, 'El código es obligatorio.').max(30, 'El código no puede superar los 30 caracteres.'),
        cantidad: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int('Debe ser un número entero.').min(1, 'Debe ser al menos 1.'),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        laboratorio_id: props.equipo?.laboratorio_id ?? '',
        nombre: props.equipo?.nombre ?? '',
        codigo: props.equipo?.codigo ?? '',
        cantidad: props.equipo?.cantidad ?? 1,
    },
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success(isEdit ? 'Equipo actualizado.' : 'Equipo registrado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit && props.equipo) {
        router.put(route('laboratorio.equipos.update', props.equipo.id), values, options);
    } else {
        router.post(route('laboratorio.equipos.store'), values, options);
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

        <FormField v-slot="{ componentField }" name="codigo">
            <FormItem>
                <FormLabel>Código *</FormLabel>
                <FormControl>
                    <Input type="text" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="cantidad">
            <FormItem>
                <FormLabel>Cantidad *</FormLabel>
                <FormControl>
                    <Input type="number" min="1" v-bind="componentField" />
                </FormControl>
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
