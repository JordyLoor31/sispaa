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
    proyecto: {
        id: number;
        titulo: string;
        objetivo: string | null;
        lider_id: number;
        colider_id: number | null;
        miembros: number[];
    };
    usuarios: Catalogo[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        titulo: z.string().min(1, 'El título es obligatorio.'),
        objetivo: z.string().nullable().optional(),
        lider_id: requiredSelect('Selecciona un líder de proyecto.'),
        colider_id: z.union([z.string(), z.number()]).nullable().optional(),
        miembros: z.array(z.union([z.string(), z.number()])).optional(),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        titulo: props.proyecto.titulo,
        objetivo: props.proyecto.objetivo ?? '',
        lider_id: props.proyecto.lider_id,
        colider_id: props.proyecto.colider_id ?? '',
        miembros: props.proyecto.miembros ?? [],
    },
});

const [liderId] = defineField('lider_id');
const [coliderId] = defineField('colider_id');
const [miembros] = defineField('miembros');

const liderInicial = props.usuarios.find((u) => u.id === props.proyecto.lider_id);
const selectedLiderObj = ref<{ value: string | number; label: string } | null>(
    liderInicial ? { value: liderInicial.id, label: liderInicial.name! } : null,
);
watch(selectedLiderObj, (newVal) => {
    liderId.value = newVal ? newVal.value : '';
});

const coliderInicial = props.usuarios.find((u) => u.id === props.proyecto.colider_id);
const selectedColiderObj = ref<{ value: string | number; label: string } | null>(
    coliderInicial ? { value: coliderInicial.id, label: coliderInicial.name! } : null,
);
watch(selectedColiderObj, (newVal) => {
    coliderId.value = newVal ? newVal.value : '';
});

const usuariosParaMiembros = computed(() =>
    props.usuarios.filter((u) => u.id !== liderId.value && u.id !== coliderId.value),
);
const miembroChecked = (id: number) => (miembros.value ?? []).includes(id);
const toggleMiembro = (id: number, checked: boolean) => {
    if (checked) {
        miembros.value = [...(miembros.value ?? []), id];
    } else {
        miembros.value = (miembros.value ?? []).filter((m) => m !== id);
    }
};

const processing = ref(false);

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    router.put(route('investigacion.update', props.proyecto.id), values, {
        preserveScroll: true,
        onSuccess: () => toast.success('Proyecto actualizado.'),
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Editar ${props.proyecto.titulo}`" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-3xl">Editar Proyecto</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">{{ props.proyecto.titulo }}</p>
            </div>

            <div class="w-full max-w-xl mx-auto rounded-2xl p-6 shadow-sm bg-[var(--sispaa-background)]">
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
                        <div class="grid max-h-40 grid-cols-1 gap-2 overflow-y-auto rounded-lg p-3 sm:grid-cols-2 border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <label v-for="u in usuariosParaMiembros" :key="u.id" class="flex items-center gap-2 text-xs text-[var(--sispaa-text)]">
                                <input type="checkbox" :checked="miembroChecked(u.id)" class="rounded accent-[var(--sispaa-primary)]" @change="toggleMiembro(u.id, ($event.target as HTMLInputElement).checked)" />
                                {{ u.name }}
                            </label>
                            <p v-if="usuariosParaMiembros.length === 0" class="col-span-full text-xs opacity-50 text-[var(--sispaa-text)]">No hay más usuarios disponibles.</p>
                        </div>
                    </div>

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
