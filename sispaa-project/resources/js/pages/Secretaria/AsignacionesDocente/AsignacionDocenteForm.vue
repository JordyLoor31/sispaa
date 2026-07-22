<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { ref, watch } from 'vue';
import { Check, ChevronsUpDown, GraduationCap, Lock } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
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
import type { AsignacionDocenteItem, Docente, MateriaOption, PeriodoOption } from './types';

const props = defineProps<{
    asignacion?: AsignacionDocenteItem | null;
    docentes: Docente[];
    materias: MateriaOption[];
    periodos: PeriodoOption[];
    tiposRol: string[];
}>();

const isEditing = !!props.asignacion;

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        docente_id: requiredSelect('Selecciona un docente.'),
        materia_id: requiredSelect('Selecciona una materia.'),
        periodo_id: requiredSelect('Selecciona un período.'),
        tipo_rol: z.string().min(1, 'Selecciona el tipo de rol.'),
        grupo: z.string().max(5, 'Máximo 5 caracteres.').nullable().optional(),
    }),
);

const { handleSubmit, setErrors, defineField, values } = useForm({
    validationSchema: formSchema,
    initialValues: {
        docente_id: props.asignacion?.docente.id ?? '',
        materia_id: props.asignacion?.materia.id ?? '',
        periodo_id: props.asignacion?.periodo.id ?? '',
        tipo_rol: props.asignacion?.tipo_rol ?? '',
        grupo: props.asignacion?.grupo ?? '',
    },
});

const [docenteId] = defineField('docente_id');
const [materiaId] = defineField('materia_id');
const [periodoId] = defineField('periodo_id');

// ── Docente (Combobox buscable) ─────────────────────────────────────────
const docenteInicial = props.docentes.find((d) => d.id === props.asignacion?.docente.id);
const selectedDocenteObj = ref<{ value: number; label: string } | null>(
    docenteInicial ? { value: docenteInicial.id, label: docenteInicial.name } : null,
);
watch(selectedDocenteObj, (v) => { docenteId.value = v ? v.value : ''; });

// ── Materia (Combobox buscable, muestra carrera) ────────────────────────
const materiaInicial = props.materias.find((m) => m.id === props.asignacion?.materia.id);
const selectedMateriaObj = ref<{ value: number; label: string } | null>(
    materiaInicial ? { value: materiaInicial.id, label: `${materiaInicial.codigo} — ${materiaInicial.nombre}` } : null,
);
watch(selectedMateriaObj, (v) => { materiaId.value = v ? v.value : ''; });

// ── Período (Combobox) ───────────────────────────────────────────────────
const periodoInicial = props.periodos.find((p) => p.id === props.asignacion?.periodo.id);
const selectedPeriodoObj = ref<{ value: number; label: string } | null>(
    periodoInicial ? { value: periodoInicial.id, label: periodoInicial.nombre } : null,
);
watch(selectedPeriodoObj, (v) => { periodoId.value = v ? v.value : ''; });

const processing = ref(false);

const onSubmit = handleSubmit((submitValues) => {
    processing.value = true;

    const options = {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
        },
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing && props.asignacion) {
        router.put(route('secretaria.asignaciones-docente.update', props.asignacion.id), submitValues, options);
    } else {
        router.post(route('secretaria.asignaciones-docente.store'), submitValues, options);
    }
});
</script>

<template>
    <form class="space-y-5" @submit="onSubmit">
        <FormField v-slot="{ errorMessage }" name="docente_id">
            <FormItem>
                <FormLabel>Docente *</FormLabel>

                <!-- Al editar, el docente queda fijo: no tiene sentido reasignar la
                     fila a otro docente desde aquí (para eso se crea una asignación
                     nueva y se elimina esta). Solo se puede elegir al crear. -->
                <div
                    v-if="isEditing"
                    class="flex items-center justify-between rounded-md px-3 py-2 text-sm opacity-70 text-[var(--sispaa-text)] bg-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)]"
                >
                    <div>
                        <p class="font-medium text-[var(--sispaa-text)]">{{ asignacion?.docente.name }}</p>
                        <p class="text-xs opacity-60 text-[var(--sispaa-text)]">{{ asignacion?.docente.email }}</p>
                    </div>
                    <Lock class="h-4 w-4 opacity-50" />
                </div>
                <Combobox v-else v-model="selectedDocenteObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedDocenteObj ? selectedDocenteObj.label : 'Selecciona un docente...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[280px] rounded-lg shadow-lg bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar docente..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron docentes.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="d in docentes"
                                :key="d.id"
                                :value="{ value: d.id, label: d.name }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]"
                            >
                                <div>
                                    <p>{{ d.name }}</p>
                                    <p class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ d.cedula ?? d.email }}</p>
                                </div>
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage">{{ errorMessage }}</FormMessage>
            </FormItem>
        </FormField>

        <FormField v-slot="{ errorMessage }" name="materia_id">
            <FormItem>
                <FormLabel>Materia *</FormLabel>
                <Combobox v-model="selectedMateriaObj" by="value">
                    <ComboboxAnchor as-child>
                        <ComboboxTrigger as-child>
                            <FormControl>
                                <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal">
                                    {{ selectedMateriaObj ? selectedMateriaObj.label : 'Selecciona una materia...' }}
                                    <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                </Button>
                            </FormControl>
                        </ComboboxTrigger>
                    </ComboboxAnchor>
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[280px] rounded-lg shadow-lg bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar materia..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron materias.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="m in materias"
                                :key="m.id"
                                :value="{ value: m.id, label: `${m.codigo} — ${m.nombre}` }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]"
                            >
                                <div>
                                    <p>{{ m.codigo }} — {{ m.nombre }}</p>
                                    <p v-if="m.carrera" class="text-xs opacity-50 text-[var(--sispaa-text)]">{{ m.carrera.nombre }}</p>
                                </div>
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage">{{ errorMessage }}</FormMessage>
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
                    <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg shadow-lg bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <ComboboxInput placeholder="Buscar período..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]" />
                        <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron períodos.</ComboboxEmpty>
                        <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                            <ComboboxItem
                                v-for="p in periodos"
                                :key="p.id"
                                :value="{ value: p.id, label: p.estado === 'activo' ? `${p.nombre} (activo)` : p.nombre }"
                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]"
                            >
                                {{ p.nombre }}
                                <span v-if="p.estado === 'activo'" class="ml-1 text-xs text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]">(activo)</span>
                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                            </ComboboxItem>
                        </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                <FormMessage v-if="errorMessage">{{ errorMessage }}</FormMessage>
            </FormItem>
        </FormField>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <FormField v-slot="{ componentField }" name="tipo_rol">
                <FormItem>
                    <FormLabel>Tipo de rol *</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger class="w-full"><SelectValue placeholder="Selecciona..." /></SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem v-for="t in tiposRol" :key="t" :value="t">{{ t }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField v-slot="{ componentField }" name="grupo">
                <FormItem>
                    <FormLabel>Grupo (opcional)</FormLabel>
                    <FormControl>
                        <Input type="text" placeholder="A, B, C..." maxlength="5" v-bind="componentField" />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>

        <div class="flex items-center gap-2 pt-2">
            <Button
                type="submit"
                :disabled="processing || !values.docente_id || !values.materia_id || !values.periodo_id || !values.tipo_rol"
                class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] disabled:opacity-50"
            >
                <GraduationCap class="h-4 w-4 mr-1.5" />
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Crear asignación' }}
            </Button>
        </div>
    </form>
</template>
