<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import type { Catalogo, EquipoItem } from './types';

const props = defineProps<{
    equipo?: EquipoItem;
    laboratorios: Catalogo[];
}>();

const isEdit = !!props.equipo;

const form = useForm({
    laboratorio_id: props.equipo?.laboratorio_id ?? ('' as number | ''),
    nombre: props.equipo?.nombre ?? '',
    codigo: props.equipo?.codigo ?? '',
    cantidad: props.equipo?.cantidad ?? 1,
});

const submit = () => {
    if (isEdit && props.equipo) {
        form.put(route('laboratorio.equipos.update', props.equipo.id), {
            preserveScroll: true,
            onSuccess: () => toast.success('Equipo actualizado.'),
        });
    } else {
        form.post(route('laboratorio.equipos.store'), {
            onSuccess: () => toast.success('Equipo registrado.'),
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-5">
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Laboratorio *</label>
            <Select v-model="form.laboratorio_id">
                <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="l in laboratorios" :key="l.id" :value="l.id">{{ l.nombre }}</SelectItem>
                </SelectContent>
            </Select>
            <p v-if="form.errors.laboratorio_id" class="text-xs text-rose-500 mt-1">{{ form.errors.laboratorio_id }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
            <input v-model="form.nombre" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Código *</label>
            <input v-model="form.codigo" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.codigo" class="text-xs text-rose-500 mt-1">{{ form.errors.codigo }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Cantidad *</label>
            <input v-model.number="form.cantidad" type="number" min="1" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.cantidad" class="text-xs text-rose-500 mt-1">{{ form.errors.cantidad }}</p>
        </div>
        <div class="flex justify-end pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                {{ form.processing ? 'Guardando...' : 'Guardar' }}
            </Button>
        </div>
    </form>
</template>
