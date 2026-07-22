<script setup lang="ts">
import { Button } from '@/components/ui/button';
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
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { Calendar, Check, ChevronsUpDown, ClipboardList } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { ref, watch } from 'vue';
import * as z from 'zod';
import type { Actividad, Catalogo } from './types';

const props = defineProps<{
    actividad?: Actividad | null;
    docentes: { id: number; name: string }[];
    carreras: Catalogo[];
    periodos: Catalogo[];
    empresas: Catalogo[];
}>();

const isEditing = !!props.actividad;

const requiredSelect = (message: string) => z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

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

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.actividad?.nombre ?? '',
        docente_lider_id: props.actividad?.docente_lider?.id ?? '',
        carrera_id: props.actividad?.carrera_id ?? '',
        periodo_id: props.actividad?.periodo_id ?? '',
        empresa_id: props.actividad?.empresa_id ?? '',
        fecha: props.actividad?.fecha ?? '',
    },
});

const [docenteLiderId] = defineField('docente_lider_id');
const [carreraId] = defineField('carrera_id');
const [periodoId] = defineField('periodo_id');
const [empresaId] = defineField('empresa_id');

const docenteInicial = props.docentes.find((d) => d.id === props.actividad?.docente_lider?.id);
const selectedDocenteObj = ref<{ value: string | number; label: string } | null>(
    docenteInicial ? { value: docenteInicial.id, label: docenteInicial.name } : null,
);
watch(selectedDocenteObj, (newVal) => {
    docenteLiderId.value = newVal ? newVal.value : '';
});

const carreraInicial = props.carreras.find((c) => c.id === props.actividad?.carrera_id);
const selectedCarreraObj = ref<{ value: string | number; label: string } | null>(
    carreraInicial ? { value: carreraInicial.id, label: carreraInicial.nombre } : null,
);
watch(selectedCarreraObj, (newVal) => {
    carreraId.value = newVal ? newVal.value : '';
});

const periodoInicial = props.periodos.find((p) => p.id === props.actividad?.periodo_id);
const selectedPeriodoObj = ref<{ value: string | number; label: string } | null>(
    periodoInicial ? { value: periodoInicial.id, label: periodoInicial.nombre } : null,
);
watch(selectedPeriodoObj, (newVal) => {
    periodoId.value = newVal ? newVal.value : '';
});

const empresaInicial = props.empresas.find((e) => e.id === props.actividad?.empresa_id);
const selectedEmpresaObj = ref<{ value: string | number; label: string } | null>(
    empresaInicial ? { value: empresaInicial.id, label: empresaInicial.nombre } : null,
);
watch(selectedEmpresaObj, (newVal) => {
    empresaId.value = newVal ? newVal.value : '';
});

const processing = ref(false);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('vinculacion.actividades'));
    }
};

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
                    <InputGroup>
                        <InputGroupAddon><ClipboardList class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" class="color-[var(--sispaa-text)]" placeholder="Nombre de la actividad" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="docente_lider_id">
            <FormItem>
                <FormLabel>Docente líder *</FormLabel>
                <Combobox v-model="selectedDocenteObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedDocenteObj ? selectedDocenteObj.label : 'Selecciona un docente...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList
                        class="w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] shadow-lg"
                    >
                        <ComboboxInput
                            placeholder="Buscar docente..."
                            class="w-full border-0 border-b border-[var(--sispaa-surface)] bg-transparent px-3 py-2.5 text-sm focus:ring-0"
                        />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron docentes.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="d in docentes"
                                :key="d.id"
                                :value="{ value: d.id, label: d.name }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                            >
                                {{ d.name }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

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
                    <ComboboxList
                        class="w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] shadow-lg"
                    >
                        <ComboboxInput
                            placeholder="Buscar carrera..."
                            class="w-full border-0 border-b border-[var(--sispaa-surface)] bg-transparent px-3 py-2.5 text-sm focus:ring-0"
                        />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="c in carreras"
                                :key="c.id"
                                :value="{ value: c.id, label: c.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                            >
                                {{ c.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="periodo_id">
            <FormItem>
                <FormLabel>Período *</FormLabel>
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
                    <ComboboxList
                        class="w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] shadow-lg"
                    >
                        <ComboboxInput
                            placeholder="Buscar período..."
                            class="w-full border-0 border-b border-[var(--sispaa-surface)] bg-transparent px-3 py-2.5 text-sm focus:ring-0"
                        />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron períodos.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="p in periodos"
                                :key="p.id"
                                :value="{ value: p.id, label: p.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                            >
                                {{ p.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="empresa_id">
            <FormItem>
                <FormLabel>Empresa (opcional)</FormLabel>
                <Combobox v-model="selectedEmpresaObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedEmpresaObj ? selectedEmpresaObj.label : 'Sin empresa asociada' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList
                        class="w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border border-[var(--sispaa-surface)] bg-[var(--sispaa-background)] shadow-lg"
                    >
                        <ComboboxInput
                            placeholder="Buscar empresa..."
                            class="w-full border-0 border-b border-[var(--sispaa-surface)] bg-transparent px-3 py-2.5 text-sm focus:ring-0"
                        />
                        <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron empresas.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                :value="{ value: '', label: 'Sin empresa asociada' }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                            >
                                Sin empresa asociada
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                            <ComboboxItem
                                v-for="e in empresas"
                                :key="e.id"
                                :value="{ value: e.id, label: e.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_18%,transparent)]"
                            >
                                {{ e.nombre }}
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <FormField v-slot="{ componentField }" name="fecha">
            <FormItem>
                <FormLabel>Fecha</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Calendar class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="date" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center">
            <Button
                type="submit"
                :disabled="processing"
                class="w-full bg-[var(--sispaa-primary)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] sm:w-auto"
            >
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Registrar actividad' }}
            </Button>
            <Button
                type="button"
                :disabled="processing"
                class="w-full bg-[var(--sispaa-accent)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-accent)_85%,black)] sm:w-auto"
                @click="goBack"
            >
                Cancelar
            </Button>
        </div>
    </form>
</template>
