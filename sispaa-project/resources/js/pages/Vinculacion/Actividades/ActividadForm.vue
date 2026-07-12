<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { Actividad, Catalogo } from './types';

const props = defineProps<{
    actividad?: Actividad | null;
    docentes: { id: number; name: string }[];
    carreras: Catalogo[];
    periodos: Catalogo[];
    empresas: Catalogo[];
}>();

const isEditing = !!props.actividad;

const form = useForm({
    nombre: props.actividad?.nombre ?? '',
    docente_lider_id: props.actividad?.docente_lider.id ?? ('' as number | ''),
    carrera_id: props.actividad?.carrera_id ?? ('' as number | ''),
    periodo_id: props.actividad?.periodo_id ?? ('' as number | ''),
    empresa_id: props.actividad?.empresa_id ?? ('' as number | ''),
    fecha: props.actividad?.fecha ?? '',
});

const submit = () => {
    if (isEditing && props.actividad) {
        form.put(route('vinculacion.actividades.update', props.actividad.id), { preserveScroll: true });
    } else {
        form.post(route('vinculacion.actividades.store'), { preserveScroll: true });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
            <input v-model="form.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Docente líder *</label>
            <Select v-model="form.docente_lider_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un docente..." /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="d in docentes" :key="d.id" :value="d.id">{{ d.name }}</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.docente_lider_id" class="text-xs text-rose-500 mt-1">{{ form.errors.docente_lider_id }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Carrera *</label>
            <Select v-model="form.carrera_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona una carrera..." /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.carrera_id" class="text-xs text-rose-500 mt-1">{{ form.errors.carrera_id }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Período *</label>
            <Select v-model="form.periodo_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona un período..." /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="p in periodos" :key="p.id" :value="p.id">{{ p.nombre }}</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.periodo_id" class="text-xs text-rose-500 mt-1">{{ form.errors.periodo_id }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Empresa (opcional)</label>
            <Select v-model="form.empresa_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Sin empresa asociada" /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="e in empresas" :key="e.id" :value="e.id">{{ e.nombre }}</SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Fecha</label>
            <input v-model="form.fecha" type="date" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
        </div>
        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                {{ form.processing ? 'Guardando...' : (isEditing ? 'Guardar cambios' : 'Registrar actividad') }}
            </Button>
        </div>
    </form>
</template>
