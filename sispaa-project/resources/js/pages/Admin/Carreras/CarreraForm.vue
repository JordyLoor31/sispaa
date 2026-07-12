<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, ArrowLeft, GraduationCap, Hash } from 'lucide-vue-next';
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
import type { Carrera, Coordinador } from './columns';

const props = defineProps<{
    carrera?: Carrera | null;
    coordinadores: Coordinador[];
}>();

const formSchema = toTypedSchema(
    z.object({
        nombre: z
            .string({ required_error: 'El nombre es obligatorio.' })
            .min(1, 'El nombre es obligatorio.')
            .max(255, 'El nombre no puede superar los 255 caracteres.'),
        codigo: z
            .string({ required_error: 'El código es obligatorio.' })
            .min(1, 'El código es obligatorio.')
            .max(10, 'El código no puede superar los 10 caracteres.'),
        coordinador_id: z.union([z.string(), z.number()]).nullable(),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.carrera?.nombre ?? '',
        codigo: props.carrera?.codigo ?? '',
        coordinador_id: props.carrera?.coordinador_id ?? null,
    },
});

const [coordinadorId] = defineField('coordinador_id');

const processing = ref(false);

const selectedCoordinadorObj = ref<{ value: string | number; label: string } | null>(
    props.carrera?.coordinador ? { value: props.carrera.coordinador.id, label: props.carrera.coordinador.name } : null,
);

watch(selectedCoordinadorObj, (newVal) => {
    coordinadorId.value = newVal ? newVal.value : null;
});

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const payload = {
        ...values,
        coordinador_id: values.coordinador_id === '' ? null : values.coordinador_id,
    };

    const options = {
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (props.carrera) {
        router.put(route('admin.carreras.update', props.carrera.id), payload, options);
    } else {
        router.post(route('admin.carreras.store'), payload, options);
    }
});
</script>

<template>
    <form class="max-w-xl space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre de Carrera *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><GraduationCap class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="codigo">
            <FormItem>
                <FormLabel>Código / Sigla *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Hash class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="coordinador_id">
            <FormItem>
                <FormLabel>Tutor / Coordinador</FormLabel>
                <Combobox v-model="selectedCoordinadorObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedCoordinadorObj ? selectedCoordinadorObj.label : 'Sin asignar' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                        <ComboboxInput placeholder="Buscar tutor..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron coordinadores.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                :value="{ value: '', label: 'Sin asignar' }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                Sin asignar
                                <ComboboxItemIndicator>
                                    <Check class="h-4 w-4 text-indigo-650" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem
                                v-for="c in coordinadores"
                                :key="c.id"
                                :value="{ value: c.id, label: c.name }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                {{ c.name }}
                                <ComboboxItemIndicator>
                                    <Check class="h-4 w-4 text-indigo-650" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <div class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-850">
            <Button as-child variant="outline" type="button">
                <Link :href="route('admin.carreras.index')">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                </Link>
            </Button>
            <Button type="submit" :disabled="processing" class="bg-indigo-600 text-white hover:bg-indigo-500">
                {{ carrera ? 'Guardar Cambios' : 'Crear Carrera' }}
            </Button>
        </div>
    </form>
</template>
