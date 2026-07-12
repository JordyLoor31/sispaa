<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import type { Catalogo, LaboratorioItem, Persona } from './types';

const props = defineProps<{
    laboratorio?: LaboratorioItem;
    carreras: Catalogo[];
    responsables: Persona[];
}>();

const isEdit = !!props.laboratorio;

const form = useForm({
    nombre: props.laboratorio?.nombre ?? '',
    ubicacion: props.laboratorio?.ubicacion ?? '',
    carrera_id: props.laboratorio?.carrera_id ?? ('' as number | ''),
    capacidad: props.laboratorio?.capacidad ?? (null as number | null),
    responsable_id: props.laboratorio?.responsable?.id ?? ('' as number | ''),
});

const submit = () => {
    if (isEdit && props.laboratorio) {
        form.put(route('laboratorio.laboratorios.update', props.laboratorio.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Laboratorio actualizado.'),
        });
    } else {
        form.post(route('laboratorio.laboratorios.store'), {
            onSuccess: () => toast.success('Laboratorio registrado.'),
        });
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
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Ubicación</label>
            <input v-model="form.ubicacion" type="text" placeholder="Bloque A - Piso 2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Carrera</label>
            <Select v-model="form.carrera_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="c in carreras" :key="c.id" :value="c.id">{{ c.nombre }}</SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Capacidad</label>
            <input v-model.number="form.capacidad" type="number" min="1" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Responsable</label>
            <Select v-model="form.responsable_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Sin asignar" /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="r in responsables" :key="r.id" :value="r.id">{{ r.name }}</SelectItem>
                </SelectContent>
            </Select>
        </div>
        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
