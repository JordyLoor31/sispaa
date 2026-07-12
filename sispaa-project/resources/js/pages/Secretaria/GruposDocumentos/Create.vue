<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requisitosDraft = ref<string[]>(['']);

const form = useForm({
    nombre: '',
    descripcion: '',
    requisitos: [] as string[],
});

const addRequisitoField = () => requisitosDraft.value.push('');
const removeRequisitoField = (index: number) => requisitosDraft.value.splice(index, 1);

const submit = () => {
    form.requisitos = requisitosDraft.value.map((r) => r.trim()).filter((r) => r.length > 0);

    if (form.requisitos.length === 0) {
        toast.error('Agrega al menos un requisito.');
        return;
    }

    form.post(route('secretaria.grupos-documentos.store'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Grupo creado y estudiantes notificados.'),
        onError: () => toast.error('Revisa los campos del formulario.'),
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Grupo de Documentos" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Nuevo Grupo de Documentos</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Todos los estudiantes activos serán notificados al crearlo.
                </p>
            </div>

            <div class="max-w-xl rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Nombre *</label>
                        <input v-model="form.nombre" type="text" placeholder="Ej: Expediente SGA" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                        <p v-if="form.errors.nombre" class="text-xs text-rose-500 mt-1">{{ form.errors.nombre }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Descripción</label>
                        <textarea v-model="form.descripcion" rows="2" class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Requisitos *</label>
                        <div class="space-y-2">
                            <div v-for="(_, index) in requisitosDraft" :key="index" class="flex items-center gap-2">
                                <input v-model="requisitosDraft[index]" type="text" placeholder="Ej: Cédula de identidad"
                                    class="w-full rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                                <button type="button" @click="removeRequisitoField(index)" v-if="requisitosDraft.length > 1"
                                    class="shrink-0 rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900">
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <button type="button" @click="addRequisitoField" class="mt-2 inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-500 font-semibold">
                            <Plus class="h-3.5 w-3.5" /> Agregar otro requisito
                        </button>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="form.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                            {{ form.processing ? 'Creando...' : 'Crear grupo' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
