<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref } from 'vue';
import { Plus, X, FolderOpen, ListChecks } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { toast } from 'vue-sonner';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requisitosDraft = ref<string[]>(['']);

const formSchema = toTypedSchema(
    z.object({
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        descripcion: z.string().max(1000, 'La descripción no puede superar los 1000 caracteres.').nullable().optional(),
    }),
);

const { handleSubmit, setErrors } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: '',
        descripcion: '',
    },
});

const processing = ref(false);

const addRequisitoField = () => requisitosDraft.value.push('');
const removeRequisitoField = (index: number) => requisitosDraft.value.splice(index, 1);

const onSubmit = handleSubmit((values) => {
    const requisitos = requisitosDraft.value.map((r) => r.trim()).filter((r) => r.length > 0);

    if (requisitos.length === 0) {
        toast.error('Agrega al menos un requisito.');
        return;
    }

    processing.value = true;

    router.post(
        route('secretaria.grupos-documentos.store'),
        { ...values, requisitos },
        {
            preserveScroll: true,
            onSuccess: () => toast.success('Grupo creado y estudiantes notificados.'),
            onError: (serverErrors: Record<string, string>) => {
                setErrors(serverErrors);
                toast.error('Revisa los campos del formulario.');
            },
            onFinish: () => {
                processing.value = false;
            },
        },
    );
});
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

            <div class="max-w-xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ componentField }" name="nombre">
                        <FormItem>
                            <FormLabel>Nombre *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><FolderOpen class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="text" placeholder="Ej: Expediente SGA" v-bind="componentField" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="descripcion">
                        <FormItem>
                            <FormLabel>Descripción</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupTextarea v-bind="componentField" rows="2" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div>
                        <label class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-slate-300">Requisitos *</label>
                        <div class="space-y-2">
                            <div v-for="(_, index) in requisitosDraft" :key="index" class="flex items-center gap-2">
                                <InputGroup>
                                    <InputGroupAddon><ListChecks class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput v-model="requisitosDraft[index]" type="text" placeholder="Ej: Cédula de identidad" />
                                </InputGroup>
                                <button
                                    v-if="requisitosDraft.length > 1"
                                    type="button"
                                    class="shrink-0 rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900"
                                    @click="removeRequisitoField(index)"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                        <button type="button" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500" @click="addRequisitoField">
                            <Plus class="h-3.5 w-3.5" /> Agregar otro requisito
                        </button>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                            {{ processing ? 'Creando...' : 'Crear grupo' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
