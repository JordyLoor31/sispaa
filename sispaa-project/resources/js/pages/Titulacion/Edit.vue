<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupTextarea, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, Calendar } from 'lucide-vue-next';
import {
    Combobox,
    ComboboxAnchor,
    ComboboxEmpty,
    ComboboxGroup,
    ComboboxInput,
    ComboboxItem,
    ComboboxItemIndicator,
    ComboboxList,
    ComboboxTrigger,
} from '@/components/ui/combobox';
import { toast } from 'vue-sonner';
import type { Persona, Titulacion } from './columns';

const props = defineProps<{
    titulacion: Titulacion;
    tutores: Persona[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formSchema = toTypedSchema(
    z.object({
        tema: z.string().min(1, 'El tema es obligatorio.'),
        tutor_id: z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, {
            message: 'Selecciona un tutor.',
        }),
        fecha_inicio: z.string().nullable().optional(),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        tema: props.titulacion.tema,
        tutor_id: props.titulacion.tutor.id,
        fecha_inicio: props.titulacion.fecha_inicio ?? '',
    },
});

const [tutorId] = defineField('tutor_id');

const tutorInicial = props.tutores.find((t) => t.id === props.titulacion.tutor.id);
const selectedTutorObj = ref<{ value: string | number; label: string } | null>(
    tutorInicial ? { value: tutorInicial.id, label: tutorInicial.name } : { value: props.titulacion.tutor.id, label: props.titulacion.tutor.name },
);
watch(selectedTutorObj, (newVal) => {
    tutorId.value = newVal ? newVal.value : '';
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    router.put(route('titulacion.update', props.titulacion.id), values, {
        preserveScroll: true,
        onSuccess: () => toast.success('Proceso actualizado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar proceso de ${props.titulacion.estudiante.name}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Editar Proceso de Titulación</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.titulacion.estudiante.name }}</p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-surface)]">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ errorMessage }" name="tutor_id">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Tutor *</FormLabel>
                            <Combobox v-model="selectedTutorObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal text-[var(--sispaa-text)]">
                                                {{ selectedTutorObj ? selectedTutorObj.label : 'Selecciona un tutor...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                    <ComboboxInput placeholder="Buscar tutor..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                    <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron tutores.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="t in tutores"
                                            :key="t.id"
                                            :value="{ value: t.id, label: t.name }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_80%,transparent)]"
                                        >
                                            {{ t.name }}
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="tema">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Tema *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupTextarea v-bind="componentField" rows="3" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="fecha_inicio">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Fecha de inicio</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><Calendar class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="date" v-bind="componentField" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ processing ? 'Guardando...' : 'Guardar cambios' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
