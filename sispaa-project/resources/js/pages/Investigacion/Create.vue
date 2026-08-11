<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupTextarea } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { Check, ChevronsUpDown, FlaskConical, X } from 'lucide-vue-next';
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
import { useSubmitToast } from '@/composables/useSubmitToast';
import type { Catalogo } from './types';

const props = defineProps<{
    periodos: Catalogo[];
    usuarios: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        titulo: z.string().min(1, 'El título es obligatorio.'),
        objetivo: z.string().nullable().optional(),
        periodo_id: requiredSelect('Selecciona un período.'),
        lider_id: requiredSelect('Selecciona un líder de proyecto.'),
        colider_id: z.union([z.string(), z.number()]).nullable().optional(),
        miembros: z.array(z.union([z.string(), z.number()])).optional(),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        titulo: '',
        objetivo: '',
        periodo_id: '',
        lider_id: '',
        colider_id: '',
        miembros: [],
    },
});

const [periodoId] = defineField('periodo_id');
const [liderId] = defineField('lider_id');
const [coliderId] = defineField('colider_id');
const [miembros] = defineField('miembros');

const selectedPeriodoObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedPeriodoObj, (newVal) => {
    periodoId.value = newVal ? newVal.value : '';
});

const selectedLiderObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedLiderObj, (newVal) => {
    liderId.value = newVal ? newVal.value : '';
});

const selectedColiderObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedColiderObj, (newVal) => {
    coliderId.value = newVal ? newVal.value : '';
});

// Miembros: se agregan con un combobox y se muestran en una lista,
// excluyendo a quien ya es líder, colíder o miembro seleccionado.
const usuariosDisponibles = computed(() =>
    props.usuarios.filter(
        (u) => u.id !== liderId.value && u.id !== coliderId.value && !(miembros.value ?? []).includes(u.id),
    ),
);
const miembrosSeleccionados = computed(() =>
    (miembros.value ?? [])
        .map((id) => props.usuarios.find((u) => u.id === id))
        .filter((u): u is Catalogo => Boolean(u))
        .map((u) => ({ id: u.id, label: u.name! })),
);
const addMiembro = (val: unknown) => {
    const sel = val as { value: string | number; label: string } | null;
    if (!sel) return;
    if (!(miembros.value ?? []).includes(sel.value)) {
        miembros.value = [...(miembros.value ?? []), sel.value];
    }
};
const removeMiembro = (id: string | number) => {
    miembros.value = (miembros.value ?? []).filter((m) => m !== id);
};

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const { withToast } = useSubmitToast('Guardando proyecto...', 'Revisa los campos del formulario.');

    router.post(route('investigacion.store'), values, withToast({
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
        },
        onFinish: () => {
            processing.value = false;
        },
    }));
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nuevo Proyecto de Investigación" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Nuevo Proyecto de Investigación</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Define el equipo que llevará adelante el proyecto.</p>
            </div>

            <div class="w-full max-w-5xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ componentField }" name="titulo">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Título *</FormLabel>
                            <FormControl>
                                <InputGroup>
                                    <InputGroupAddon><FlaskConical class="h-4 w-4" /></InputGroupAddon>
                                    <InputGroupInput type="text" placeholder="Ej. Evaluación de sustratos orgánicos en cultivos de ciclo corto" v-bind="componentField" />
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
                                    <InputGroupTextarea placeholder="Describe brevemente el objetivo del proyecto..." v-bind="componentField" rows="3" />
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

                    <FormField v-slot="{ errorMessage }" name="lider_id">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Líder de proyecto *</FormLabel>
                            <Combobox v-model="selectedLiderObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal text-[var(--sispaa-text)]">
                                                {{ selectedLiderObj ? selectedLiderObj.label : 'Selecciona un líder de proyecto...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                    <ComboboxInput placeholder="Buscar usuario..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                    <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron usuarios.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="u in usuarios"
                                            :key="u.id"
                                            :value="{ value: u.id, label: u.name! }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_80%,transparent)]"
                                        >
                                            {{ u.name }}
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ errorMessage }" name="colider_id">
                        <FormItem>
                            <FormLabel class="text-[var(--sispaa-text)]">Colíder (opcional)</FormLabel>
                            <Combobox v-model="selectedColiderObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal text-[var(--sispaa-text)]">
                                                {{ selectedColiderObj ? selectedColiderObj.label : 'Selecciona un colíder...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                    <ComboboxInput placeholder="Buscar usuario..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                    <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron usuarios.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="u in usuarios.filter(x => x.id !== liderId)"
                                            :key="u.id"
                                            :value="{ value: u.id, label: u.name! }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_80%,transparent)]"
                                        >
                                            {{ u.name }}
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[var(--sispaa-text)]">Miembros (opcional)</label>
                        <Combobox @update:model-value="addMiembro">
                            <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal text-[var(--sispaa-text)]">
                                        Agregar un miembro...
                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                            </ComboboxAnchor>
                            <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                <ComboboxInput placeholder="Buscar usuario..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                                <ComboboxEmpty class="py-2 text-center text-xs opacity-60 text-[var(--sispaa-text)]">No se encontraron usuarios.</ComboboxEmpty>
                                <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                    <ComboboxItem
                                        v-for="u in usuariosDisponibles"
                                        :key="u.id"
                                        :value="{ value: u.id, label: u.name! }"
                                        class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_80%,transparent)]"
                                    >
                                        {{ u.name }}
                                        <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>

                        <div v-if="(miembros ?? []).length" class="mt-3 space-y-2">
                            <div v-for="m in miembrosSeleccionados" :key="m.id" class="flex items-center justify-between gap-2 rounded-lg border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] bg-[var(--sispaa-background)] px-3 py-2">
                                <span class="text-sm text-[var(--sispaa-text)]">{{ m.label }}</span>
                                <button type="button" @click="removeMiembro(m.id)" class="shrink-0 rounded-md p-1 opacity-60 text-[var(--sispaa-text)] hover:opacity-100 hover:bg-[color:color-mix(in_srgb,var(--sispaa-surface)_60%,transparent)]">
                                    <X class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>
                        <p v-else class="mt-2 text-xs opacity-50 text-[var(--sispaa-text)]">Aún no has agregado miembros.</p>
                    </div>

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
