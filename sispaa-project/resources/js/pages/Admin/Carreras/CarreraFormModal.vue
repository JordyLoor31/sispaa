<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import { Button } from '@/components/ui/button';
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
                    <label class="block text-xs font-bold text-slate-500 uppercase">Tutor / Coordinador</label>
                    <select v-model="form.coordinador_id" class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350">
                        <option :value="''">Sin asignar</option>
                        <option v-for="c in coordinadores" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
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