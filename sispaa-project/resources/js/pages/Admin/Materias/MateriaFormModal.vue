<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown } from 'lucide-vue-next';
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
        <AlertDialogContent class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 shadow-xl dark:border-slate-900 dark:bg-slate-950">
            <AlertDialogHeader class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/85">
                <div class="flex w-full flex-col gap-1 text-left">
                    <AlertDialogTitle class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ materia ? 'Editar Asignatura' : 'Nueva Asignatura' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-xs text-slate-400">
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

                            <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                                <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                                <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                    <ComboboxItem
                                        v-for="c in carreras"
                                        :key="c.id"
                                        :value="{ value: c.id, label: c.nombre }"
                                        class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                                    >
                                        {{ c.nombre }}
                                        <ComboboxItemIndicator>
                                            <Check class="h-4 w-4 text-indigo-650" />
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
                            <Input type="text" v-bind="componentField" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="codigo">
                    <FormItem>
                        <FormLabel>Código de Asignatura *</FormLabel>
                        <FormControl>
                            <Input type="text" v-bind="componentField" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <div class="grid grid-cols-2 gap-4">
                    <FormField v-slot="{ componentField }" name="creditos">
                        <FormItem>
                            <FormLabel>Créditos *</FormLabel>
                            <FormControl>
                                <Input type="number" min="1" v-bind="componentField" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="nivel">
                        <FormItem>
                            <FormLabel>Nivel / Semestre *</FormLabel>
                            <FormControl>
                                <Input type="number" min="1" max="10" v-bind="componentField" />
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t border-slate-100 pt-4 dark:border-slate-850">
                    <Button type="button" variant="outline" @click="emit('update:open', false)"> Cancelar </Button>
                    <Button type="submit" :disabled="processing" class="bg-indigo-600 text-white hover:bg-indigo-500">
                        {{ materia ? 'Guardar Cambios' : 'Crear Asignatura' }}
                    </Button>
                </div>
            </form>
        </AlertDialogContent>
    </AlertDialog>
</template>
