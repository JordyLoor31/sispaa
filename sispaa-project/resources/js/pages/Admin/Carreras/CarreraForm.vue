<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, ArrowLeft, GraduationCap, Hash, Palette } from 'lucide-vue-next';
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
    paletaColores?: string[];
}>();

const PALETA_DEFECTO = [
    '#6366f1', '#f59e0b', '#10b981', '#ef4444', '#0ea5e9',
    '#8b5cf6', '#ec4899', '#14b8a6', '#f97316', '#84cc16',
];

const paleta = props.paletaColores?.length ? props.paletaColores : PALETA_DEFECTO;

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
        color: z
            .string()
            .regex(/^#[0-9A-Fa-f]{6}$/, 'El color debe ser un hexadecimal válido (ej: #6366f1).'),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.carrera?.nombre ?? '',
        codigo: props.carrera?.codigo ?? '',
        coordinador_id: props.carrera?.coordinador_id ?? null,
        color: props.carrera?.color ?? paleta[0],
    },
});

const [coordinadorId] = defineField('coordinador_id');
const [colorField] = defineField('color');

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
    <form class="w-full max-w-xl space-y-6" @submit="onSubmit">
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

                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar tutor..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron coordinadores.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                :value="{ value: '', label: 'Sin asignar' }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                Sin asignar
                                <ComboboxItemIndicator>
                                    <Check class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem
                                v-for="c in coordinadores"
                                :key="c.id"
                                :value="{ value: c.id, label: c.name }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                {{ c.name }}
                                <ComboboxItemIndicator>
                                    <Check class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                </ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="color">
            <FormItem>
                <FormLabel class="flex items-center gap-1.5"><Palette class="h-3.5 w-3.5" /> Etiqueta / Color *</FormLabel>
                <FormControl>
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-for="swatch in paleta"
                            :key="swatch"
                            type="button"
                            class="h-7 w-7 rounded-full border-2 transition-transform"
                            :class="colorField === swatch ? 'scale-110 border-[var(--sispaa-text)]' : 'border-transparent hover:scale-105'"
                            :style="{ backgroundColor: swatch }"
                            :aria-label="`Color ${swatch}`"
                            @click="colorField = swatch"
                        />
                        <label class="ml-1 flex items-center gap-2 rounded-lg border px-2 py-1 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <input type="color" v-model="colorField" class="h-6 w-8 cursor-pointer border-0 bg-transparent p-0" />
                            <span class="text-xs font-mono opacity-60 text-[var(--sispaa-text)]">{{ colorField }}</span>
                        </label>
                    </div>
                </FormControl>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
            <Button as-child variant="outline" type="button">
                <Link :href="route('admin.carreras.index')">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                </Link>
            </Button>
            <Button type="submit" :disabled="processing" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                {{ carrera ? 'Guardar Cambios' : 'Crear Carrera' }}
            </Button>
        </div>
    </form>
</template>
