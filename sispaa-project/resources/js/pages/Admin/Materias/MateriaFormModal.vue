<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, BookOpen, Hash, Award, Layers } from 'lucide-vue-next';
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
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';

interface CarreraItem {
    id: number;
    nombre: string;
}

interface MateriaItem {
    id: number;
    nombre: string;
    codigo: string;
    creditos: number;
    nivel: number;
    carrera_id: number;
}

const props = defineProps<{
    open: boolean;
    materia: MateriaItem | null;
    carreras: CarreraItem[];
    defaultCarreraId: string | null;
}>();

const emit = defineEmits(['update:open', 'success']);

const formSchema = toTypedSchema(
    z.object({
        carrera_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, {
            message: 'Selecciona una carrera.',
        }),
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        codigo: z.string().min(1, 'El código es obligatorio.').max(20, 'El código no puede superar los 20 caracteres.'),
        creditos: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int('Debe ser un número entero.').min(1, 'Debe ser al menos 1.'),
        nivel: z.coerce.number({ invalid_type_error: 'Ingresa un número válido.' }).int('Debe ser un número entero.').min(1, 'Debe ser al menos 1.').max(10, 'No puede superar 10.'),
    }),
);

const { handleSubmit, setErrors, defineField, resetForm } = useForm({
    validationSchema: formSchema,
    initialValues: {
        carrera_id: '',
        nombre: '',
        codigo: '',
        creditos: 1,
        nivel: 1,
    },
});

const [carreraId] = defineField('carrera_id');

const selectedCarreraObj = ref<{ value: string | number; label: string } | null>(null);
const processing = ref(false);

watch(
    () => carreraId.value,
    (newVal) => {
        if (!newVal || newVal === '') {
            selectedCarreraObj.value = null;
        } else {
            const carrera = props.carreras.find((c) => c.id === Number(newVal));
            selectedCarreraObj.value = carrera ? { value: carrera.id, label: carrera.nombre } : null;
        }
    },
    { immediate: true },
);

watch(selectedCarreraObj, (newVal) => {
    carreraId.value = newVal ? newVal.value : '';
});

watch(
    () => props.open,
    (newVal) => {
        if (!newVal) return;

        if (props.materia) {
            resetForm({
                values: {
                    carrera_id: props.materia.carrera_id,
                    nombre: props.materia.nombre,
                    codigo: props.materia.codigo,
                    creditos: props.materia.creditos,
                    nivel: props.materia.nivel,
                },
            });
        } else {
            resetForm({
                values: {
                    carrera_id: props.defaultCarreraId && props.defaultCarreraId !== 'all' ? props.defaultCarreraId : '',
                    nombre: '',
                    codigo: '',
                    creditos: 1,
                    nivel: 1,
                },
            });
        }
    },
);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const options = {
        onSuccess: () => {
            emit('update:open', false);
            emit('success');
        },
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (props.materia) {
        router.put(route('admin.materias.update', props.materia.id), values, options);
    } else {
        router.post(route('admin.materias.store'), values, options);
    }
});
</script>

<template>
    <AlertDialog :open="open" @update:open="(val) => emit('update:open', val)">
        <AlertDialogContent class="w-full max-w-md rounded-2xl border p-6 shadow-xl bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
            <AlertDialogHeader class="flex items-center justify-between border-b pb-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                <div class="flex w-full flex-col gap-1 text-left">
                    <AlertDialogTitle class="text-lg font-bold text-[var(--sispaa-text)]">
                        {{ materia ? 'Editar Asignatura' : 'Nueva Asignatura' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-xs opacity-60 text-[var(--sispaa-text)]">
                        Complete los datos para configurar la asignatura de la malla.
                    </AlertDialogDescription>
                </div>
            </AlertDialogHeader>

            <form class="mt-4 space-y-4" @submit="onSubmit">
                <FormField v-slot="{ errorMessage }" name="carrera_id">
                    <FormItem>
                        <FormLabel>Carrera *</FormLabel>
                        <Combobox v-model="selectedCarreraObj" by="value">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button type="button" variant="outline" class="mt-1 w-full justify-between text-left text-sm font-normal">
                                        {{ selectedCarreraObj ? selectedCarreraObj.label : 'Seleccionar Carrera...' }}
                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>

                            <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron carreras.</ComboboxEmpty>
                                <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                    <ComboboxItem
                                        v-for="c in carreras"
                                        :key="c.id"
                                        :value="{ value: c.id, label: c.nombre }"
                                        class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                                    >
                                        {{ c.nombre }}
                                        <ComboboxItemIndicator>
                                            <Check class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <p v-if="errorMessage" class="mt-1 text-sm font-medium text-destructive">{{ errorMessage }}</p>
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="nombre">
                    <FormItem>
                        <FormLabel>Nombre de la Asignatura *</FormLabel>
                        <FormControl>
                            <InputGroup>
                                <InputGroupAddon><BookOpen class="h-4 w-4" /></InputGroupAddon>
                                <InputGroupInput type="text" v-bind="componentField" />
                            </InputGroup>
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="codigo">
                    <FormItem>
                        <FormLabel>Código de Asignatura *</FormLabel>
                        <FormControl>
                            <InputGroup>
                                <InputGroupAddon><Hash class="h-4 w-4" /></InputGroupAddon>
                                <InputGroupInput type="text" v-bind="componentField" />
                            </InputGroup>
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <FormField v-slot="{ componentField }" name="creditos">
                        <FormItem>
                            <FormLabel>Créditos *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><Award class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="number" min="1" v-bind="componentField" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="nivel">
                        <FormItem>
                            <FormLabel>Nivel / Semestre *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><Layers class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="number" min="1" max="10" v-bind="componentField" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="mt-6 flex flex-col-reverse justify-end gap-3 border-t pt-4 sm:flex-row border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <Button type="button" variant="outline" @click="emit('update:open', false)"> Cancelar </Button>
                    <Button type="submit" :disabled="processing" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        {{ materia ? 'Guardar Cambios' : 'Crear Asignatura' }}
                    </Button>
                </div>
            </form>
        </AlertDialogContent>
    </AlertDialog>
</template>
