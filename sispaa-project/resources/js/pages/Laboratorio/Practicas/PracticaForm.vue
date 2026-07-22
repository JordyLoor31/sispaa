<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, Hash, Calendar, Clock, BookOpen, Layers, Award, GraduationCap, Users2 } from 'lucide-vue-next';
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
import type { Catalogo, PracticaEditItem } from './types';

const props = defineProps<{
    practica?: PracticaEditItem;
    materias: Catalogo[];
    laboratorios: Catalogo[];
    periodos: Catalogo[];
    equiposCatalogo: Catalogo[];
    reactivosCatalogo: Catalogo[];
}>();

const isEdit = !!props.practica;

const usoItemSchema = z.object({ id: z.number(), cantidad_usada: z.number().int().min(1) });

const formSchema = toTypedSchema(
    z.object({
        materia_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message: 'Selecciona una materia.' }),
        periodo_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message: 'Selecciona un período.' }),
        laboratorio_id: z.union([z.string(), z.number()]).nullable(),
        numero_practica: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int().min(1, 'Debe ser al menos 1.'),
        fecha: z.string().min(1, 'La fecha es obligatoria.'),
        tema: z.string().min(1, 'El tema es obligatorio.').max(255, 'El tema no puede superar los 255 caracteres.'),
        subtema: z.string().max(255).nullable().optional(),
        logro_aprendizaje: z.string().max(255).nullable().optional(),
        semestre: z.string().max(50).nullable().optional(),
        numero_estudiantes: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int().min(0).nullable().optional(),
        horario: z.string().max(100).nullable().optional(),
        objetivo: z.string().max(2000).nullable().optional(),
        metodologia: z.string().max(2000).nullable().optional(),
        resultados: z.string().max(2000).nullable().optional(),
        conclusiones: z.string().max(2000).nullable().optional(),
        equipos: z.array(usoItemSchema),
        reactivos: z.array(usoItemSchema),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        materia_id: props.practica?.materia_id ?? '',
        periodo_id: props.practica?.periodo_id ?? '',
        laboratorio_id: props.practica?.laboratorio_id ?? '',
        numero_practica: props.practica?.numero_practica ?? 1,
        fecha: props.practica?.fecha ?? '',
        tema: props.practica?.tema ?? '',
        subtema: props.practica?.subtema ?? '',
        logro_aprendizaje: props.practica?.logro_aprendizaje ?? '',
        semestre: props.practica?.semestre ?? '',
        numero_estudiantes: props.practica?.numero_estudiantes ?? null,
        horario: props.practica?.horario ?? '',
        objetivo: props.practica?.objetivo ?? '',
        metodologia: props.practica?.metodologia ?? '',
        resultados: props.practica?.resultados ?? '',
        conclusiones: props.practica?.conclusiones ?? '',
        equipos: props.practica?.equipos ?? [],
        reactivos: props.practica?.reactivos ?? [],
    },
});

const [equipos] = defineField('equipos');
const [reactivos] = defineField('reactivos');
const [materiaId] = defineField('materia_id');
const [periodoId] = defineField('periodo_id');
const [laboratorioId] = defineField('laboratorio_id');

const toggleEquipo = (id: number, checked: boolean) => {
    if (checked) {
        equipos.value = [...(equipos.value ?? []), { id, cantidad_usada: 1 }];
    } else {
        equipos.value = (equipos.value ?? []).filter((e) => e.id !== id);
    }
};
const toggleReactivo = (id: number, checked: boolean) => {
    if (checked) {
        reactivos.value = [...(reactivos.value ?? []), { id, cantidad_usada: 1 }];
    } else {
        reactivos.value = (reactivos.value ?? []).filter((r) => r.id !== id);
    }
};
const equipoChecked = (id: number) => (equipos.value ?? []).some((e) => e.id === id);
const reactivoChecked = (id: number) => (reactivos.value ?? []).some((r) => r.id === id);

const materiaInicial = props.materias.find((m) => m.id === props.practica?.materia_id);
const selectedMateriaObj = ref<{ value: string | number; label: string } | null>(
    materiaInicial ? { value: materiaInicial.id, label: materiaInicial.nombre } : null,
);
watch(selectedMateriaObj, (newVal) => {
    materiaId.value = newVal ? newVal.value : '';
});

const periodoInicial = props.periodos.find((p) => p.id === props.practica?.periodo_id);
const selectedPeriodoObj = ref<{ value: string | number; label: string } | null>(
    periodoInicial ? { value: periodoInicial.id, label: periodoInicial.nombre } : null,
);
watch(selectedPeriodoObj, (newVal) => {
    periodoId.value = newVal ? newVal.value : '';
});

const laboratorioInicial = props.laboratorios.find((l) => l.id === props.practica?.laboratorio_id);
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
        onSuccess: () => toast.success(isEdit ? 'Práctica actualizada.' : 'Práctica registrada.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEdit && props.practica) {
        router.put(route('laboratorio.practicas.update', props.practica.id), values, options);
    } else {
        router.post(route('laboratorio.practicas.store'), values, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField v-slot="{ errorMessage }" name="materia_id">
                <FormItem>
                    <FormLabel>Materia *</FormLabel>
                    <Combobox v-model="selectedMateriaObj" by="value">
                        <ComboboxAnchor as-child>
                            <ComboboxTrigger as-child>
                                <FormControl>
                                    <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                        {{ selectedMateriaObj ? selectedMateriaObj.label : 'Selecciona...' }}
                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </FormControl>
                            </ComboboxTrigger>
                        </ComboboxAnchor>
                        <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <ComboboxInput placeholder="Buscar materia..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron materias.</ComboboxEmpty>
                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                <ComboboxItem
                                    v-for="m in materias"
                                    :key="m.id"
                                    :value="{ value: m.id, label: m.nombre }"
                                    class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"
                                >
                                    {{ m.nombre }}
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
                                        {{ selectedPeriodoObj ? selectedPeriodoObj.label : 'Selecciona...' }}
                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </FormControl>
                            </ComboboxTrigger>
                        </ComboboxAnchor>
                        <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                            <ComboboxInput placeholder="Buscar período..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron períodos.</ComboboxEmpty>
                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                <ComboboxItem
                                    v-for="per in periodos"
                                    :key="per.id"
                                    :value="{ value: per.id, label: per.nombre }"
                                    class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"
                                >
                                    {{ per.nombre }}
                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                </ComboboxItem>
                            </ComboboxGroup>
                        </ComboboxList>
                    </Combobox>
                    <FormMessage v-if="errorMessage" />
                </FormItem>
            </FormField>

            <FormField v-slot="{ errorMessage }" name="laboratorio_id">
                <FormItem>
                    <FormLabel>Laboratorio</FormLabel>
                    <Combobox v-model="selectedLaboratorioObj" by="value">
                        <ComboboxAnchor as-child>
                            <ComboboxTrigger as-child>
                                <FormControl>
                                    <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                        {{ selectedLaboratorioObj ? selectedLaboratorioObj.label : 'Sin asignar' }}
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
                                    :value="{ value: '', label: 'Sin asignar' }"
                                    class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"
                                >
                                    Sin asignar
                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                </ComboboxItem>
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

            <FormField v-slot="{ componentField }" name="numero_practica">
                <FormItem>
                    <FormLabel>N° de práctica *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Hash class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="number" min="1" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="fecha">
                <FormItem>
                    <FormLabel>Fecha *</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Calendar class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="date" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="horario">
                <FormItem>
                    <FormLabel>Horario</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Clock class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" placeholder="Ej: 08:00 - 10:00" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <FormField v-slot="{ componentField }" name="tema">
            <FormItem>
                <FormLabel>Tema *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><BookOpen class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormField v-slot="{ componentField }" name="subtema">
                <FormItem>
                    <FormLabel>Subtema</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Layers class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="logro_aprendizaje">
                <FormItem>
                    <FormLabel>Logro de aprendizaje</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Award class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="semestre">
                <FormItem>
                    <FormLabel>Semestre</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><GraduationCap class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="text" placeholder="Ej: Quinto semestre" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="numero_estudiantes">
                <FormItem>
                    <FormLabel>N° de estudiantes</FormLabel>
                    <FormControl>
                        <InputGroup>
                            <InputGroupAddon><Users2 class="h-4 w-4" /></InputGroupAddon>
                            <InputGroupInput type="number" min="0" v-bind="componentField" />
                        </InputGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <FormField v-slot="{ componentField }" name="objetivo">
            <FormItem>
                <FormLabel>Objetivo</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupTextarea v-bind="componentField" rows="2" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="metodologia">
            <FormItem>
                <FormLabel>Metodología</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupTextarea v-bind="componentField" rows="2" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="resultados">
            <FormItem>
                <FormLabel>Resultados</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupTextarea v-bind="componentField" rows="2" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField v-slot="{ componentField }" name="conclusiones">
            <FormItem>
                <FormLabel>Conclusiones</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupTextarea v-bind="componentField" rows="2" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <div>
            <label class="mb-2 block text-sm font-semibold text-[var(--sispaa-text)]">Equipos usados</label>
            <div class="grid max-h-40 grid-cols-1 gap-2 overflow-y-auto rounded-lg p-3 sm:grid-cols-2 border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                <label v-for="e in equiposCatalogo" :key="e.id" class="flex items-center gap-2 text-xs text-[var(--sispaa-text)]">
                    <input type="checkbox" :checked="equipoChecked(e.id)" class="rounded accent-[var(--sispaa-primary)]" @change="toggleEquipo(e.id, ($event.target as HTMLInputElement).checked)" />
                    {{ e.nombre }}
                </label>
                <p v-if="equiposCatalogo.length === 0" class="col-span-full text-xs opacity-50 text-[var(--sispaa-text)]">No hay equipos registrados.</p>
            </div>
        </div>
        <div>
            <label class="mb-2 block text-sm font-semibold text-[var(--sispaa-text)]">Reactivos usados</label>
            <div class="grid max-h-40 grid-cols-1 gap-2 overflow-y-auto rounded-lg p-3 sm:grid-cols-2 border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                <label v-for="r in reactivosCatalogo" :key="r.id" class="flex items-center gap-2 text-xs text-[var(--sispaa-text)]">
                    <input type="checkbox" :checked="reactivoChecked(r.id)" class="rounded accent-[var(--sispaa-primary)]" @change="toggleReactivo(r.id, ($event.target as HTMLInputElement).checked)" />
                    {{ r.nombre }}
                </label>
                <p v-if="reactivosCatalogo.length === 0" class="col-span-full text-xs opacity-50 text-[var(--sispaa-text)]">No hay reactivos registrados.</p>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                {{ processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
