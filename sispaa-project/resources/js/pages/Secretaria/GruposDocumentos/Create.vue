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
import { FORMATOS_DOCUMENTO } from '@/lib/formatos-documento';

defineProps<{
    breadcrumbs?: BreadcrumbItemType[];
}>();

interface RequisitoDraft {
    nombre: string;
    formatos: string[];
}

const requisitosDraft = ref<RequisitoDraft[]>([{ nombre: '', formatos: [] }]);

const toggleFormato = (requisito: RequisitoDraft, ext: string) => {
    const idx = requisito.formatos.indexOf(ext);
    if (idx >= 0) {
        requisito.formatos.splice(idx, 1);
    } else {
        requisito.formatos.push(ext);
    }
};

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

const addRequisitoField = () => requisitosDraft.value.push({ nombre: '', formatos: [] });
const removeRequisitoField = (index: number) => requisitosDraft.value.splice(index, 1);

const onSubmit = handleSubmit((values) => {
    const requisitos = requisitosDraft.value.map((r) => r.nombre.trim()).filter((r) => r.length > 0);
    const requisitosFormatos = requisitosDraft.value
        .filter((r) => r.nombre.trim().length > 0)
        .map((r) => (r.formatos.length > 0 ? r.formatos : null));

    if (requisitos.length === 0) {
        toast.error('Agrega al menos un requisito.');
        return;
    }

    processing.value = true;

    router.post(
        route('secretaria.grupos-documentos.store'),
        { ...values, requisitos, requisitos_formatos: requisitosFormatos },
        {
            preserveScroll: true,
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Nuevo Grupo de Documentos</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Todos los estudiantes activos serán notificados al crearlo.
                </p>
            </div>

            <div class="max-w-5xl w-full mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
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
                        <label class="mb-1.5 block text-sm font-semibold text-[var(--sispaa-text)]">Requisitos *</label>
                        <div class="space-y-3">
                            <div v-for="(requisito, index) in requisitosDraft" :key="index" class="rounded-xl border p-3 space-y-2 border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]">
                                <div class="flex items-center gap-2">
                                    <InputGroup>
                                        <InputGroupAddon><ListChecks class="h-4 w-4" /></InputGroupAddon>
                                        <InputGroupInput v-model="requisito.nombre" type="text" placeholder="Ej: Cédula de identidad" />
                                    </InputGroup>
                                    <button
                                        v-if="requisitosDraft.length > 1"
                                        type="button"
                                        class="shrink-0 rounded-lg p-2 opacity-50 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]"
                                        @click="removeRequisitoField(index)"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 pl-1">
                                    <span class="text-xs font-semibold opacity-60 text-[var(--sispaa-text)]">Formatos permitidos:</span>
                                    <label
                                        v-for="f in FORMATOS_DOCUMENTO"
                                        :key="f.ext"
                                        class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold transition-colors"
                                        :class="requisito.formatos.includes(f.ext)
                                            ? 'border-[var(--sispaa-primary)] text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)]'
                                            : 'opacity-50 text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] hover:opacity-100'"
                                    >
                                        <input type="checkbox" class="hidden" :checked="requisito.formatos.includes(f.ext)" @change="toggleFormato(requisito, f.ext)" />
                                        {{ f.label }}
                                    </label>
                                    <span v-if="requisito.formatos.length === 0" class="text-xs opacity-50 text-[var(--sispaa-text)]">(todos: PDF, JPG, PNG, JPEG)</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80" @click="addRequisitoField">
                            <Plus class="h-3.5 w-3.5" /> Agregar otro requisito
                        </button>
                    </div>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ processing ? 'Creando...' : 'Crear grupo' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
