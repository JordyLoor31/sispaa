<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, Wrench, Hash, Boxes } from 'lucide-vue-next';
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

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        laboratorio_id: props.equipo?.laboratorio_id ?? '',
        nombre: props.equipo?.nombre ?? '',
        codigo: props.equipo?.codigo ?? '',
        cantidad: props.equipo?.cantidad ?? 1,
    },
});

const [laboratorioId] = defineField('laboratorio_id');

const laboratorioInicial = props.laboratorios.find((l) => l.id === props.equipo?.laboratorio_id);
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
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                        <ComboboxInput placeholder="Buscar laboratorio..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron laboratorios.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="l in laboratorios"
                                :key="l.id"
                                :value="{ value: l.id, label: l.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                            >
                                {{ l.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
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
                        <InputGroupAddon><Wrench class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="codigo">
            <FormItem>
                <FormLabel>Código *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Hash class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="cantidad">
            <FormItem>
                <FormLabel>Cantidad *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Boxes class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="number" min="1" v-bind="componentField" />
                    </InputGroup>
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
