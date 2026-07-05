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
                    <label class="block text-xs font-bold text-slate-500 uppercase">Carrera *</label>
                    <select v-model="form.carrera_id" required class="mt-1 w-full rounded-lg border-slate-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350">
                        <option value="" disabled>Seleccionar Carrera...</option>
                        <option v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</option>
                    </select>
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