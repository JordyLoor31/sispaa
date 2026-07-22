<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, ArrowLeft, CalendarRange, Clock } from 'lucide-vue-next';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import type { EstadoPeriodo, Periodo } from './columns';

const props = defineProps<{
    periodo?: Periodo | null;
}>();

const ESTADO_LABELS: Record<EstadoPeriodo, string> = {
    planificado: 'Planificado',
    activo: 'Activo',
    finalizado: 'Finalizado',
};

const formSchema = toTypedSchema(
    z.object({
        nombre: z
            .string({ required_error: 'El nombre es obligatorio.' })
            .min(1, 'El nombre es obligatorio.')
            .max(255, 'El nombre no puede superar los 255 caracteres.'),
        fecha_inicio: z.string({ required_error: 'La fecha de inicio es obligatoria.' }).min(1, 'La fecha de inicio es obligatoria.'),
        fecha_fin: z.string({ required_error: 'La fecha de fin es obligatoria.' }).min(1, 'La fecha de fin es obligatoria.'),
        tipo: z.enum(['semestral', 'anual']),
        estado: z.enum(['planificado', 'activo', 'finalizado']),
        fecha_limite_silabo: z.string().optional().nullable(),
        fecha_limite_informe: z.string().optional().nullable(),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.periodo?.nombre ?? '',
        fecha_inicio: props.periodo?.fecha_inicio ?? '',
        fecha_fin: props.periodo?.fecha_fin ?? '',
        tipo: (props.periodo?.tipo as 'semestral' | 'anual') ?? 'semestral',
        estado: props.periodo?.estado ?? 'planificado',
        fecha_limite_silabo: props.periodo?.fecha_limite_silabo ?? '',
        fecha_limite_informe: props.periodo?.fecha_limite_informe ?? '',
    },
});

const [fechaInicio] = defineField('fecha_inicio');
const [fechaFin] = defineField('fecha_fin');
const [fechaLimiteSilabo] = defineField('fecha_limite_silabo');
const [fechaLimiteInforme] = defineField('fecha_limite_informe');
const [tipoField] = defineField('tipo');
const [estadoField] = defineField('estado');

const processing = ref(false);

const selectedTipoObj = ref<{ value: 'semestral' | 'anual'; label: string } | null>(null);
watch(() => tipoField.value, (newVal) => {
    if (newVal) selectedTipoObj.value = { value: newVal, label: newVal.charAt(0).toUpperCase() + newVal.slice(1) };
}, { immediate: true });
watch(selectedTipoObj, (newVal) => {
    if (newVal) tipoField.value = newVal.value;
});

const selectedEstadoObj = ref<{ value: EstadoPeriodo; label: string } | null>(null);
watch(() => estadoField.value, (newVal) => {
    if (newVal) selectedEstadoObj.value = { value: newVal, label: ESTADO_LABELS[newVal] };
}, { immediate: true });
watch(selectedEstadoObj, (newVal) => {
    if (newVal) estadoField.value = newVal.value;
});

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const payload = {
        ...values,
        fecha_limite_silabo: values.fecha_limite_silabo || null,
        fecha_limite_informe: values.fecha_limite_informe || null,
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

    if (props.periodo) {
        router.put(route('admin.periodos.update', props.periodo.id), payload, options);
    } else {
        router.post(route('admin.periodos.store'), payload, options);
    }
});
</script>

<template>
    <form class="w-full max-w-xl space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre del Periodo * (Ej: 2026-1)</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><CalendarRange class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-2 gap-4">
            <FormField name="fecha_inicio">
                <FormItem>
                    <FormLabel>Fecha Inicio *</FormLabel>
                    <FormControl>
                        <input v-model="fechaInicio" type="date" class="flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] focus:ring-1 focus:ring-[color:var(--sispaa-primary)] focus:border-[color:var(--sispaa-primary)]" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="fecha_fin">
                <FormItem>
                    <FormLabel>Fecha Fin *</FormLabel>
                    <FormControl>
                        <input v-model="fechaFin" type="date" class="flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] focus:ring-1 focus:ring-[color:var(--sispaa-primary)] focus:border-[color:var(--sispaa-primary)]" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <FormField v-if="!periodo" v-slot="{ errorMessage }" name="tipo">
            <FormItem>
                <FormLabel>Tipo *</FormLabel>
                <Combobox v-model="selectedTipoObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedTipoObj ? selectedTipoObj.label : 'Semestral' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[200px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron tipos.</ComboboxEmpty>
                        <ComboboxGroup class="p-1">
                            <ComboboxItem :value="{ value: 'semestral', label: 'Semestral' }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                                Semestral
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem :value="{ value: 'anual', label: 'Anual' }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                                Anual
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-else v-slot="{ errorMessage }" name="estado">
            <FormItem>
                <FormLabel>Estado *</FormLabel>
                <Combobox v-model="selectedEstadoObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedEstadoObj ? selectedEstadoObj.label : 'Planificado' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>

                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[200px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron estados.</ComboboxEmpty>
                        <ComboboxGroup class="p-1">
                            <ComboboxItem
                                v-for="opt in (['planificado', 'activo', 'finalizado'] as EstadoPeriodo[])"
                                :key="opt"
                                :value="{ value: opt, label: ESTADO_LABELS[opt] }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                {{ ESTADO_LABELS[opt] }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <p class="mt-1 text-[11px] opacity-60 text-[var(--sispaa-text)]">
                    Al activar este periodo, cualquier otro periodo activo pasará automáticamente a Finalizado.
                </p>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-1 gap-4 border-t pt-4 sm:grid-cols-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
            <FormField name="fecha_limite_silabo">
                <FormItem>
                    <FormLabel class="flex items-center gap-1.5"><Clock class="h-3.5 w-3.5" /> Fecha Límite Sílabos</FormLabel>
                    <FormControl>
                        <input v-model="fechaLimiteSilabo" type="date" class="flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] focus:ring-1 focus:ring-[color:var(--sispaa-primary)] focus:border-[color:var(--sispaa-primary)]" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="fecha_limite_informe">
                <FormItem>
                    <FormLabel class="flex items-center gap-1.5"><Clock class="h-3.5 w-3.5" /> Fecha Límite Informes</FormLabel>
                    <FormControl>
                        <input v-model="fechaLimiteInforme" type="date" class="flex h-9 w-full rounded-md border px-3 py-1 text-sm shadow-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] focus:ring-1 focus:ring-[color:var(--sispaa-primary)] focus:border-[color:var(--sispaa-primary)]" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <div class="flex flex-col-reverse gap-3 border-t pt-4 sm:flex-row sm:items-center sm:justify-between border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
            <Button as-child variant="outline" type="button">
                <Link :href="route('admin.periodos.index')">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                </Link>
            </Button>
            <Button type="submit" :disabled="processing" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                {{ periodo ? 'Guardar Cambios' : 'Crear Periodo' }}
            </Button>
        </div>
    </form>
</template>
