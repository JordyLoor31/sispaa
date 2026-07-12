<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, FlaskConical, MapPin, Users2 } from 'lucide-vue-next';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
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

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.laboratorio?.nombre ?? '',
        ubicacion: props.laboratorio?.ubicacion ?? '',
        carrera_id: props.laboratorio?.carrera_id ?? '',
        capacidad: props.laboratorio?.capacidad ?? null,
        responsable_id: props.laboratorio?.responsable?.id ?? '',
    },
});

const [carreraId] = defineField('carrera_id');
const [responsableId] = defineField('responsable_id');

const carreraInicial = props.carreras.find((c) => c.id === props.laboratorio?.carrera_id);
const selectedCarreraObj = ref<{ value: string | number; label: string } | null>(
    carreraInicial ? { value: carreraInicial.id, label: carreraInicial.nombre } : null,
);
watch(selectedCarreraObj, (newVal) => {
    carreraId.value = newVal ? newVal.value : '';
});

const responsableInicial = props.laboratorio?.responsable;
const selectedResponsableObj = ref<{ value: string | number; label: string } | null>(
    responsableInicial ? { value: responsableInicial.id, label: responsableInicial.name } : null,
);
watch(selectedResponsableObj, (newVal) => {
    responsableId.value = newVal ? newVal.value : '';
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
                    <InputGroup>
                        <InputGroupAddon><FlaskConical class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="ubicacion">
            <FormItem>
                <FormLabel>Ubicación</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><MapPin class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" placeholder="Bloque A - Piso 2" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="carrera_id">
            <FormItem>
                <FormLabel>Carrera</FormLabel>
                <Combobox v-model="selectedCarreraObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedCarreraObj ? selectedCarreraObj.label : 'Sin asignar' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                        <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                :value="{ value: '', label: 'Sin asignar' }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                Sin asignar
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem
                                v-for="c in carreras"
                                :key="c.id"
                                :value="{ value: c.id, label: c.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                {{ c.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="capacidad">
            <FormItem>
                <FormLabel>Capacidad</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Users2 class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="number" min="1" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="responsable_id">
            <FormItem>
                <FormLabel>Responsable</FormLabel>
                <Combobox v-model="selectedResponsableObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedResponsableObj ? selectedResponsableObj.label : 'Sin asignar' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                        <ComboboxInput placeholder="Buscar responsable..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron responsables.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                :value="{ value: '', label: 'Sin asignar' }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                Sin asignar
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem
                                v-for="r in responsables"
                                :key="r.id"
                                :value="{ value: r.id, label: r.name }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                {{ r.name }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                {{ processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
