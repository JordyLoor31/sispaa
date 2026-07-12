<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Convocatoria } from './types';

const props = defineProps<{
    convocatoria?: Convocatoria | null;
    modulos: string[];
}>();

const isEditing = !!props.convocatoria;

const form = useForm({
    titulo: props.convocatoria?.titulo ?? '',
    descripcion: props.convocatoria?.descripcion ?? '',
    modulo: props.convocatoria?.modulo ?? '',
    tipo_documento: props.convocatoria?.tipo_documento ?? '',
    fecha_inicio: props.convocatoria?.fecha_inicio ?? '',
    fecha_fin: props.convocatoria?.fecha_fin ?? '',
    activa: props.convocatoria?.activa ?? true,
});

const submit = () => {
    if (isEditing && props.convocatoria) {
        form.put(route('secretaria.convocatorias.update', props.convocatoria.id), { preserveScroll: true });
    } else {
        form.post(route('secretaria.convocatorias.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Título *</label>
            <input v-model="form.titulo" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.titulo" class="text-xs text-rose-500 mt-1">{{ form.errors.titulo }}</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Módulo *</label>
            <Select v-model="form.modulo">
                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un módulo..." /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="m in modulos" :key="m" :value="m">{{ m }}</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.modulo" class="text-xs text-rose-500 mt-1">{{ form.errors.modulo }}</p>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tipo de documento (opcional)</label>
            <input v-model="form.tipo_documento" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripción</label>
            <textarea v-model="form.descripcion" rows="3" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Inicio *</label>
                <input v-model="form.fecha_inicio" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                <p v-if="form.errors.fecha_inicio" class="text-xs text-rose-500 mt-1">{{ form.errors.fecha_inicio }}</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fin *</label>
                <input v-model="form.fecha_fin" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                <p v-if="form.errors.fecha_fin" class="text-xs text-rose-500 mt-1">{{ form.errors.fecha_fin }}</p>
            </div>
        </div>

        <div v-if="isEditing" class="flex items-center gap-2">
            <input id="activa" type="checkbox" v-model="form.activa" class="rounded border-slate-300" />
            <label for="activa" class="text-sm text-slate-700 dark:text-slate-300">Convocatoria activa</label>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                {{ form.processing ? 'Guardando...' : (isEditing ? 'Guardar cambios' : 'Crear convocatoria') }}
            </Button>
        </div>
    </form>
</template>
