<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { computed, ref, watch } from 'vue';
import { Check, ChevronsUpDown, Search, UserCheck, Calendar } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
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
import type { Estudiante, Periodo, Carrera } from './types';

const props = defineProps<{
    estudiantes: Estudiante[];
    periodos: Periodo[];
    carreras: Carrera[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const estudianteSearch = ref('');
const filteredEstudiantes = computed(() => {
    const q = estudianteSearch.value.toLowerCase();
    if (!q) return props.estudiantes.slice(0, 30);
    return props.estudiantes
        .filter((e) => e.name.toLowerCase().includes(q) || (e.cedula && e.cedula.includes(q)) || e.email.toLowerCase().includes(q))
        .slice(0, 30);
});

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        estudiante_id: requiredSelect('Selecciona un estudiante.'),
        periodo_id: requiredSelect('Selecciona un período.'),
        carrera_id: requiredSelect('Selecciona una carrera.'),
        fecha_matricula: z.string().min(1, 'La fecha de matrícula es obligatoria.'),
    }),
);

const { handleSubmit, setErrors, defineField, values } = useForm({
    validationSchema: formSchema,
    initialValues: {
        estudiante_id: '',
        periodo_id: '',
        carrera_id: '',
        fecha_matricula: new Date().toISOString().split('T')[0],
    },
});

const [estudianteId] = defineField('estudiante_id');
const [periodoId] = defineField('periodo_id');
const [carreraId] = defineField('carrera_id');

const selectedEstudiante = computed(() => props.estudiantes.find((e) => e.id === estudianteId.value));

const selectedPeriodoObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedPeriodoObj, (newVal) => {
    periodoId.value = newVal ? newVal.value : '';
});

const selectedCarreraObj = ref<{ value: string | number; label: string } | null>(null);
watch(selectedCarreraObj, (newVal) => {
    carreraId.value = newVal ? newVal.value : '';
});

const processing = ref(false);

const onSubmit = handleSubmit((submitValues) => {
    processing.value = true;

    router.post(route('secretaria.matriculas.store'), submitValues, {
        preserveScroll: true,
        onSuccess: () => toast.success('Matrícula registrada correctamente ✓'),
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            const firstError = Object.values(serverErrors)[0];
            toast.error(firstError || 'Error al registrar la matrícula.');
        },
        onFinish: () => {
            processing.value = false;
        },
    });
});
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Nueva Matrícula" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="flex items-center gap-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    <UserCheck class="h-6 w-6 text-indigo-500" /> Nueva Matrícula
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inscribe un estudiante en un período académico y carrera.</p>
            </div>

            <div class="max-w-xl mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <form class="space-y-5" @submit="onSubmit">
                    <FormField v-slot="{ errorMessage }" name="estudiante_id">
                        <FormItem>
                            <FormLabel>Estudiante *</FormLabel>
                            <InputGroup>
                                <InputGroupAddon><Search class="h-4 w-4" /></InputGroupAddon>
                                <InputGroupInput v-model="estudianteSearch" type="text" placeholder="Buscar por nombre o cédula..." />
                            </InputGroup>
                            <div
                                v-if="estudianteSearch || !estudianteId"
                                class="mt-1 max-h-48 overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <button
                                    v-for="e in filteredEstudiantes"
                                    :key="e.id"
                                    type="button"
                                    class="w-full border-b border-slate-100 px-3 py-2.5 text-left text-sm transition-colors last:border-0 hover:bg-indigo-50 dark:border-slate-800 dark:hover:bg-indigo-950/20"
                                    :class="{ 'bg-indigo-50 dark:bg-indigo-950/20': estudianteId === e.id }"
                                    @click="
                                        () => {
                                            estudianteId = e.id;
                                            estudianteSearch = e.name;
                                        }
                                    "
                                >
                                    <p class="font-semibold text-slate-800 dark:text-slate-200">{{ e.name }}</p>
                                    <p class="text-xs text-slate-400">{{ e.cedula ?? e.email }}</p>
                                </button>
                                <p v-if="filteredEstudiantes.length === 0" class="px-3 py-4 text-center text-sm text-slate-400">Sin resultados</p>
                            </div>
                            <div v-if="estudianteId && !estudianteSearch.length" class="mt-1 text-xs font-semibold text-emerald-600">
                                ✓ {{ selectedEstudiante?.name }}
                            </div>
                            <p v-if="errorMessage" class="mt-1 text-sm font-medium text-destructive">{{ errorMessage }}</p>
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ errorMessage }" name="periodo_id">
                        <FormItem>
                            <FormLabel>Período Académico *</FormLabel>
                            <Combobox v-model="selectedPeriodoObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                                {{ selectedPeriodoObj ? selectedPeriodoObj.label : 'Selecciona un período...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                                    <ComboboxInput placeholder="Buscar período..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                    <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron períodos.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="p in periodos"
                                            :key="p.id"
                                            :value="{ value: p.id, label: p.estado === 'activo' ? `${p.nombre} (activo)` : p.nombre }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                                        >
                                            {{ p.nombre }}
                                            <span v-if="p.estado === 'activo'" class="ml-1 text-xs text-emerald-500">(activo)</span>
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ errorMessage }" name="carrera_id">
                        <FormItem>
                            <FormLabel>Carrera *</FormLabel>
                            <Combobox v-model="selectedCarreraObj" by="value">
                                <ComboboxAnchor as-child>
                                    <ComboboxTrigger as-child>
                                        <FormControl>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                                {{ selectedCarreraObj ? selectedCarreraObj.label : 'Selecciona una carrera...' }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </FormControl>
                                    </ComboboxTrigger>
                                </ComboboxAnchor>
                                <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                                    <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                    <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                                    <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                        <ComboboxItem
                                            v-for="c in carreras"
                                            :key="c.id"
                                            :value="{ value: c.id, label: `${c.codigo} — ${c.nombre}` }"
                                            class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                                        >
                                            {{ c.codigo }} — {{ c.nombre }}
                                            <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                        </ComboboxItem>
                                    </ComboboxGroup>
                                </ComboboxList>
                            </Combobox>
                            <FormMessage v-if="errorMessage" />
                        </FormItem>
                    </FormField>

                    <FormField v-slot="{ componentField }" name="fecha_matricula">
                        <FormItem>
                            <FormLabel>Fecha de Matrícula *</FormLabel>
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
                        <Button
                            type="submit"
                            :disabled="processing || !values.estudiante_id || !values.periodo_id || !values.carrera_id"
                            class="bg-indigo-600 font-semibold text-white hover:bg-indigo-500 disabled:opacity-50"
                        >
                            {{ processing ? 'Registrando...' : 'Registrar Matrícula' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppSidebarLayout>
</template>
