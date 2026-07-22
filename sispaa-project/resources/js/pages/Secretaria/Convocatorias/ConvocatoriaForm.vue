<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Megaphone, FileText, Calendar } from 'lucide-vue-next';
import type { Convocatoria } from './types';

const props = defineProps<{
    convocatoria?: Convocatoria | null;
    modulos: string[];
}>();

const isEditing = !!props.convocatoria;

const formSchema = toTypedSchema(
    z
        .object({
            titulo: z.string().min(1, 'El título es obligatorio.').max(255, 'El título no puede superar los 255 caracteres.'),
            descripcion: z.string().max(1000, 'La descripción no puede superar los 1000 caracteres.').nullable().optional(),
            modulo: z.string().min(1, 'Selecciona un módulo.').max(100),
            tipo_documento: z.string().max(255).nullable().optional(),
            fecha_inicio: z.string().min(1, 'La fecha de inicio es obligatoria.'),
            fecha_fin: z.string().min(1, 'La fecha de fin es obligatoria.'),
            activa: z.boolean(),
        })
        .refine((data) => !data.fecha_inicio || !data.fecha_fin || data.fecha_fin >= data.fecha_inicio, {
            message: 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            path: ['fecha_fin'],
        }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        titulo: props.convocatoria?.titulo ?? '',
        descripcion: props.convocatoria?.descripcion ?? '',
        modulo: props.convocatoria?.modulo ?? '',
        tipo_documento: props.convocatoria?.tipo_documento ?? '',
        fecha_inicio: props.convocatoria?.fecha_inicio ?? '',
        fecha_fin: props.convocatoria?.fecha_fin ?? '',
        activa: props.convocatoria?.activa ?? true,
    },
});

const [activa] = defineField('activa');

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

    if (isEditing && props.convocatoria) {
        router.put(route('secretaria.convocatorias.update', props.convocatoria.id), values, options);
    } else {
        router.post(route('secretaria.convocatorias.store'), values, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="titulo">
            <FormItem>
                <FormLabel>Título *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Megaphone class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="modulo">
            <FormItem>
                <FormLabel>Módulo *</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un módulo..." /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="m in modulos" :key="m" :value="m">{{ m }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="tipo_documento">
            <FormItem>
                <FormLabel>Tipo de documento (opcional)</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><FileText class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="descripcion">
            <FormItem>
                <FormLabel>Descripción</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupTextarea v-bind="componentField" rows="3" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <FormField v-slot="{ componentField }" name="fecha_inicio">
                <FormItem>
                    <FormLabel>Inicio *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Calendar class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="date" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="fecha_fin">
                <FormItem>
                    <FormLabel>Fin *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Calendar class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="date" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <div v-if="isEditing" class="flex items-center gap-2">
            <input id="activa" v-model="activa" type="checkbox" class="rounded border-0 accent-[var(--sispaa-primary)]" />
            <label for="activa" class="text-sm text-[var(--sispaa-text)]">Convocatoria activa</label>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Crear convocatoria' }}
            </Button>
        </div>
    </form>
</template>
