<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, FlaskConical } from 'lucide-vue-next';
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
import type { Catalogo } from './types';

const props = defineProps<{
    periodos: Catalogo[];
    coordinadores: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        titulo: z.string().min(1, 'El título es obligatorio.'),
        objetivo: z.string().nullable().optional(),
        periodo_id: requiredSelect('Selecciona un período.'),
        coordinador_id: requiredSelect('Selecciona un coordinador.'),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        titulo: '',
        objetivo: '',
        periodo_id: '',
        coordinador_id: '',
    },
});

const [periodoId] = defineField('periodo_id');
const [coordinadorId] = defineField('coordinador_id');

const selectedPeriodoObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedPeriodoObj, (newVal) => {
    periodoId.value = newVal ? newVal.value : '';
});

const selectedCoordinadorObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedCoordinadorObj, (newVal) => {
    coordinadorId.value = newVal ? newVal.value : '';
});

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    router.post(route('investigacion.store'), values, {
        preserveScroll: true,
        onSuccess: () => toast.success('Proyecto creado.'),
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            toast.error('Revisa los campos del formulario.');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Proyecto de Investigación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Nuevo Proyecto de Investigación</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Selecciona el coordinador que supervisará tu proyecto.</p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ componentField }" name="titulo">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Título *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><FlaskConical class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="text" v-bind="componentField" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="objetivo">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Objetivo</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupTextarea v-bind="componentField" rows="3" />
                                </InputGroup>
                            </FormControl>
                            <FormMessage />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ errorMessage }" name="periodo_id">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Período *</FormLabel>
                            <Combobox v-model="selectedPeriodoObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal text-[var(--sispaa-text)]">
                                                {{ selectedPeriodoObj ? selectedPeriodoObj.label : 'Selecciona un período...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                    <ComboboxInput placeholder="Buscar período..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                    <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron períodos.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="per in periodos"
                                            :key="per.id"
                                            :value="{ value: per.id, label: per.nombre }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_80%,transparent)]"
                                        >
                                            {{ per.nombre }}
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ errorMessage }" name="coordinador_id">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Coordinador supervisor *</FormLabel>
                            <Combobox v-model="selectedCoordinadorObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal text-[var(--sispaa-text)]">
                                                {{ selectedCoordinadorObj ? selectedCoordinadorObj.label : 'Selecciona un coordinador...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                    <ComboboxInput placeholder="Buscar coordinador..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                    <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron coordinadores.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="c in coordinadores"
                                            :key="c.id"
                                            :value="{ value: c.id, label: c.name }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_80%,transparent)]"
                                        >
                                            {{ c.name }}
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <div class="flex items-center gap-2 pt-2">
                        <Button type="submit" :disabled="processing" class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            {{ processing ? 'Creando...' : 'Crear proyecto' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
