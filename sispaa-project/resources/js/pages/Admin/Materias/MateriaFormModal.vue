<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch, ref } from 'vue';
import { Button } from '@/components/ui/button';
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

const form = useForm({
    carrera_id: '' as number | string,
    nombre: '',
    codigo: '',
    creditos: 1,
    nivel: 1,
});

const selectedCarreraObj = ref<{ value: string | number, label: string } | null>(null);

watch(() => form.carrera_id, (newVal) => {
    if (!newVal || newVal === '') {
        selectedCarreraObj.value = null;
    } else {
        const carrera = props.carreras.find(c => c.id === Number(newVal));
        if (carrera) {
            selectedCarreraObj.value = { value: carrera.id, label: carrera.nombre };
        } else {
            selectedCarreraObj.value = null;
        }
    }
}, { immediate: true });

watch(selectedCarreraObj, (newVal) => {
    form.carrera_id = newVal ? newVal.value : '';
});

watch(() => props.open, (newVal) => {
    if (newVal) {
        if (props.materia) {
            form.carrera_id = props.materia.carrera_id.toString();
            form.nombre = props.materia.nombre;
            form.codigo = props.materia.codigo;
            form.creditos = props.materia.creditos;
            form.nivel = props.materia.nivel;
        } else {
            form.reset();
            if (props.defaultCarreraId && props.defaultCarreraId !== 'all') {
                form.carrera_id = props.defaultCarreraId;
            }
        }
        form.clearErrors();
    }
});

const submit = () => {
    if (props.materia) {
        form.put(route('admin.materias.update', props.materia.id), {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            }
        });
    } else {
        form.post(route('admin.materias.store'), {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            }
        });
    }
};
</script>

<template>
    <AlertDialog :open="open" @update:open="val => emit('update:open', val)">
        <AlertDialogContent class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-950 p-6 shadow-xl border border-slate-100 dark:border-slate-900">
            <AlertDialogHeader class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800/85">
                <div class="flex flex-col gap-1 text-left w-full">
                    <AlertDialogTitle class="text-lg font-bold text-slate-900 dark:text-white">
                        {{ materia ? 'Editar Asignatura' : 'Nueva Asignatura' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-xs text-slate-400">
                        Complete los datos para configurar la asignatura de la malla.
                    </AlertDialogDescription>
                </div>
            </AlertDialogHeader>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Carrera *</label>
                    <Combobox v-model="selectedCarreraObj" by="value">
                        <ComboboxAnchor as-child>
                            <ComboboxTrigger as-child>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350 mt-1">
                                    {{ selectedCarreraObj ? selectedCarreraObj.label : 'Seleccionar Carrera...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </ComboboxTrigger>
                        </ComboboxAnchor>

                        <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-lg shadow-lg">
                            <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b border-slate-105 dark:border-slate-850 bg-transparent text-sm focus:ring-0 py-2.5 px-3" />
                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                <ComboboxItem
                                    v-for="c in carreras"
                                    :key="c.id"
                                    :value="{ value: c.id, label: c.nombre }"
                                    class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800"
                                >
                                    {{ c.nombre }}
                                    <ComboboxItemIndicator>
                                        <Check class="h-4 w-4 text-indigo-650" />
                                    </ComboboxItemIndicator>
                                </ComboboxItem>
                            </ComboboxGroup>
                        </ComboboxList>
                    </Combobox>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase">Nombre de la Asignatura *</label>
                    <input v-model="form.nombre" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                    <div v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase">Código de Asignatura *</label>
                    <input v-model="form.codigo" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                    <div v-if="form.errors.codigo" class="text-xs text-rose-500 mt-1">{{ form.errors.codigo }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase">Créditos *</label>
                        <input v-model.number="form.creditos" type="number" min="1" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase">Nivel / Semestre *</label>
                        <input v-model.number="form.nivel" type="number" min="1" max="10" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6 dark:border-slate-850">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        {{ materia ? 'Guardar Cambios' : 'Crear Asignatura' }}
                    </Button>
                </div>
            </form>
        </AlertDialogContent>
    </AlertDialog>
</template>
