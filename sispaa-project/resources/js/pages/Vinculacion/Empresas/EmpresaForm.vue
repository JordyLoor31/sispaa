<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import type { Empresa } from './types';

const props = defineProps<{
    empresa?: Empresa | null;
}>();

const isEditing = !!props.empresa;

const form = useForm({
    nombre: props.empresa?.nombre ?? '',
    ruc: props.empresa?.ruc ?? '',
    sector: props.empresa?.sector ?? '',
    contacto: props.empresa?.contacto ?? '',
});

const submit = () => {
    if (isEditing && props.empresa) {
        form.put(route('vinculacion.empresas.update', props.empresa.id), { preserveScroll: true });
    } else {
        form.post(route('vinculacion.empresas.store'), { preserveScroll: true });
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
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">RUC</label>
            <input v-model="form.ruc" type="text" maxlength="13" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
            <p v-if="form.errors.ruc" class="text-xs text-rose-500 mt-1">{{ form.errors.ruc }}</p>
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Sector</label>
            <input v-model="form.sector" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Contacto</label>
            <input v-model="form.contacto" type="text" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
        </div>
        <div class="flex items-center gap-2 pt-2">
            <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                {{ form.processing ? 'Guardando...' : (isEditing ? 'Guardar cambios' : 'Registrar empresa') }}
            </Button>
        </div>
    </form>
</template>
