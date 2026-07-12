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
import type { Actividad, Catalogo } from './types';

const props = defineProps<{
    actividad?: Actividad | null;
    docentes: { id: number; name: string }[];
    carreras: Catalogo[];
    periodos: Catalogo[];
    empresas: Catalogo[];
}>();

const isEditing = !!props.actividad;

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        docente_lider_id: requiredSelect('Selecciona un docente líder.'),
        carrera_id: requiredSelect('Selecciona una carrera.'),
        periodo_id: requiredSelect('Selecciona un período.'),
        empresa_id: z.union([z.string(), z.number()]).nullable(),
        fecha: z.string().nullable().optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.actividad?.nombre ?? '',
        docente_lider_id: props.actividad?.docente_lider.id ?? '',
        carrera_id: props.actividad?.carrera_id ?? '',
        periodo_id: props.actividad?.periodo_id ?? '',
        empresa_id: props.actividad?.empresa_id ?? '',
        fecha: props.actividad?.fecha ?? '',
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

    if (isEditing && props.actividad) {
        router.put(route('vinculacion.actividades.update', props.actividad.id), values, options);
    } else {
        router.post(route('vinculacion.actividades.store'), values, options);
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

        <FormField v-slot="{ componentField }" name="docente_lider_id">
            <FormItem>
                <FormLabel>Docente líder *</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un docente..." /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="d in docentes" :key="d.id" :value="d.id">{{ d.name }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="carrera_id">
            <FormItem>
                <FormLabel>Carrera *</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona una carrera..." /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
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
                        <SelectItem v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="empresa_id">
            <FormItem>
                <FormLabel>Empresa (opcional)</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger class="w-full"><SelectValue placeholder="Sin empresa asociada" /></SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem v-for="e in empresas" :key="e.id" :value="e.id">{{ e.nombre }}</SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="fecha">
            <FormItem>
                <FormLabel>Fecha</FormLabel>
                <FormControl>
                    <Input type="date" v-bind="componentField" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Registrar actividad' }}
            </Button>
        </div>
    </form>
</template>
