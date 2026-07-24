<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Check, ChevronsUpDown, AlertTriangle } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { InputGroup, InputGroupTextarea } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
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
import type { CarreraOption, FaltaSemanalItem, PeriodoOption } from './types';

const props = defineProps<{
    falta?: FaltaSemanalItem | null;
    carreras: CarreraOption[];
    periodos: PeriodoOption[];
}>();

const isEditing = !!props.falta;

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        carrera_id: requiredSelect('Selecciona una carrera.'),
        periodo_id: requiredSelect('Selecciona un período.'),
        semana: z.union([z.string(), z.number()]).refine((v) => Number(v) >= 1 && Number(v) <= 16, { message: 'La semana debe estar entre 1 y 16.' }),
        cantidad_faltas: z.union([z.string(), z.number()]).refine((v) => Number(v) >= 0, { message: 'La cantidad de faltas no puede ser negativa.' }),
        observaciones: z.string().max(1000, 'Máximo 1000 caracteres.').nullable().optional(),
    }),
);

const { handleSubmit, setErrors, defineField, values } = useForm({
    validationSchema: formSchema,
    initialValues: {
        carrera_id: props.falta?.carrera.id ?? '',
        periodo_id: props.falta?.periodo.id ?? '',
        semana: props.falta?.semana ?? '',
        cantidad_faltas: props.falta?.cantidad_faltas ?? '',
        observaciones: props.falta?.observaciones ?? '',
    },
});

const [carreraId] = defineField('carrera_id');
const [periodoId] = defineField('periodo_id');

// ── Carrera (Combobox buscable) ──────────────────────────────────────────
const carreraInicial = props.carreras.find((c) => c.id === props.falta?.carrera.id);
const selectedCarreraObj = ref<{ value: number; label: string } | null>(
    carreraInicial ? { value: carreraInicial.id, label: carreraInicial.nombre } : null,
);
watch(selectedCarreraObj, (v) => { carreraId.value = v ? v.value : ''; });

// ── Período (Combobox) ────────────────────────────────────────────────────
const periodoInicial = props.periodos.find((p) => p.id === props.falta?.periodo.id);
const selectedPeriodoObj = ref<{ value: number; label: string } | null>(
    periodoInicial ? { value: periodoInicial.id, label: periodoInicial.nombre } : null,
);
watch(selectedPeriodoObj, (v) => { periodoId.value = v ? v.value : ''; });

const processing = ref(false);

const onSubmit = handleSubmit((submitValues) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing && props.falta) {
        router.put(route('secretaria.faltas-semanales.update', props.falta.id), submitValues, options);
    } else {
        router.post(route('secretaria.faltas-semanales.store'), submitValues, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ errorMessage }" name="carrera_id">
            <FormItem>
                <FormLabel>Carrera *</FormLabel>
                <Combobox v-model="selectedCarreraObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedCarreraObj ? selectedCarreraObj.label : 'Selecciona una carrera...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[280px] rounded-lg shadow-lg bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron carreras.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="c in carreras"
                                :key="c.id"
                                :value="{ value: c.id, label: c.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]"
                            >
                                {{ c.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage">{{ errorMessage }}</FormMessage>
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="periodo_id">
            <FormItem>
                <FormLabel>Período Académico *</FormLabel>
                <Combobox v-model="selectedPeriodoObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedPeriodoObj ? selectedPeriodoObj.label : 'Selecciona un período...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg shadow-lg bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar período..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron períodos.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="p in periodos"
                                :key="p.id"
                                :value="{ value: p.id, label: p.estado === 'activo' ? `${p.nombre} (activo)` : p.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]"
                            >
                                {{ p.nombre }}
                                <span v-if="p.estado === 'activo'" class="ml-1 text-xs text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]">(activo)</span>
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage">{{ errorMessage }}</FormMessage>
            </FormItem>
        </FormField>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <FormField v-slot="{ componentField }" name="semana">
                <FormItem>
                    <FormLabel>Semana (1-16) *</FormLabel>
                    <FormControl>
                        <Input type="number" min="1" max="16" placeholder="Ej. 3" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="cantidad_faltas">
                <FormItem>
                    <FormLabel>Cantidad de Faltas *</FormLabel>
                    <FormControl>
                        <Input type="number" min="0" placeholder="Ej. 12" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <FormField v-slot="{ componentField }" name="observaciones">
            <FormItem>
                <FormLabel>Observaciones (opcional)</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupTextarea placeholder="Notas adicionales sobre este registro..." rows="3" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="flex items-center gap-2 pt-2">
            <Button
                type="submit"
                :disabled="processing || !values.carrera_id || !values.periodo_id || !values.semana || values.cantidad_faltas === '' "
                class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] disabled:opacity-50"
            >
                <AlertTriangle class="h-4 w-4 mr-1.5" />
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Crear registro' }}
            </Button>
        </div>
    </form>
</template>
