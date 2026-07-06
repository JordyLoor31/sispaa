<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch, computed, ref } from 'vue';
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

interface Coordinator {
    id: number;
    name: string;
}

interface CarreraItem {
    id: number;
    nombre: string;
    codigo: string;
    coordinador_id: number | null;
}

const props = defineProps<{
    open: boolean;
    carrera: CarreraItem | null;
    coordinadores: Coordinator[];
}>();

const emit = defineEmits(['update:open', 'success']);

const form = useForm({
    nombre: '',
    codigo: '',
    coordinador_id: null as number | null | string,
});

const selectedCoordinadorObj = ref<{ value: string | number, label: string } | null>(null);

watch(() => form.coordinador_id, (newVal) => {
    if (!newVal || newVal === '') {
        selectedCoordinadorObj.value = { value: '', label: 'Sin asignar' };
    } else {
        const coord = props.coordinadores.find(c => c.id === Number(newVal));
        if (coord) {
            selectedCoordinadorObj.value = { value: coord.id, label: coord.name };
        } else {
            selectedCoordinadorObj.value = { value: '', label: 'Sin asignar' };
        }
    }
}, { immediate: true });

watch(selectedCoordinadorObj, (newVal) => {
    form.coordinador_id = newVal ? newVal.value : '';
});

watch(() => props.open, (newVal) => {
    if (newVal) {
        if (props.carrera) {
            form.nombre = props.carrera.nombre;
            form.codigo = props.carrera.codigo;
            form.coordinador_id = props.carrera.coordinador_id || '';
        } else {
            form.reset();
        }
        form.clearErrors();
    }
});

const submit = () => {
    const payloadCoordinator = form.coordinador_id === '' ? null : form.coordinador_id;
    
    form.transform((data) => ({
        ...data,
        coordinador_id: payloadCoordinator
    }));

    if (props.carrera) {
        form.put(route('admin.carreras.update', props.carrera.id), {
            onSuccess: () => {
                emit('update:open', false);
                emit('success');
            }
        });
    } else {
        form.post(route('admin.carreras.store'), {
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
                        {{ carrera ? 'Editar Carrera' : 'Crear Carrera' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription class="text-xs text-slate-400">
                        Ingrese o actualice la información del programa académico.
                    </AlertDialogDescription>
                </div>
            </AlertDialogHeader>

            <form @submit.prevent="submit" class="mt-4 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase">Nombre de Carrera *</label>
                    <input v-model="form.nombre" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                    <div v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase">Código / Sigla *</label>
                    <input v-model="form.codigo" type="text" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350" />
                    <div v-if="form.errors.codigo" class="text-xs text-rose-500 mt-1">{{ form.errors.codigo }}</div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Tutor / Coordinador</label>
                    <Combobox v-model="selectedCoordinadorObj" by="value">
                        <ComboboxAnchor as-child>
                            <ComboboxTrigger as-child>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal border-slate-200 focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350 mt-1">
                                    {{ selectedCoordinadorObj ? selectedCoordinadorObj.label : 'Sin asignar' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </ComboboxTrigger>
                        </ComboboxAnchor>

                        <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] bg-white dark:bg-slate-950 border border-slate-100 dark:border-slate-900 rounded-lg shadow-lg">
                            <ComboboxInput placeholder="Buscar tutor..." class="w-full border-0 border-b border-slate-105 dark:border-slate-850 bg-transparent text-sm focus:ring-0 py-2.5 px-3" />
                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron coordinadores.</ComboboxEmpty>
                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                <ComboboxItem :value="{ value: '', label: 'Sin asignar' }" class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800">
                                    Sin asignar
                                    <ComboboxItemIndicator>
                                        <Check class="h-4 w-4 text-indigo-650" />
                                    </ComboboxItemIndicator>
                                </ComboboxItem>
                                <ComboboxItem
                                    v-for="c in coordinadores"
                                    :key="c.id"
                                    :value="{ value: c.id, label: c.name }"
                                    class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-900 data-[state=checked]:bg-slate-100 dark:data-[state=checked]:bg-slate-800"
                                >
                                    {{ c.name }}
                                    <ComboboxItemIndicator>
                                        <Check class="h-4 w-4 text-indigo-650" />
                                    </ComboboxItemIndicator>
                                </ComboboxItem>
                            </ComboboxGroup>
                        </ComboboxList>
                    </Combobox>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4 mt-6 dark:border-slate-850">
                    <Button type="button" variant="outline" @click="emit('update:open', false)">
                        Cancelar
                    </Button>
                    <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                        {{ carrera ? 'Guardar Cambios' : 'Crear Carrera' }}
                    </Button>
                </div>
            </form>
        </AlertDialogContent>
    </AlertDialog>
</template>