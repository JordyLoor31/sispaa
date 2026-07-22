<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, TestTube2, Beaker, Scale, Ruler } from 'lucide-vue-next';
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
import type { Catalogo, ReactivoItem } from './types';

const props = defineProps<{
    reactivo?: ReactivoItem;
    laboratorios: Catalogo[];
}>();

const isEdit = !!props.reactivo;

const formSchema = toTypedSchema(
    z.object({
        laboratorio_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, {
            message: 'Selecciona un laboratorio.',
        }),
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        formula: z.string().max(255, 'La fórmula no puede superar los 255 caracteres.').nullable().optional(),
        cantidad: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int('Debe ser un número entero.').min(0, 'No puede ser negativo.'),
        unidad: z.string().max(30, 'La unidad no puede superar los 30 caracteres.').nullable().optional(),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        laboratorio_id: props.reactivo?.laboratorio_id ?? '',
        nombre: props.reactivo?.nombre ?? '',
        formula: props.reactivo?.formula ?? '',
        cantidad: props.reactivo?.cantidad ?? 0,
        unidad: props.reactivo?.unidad ?? '',
    },
});

const [laboratorioId] = defineField('laboratorio_id');

const laboratorioInicial = props.laboratorios.find((l) => l.id === props.reactivo?.laboratorio_id);
const selectedLaboratorioObj = ref<{ value: string | number; label: string } | null>(
    laboratorioInicial ? { value: laboratorioInicial.id, label: laboratorioInicial.nombre } : null,
);
watch(selectedLaboratorioObj, (newVal) => {
    laboratorioId.value = newVal ? newVal.value : '';
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onSuccess: () => toast.success(isEdit ? 'Reactivo actualizado.' : 'Reactivo registrado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit && props.reactivo) {
        router.put(route('laboratorio.reactivos.update', props.reactivo.id), values, options);
    } else {
        router.post(route('laboratorio.reactivos.store'), values, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ errorMessage }" name="laboratorio_id">
            <FormItem>
                <FormLabel>Laboratorio *</FormLabel>
                <Combobox v-model="selectedLaboratorioObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedLaboratorioObj ? selectedLaboratorioObj.label : 'Selecciona...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar laboratorio..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron laboratorios.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="l in laboratorios"
                                :key="l.id"
                                :value="{ value: l.id, label: l.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"
                            >
                                {{ l.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><TestTube2 class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="formula">
            <FormItem>
                <FormLabel>Fórmula</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Beaker class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-2 gap-4">
            <FormField v-slot="{ componentField }" name="cantidad">
                <FormItem>
                    <FormLabel>Cantidad *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Scale class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="number" min="0" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="unidad">
                <FormItem>
                    <FormLabel>Unidad</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Ruler class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" placeholder="ml, gr, L..." v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                {{ processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
