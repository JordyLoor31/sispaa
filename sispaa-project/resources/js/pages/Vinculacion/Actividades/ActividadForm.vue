<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { router } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { Calendar, ClipboardList } from 'lucide-vue-next';
import { useForm } from 'vee-validate';
import { computed, ref } from 'vue';
import * as z from 'zod';
import ComboSelect from './ComboSelect.vue';
import MatrizBeneficiarios from './MatrizBeneficiarios.vue';
import {
    conteosToMatriz,
    emptyMatriz,
    matrizToConteos,
    TIPO_LABEL_CORTO,
    type Actividad,
    type BeneficiarioRef,
    type Catalogo,
    type Matriz,
    type RepresentanteRef,
} from './types';

const props = defineProps<{
    actividad?: Actividad | null;
    docentes: { id: number; name: string }[];
    carreras: Catalogo[];
    periodos: Catalogo[];
    beneficiarios: BeneficiarioRef[];
    representantes: RepresentanteRef[];
}>();

const isEditing = !!props.actividad;
const enEjecucion = !isEditing || props.actividad?.estado === 'en_ejecucion';

const requiredSelect = (message: string) =>
    z.union([z.string(), z.number()]).nullable().refine((v) => v !== '' && v !== null && v !== undefined, { message });

const formSchema = toTypedSchema(
    z.object({
        nombre: z.string().min(1, 'El nombre es obligatorio.').max(255, 'El nombre no puede superar los 255 caracteres.'),
        docente_lider_id: requiredSelect('Selecciona un líder.'),
        supervisor_id: requiredSelect('Selecciona un supervisor.'),
        carrera_id: requiredSelect('Selecciona una carrera.'),
        periodo_id: requiredSelect('Selecciona un período.'),
        beneficiario_id: requiredSelect('Selecciona un beneficiario.'),
        representante_id: z.union([z.string(), z.number()]).nullable(),
        fecha_inicio: z.string().min(1, 'La fecha de inicio es obligatoria.'),
    }),
);

const { handleSubmit, setErrors, defineField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        nombre: props.actividad?.nombre ?? '',
        docente_lider_id: props.actividad?.docente_lider?.id ?? null,
        supervisor_id: props.actividad?.supervisor?.id ?? null,
        carrera_id: props.actividad?.carrera_id ?? null,
        periodo_id: props.actividad?.periodo_id ?? null,
        beneficiario_id: props.actividad?.beneficiario_id ?? null,
        representante_id: props.actividad?.representante_id ?? null,
        fecha_inicio: props.actividad?.fecha_inicio ?? '',
    },
});

const [docenteLiderId] = defineField('docente_lider_id');
const [supervisorId] = defineField('supervisor_id');
const [carreraId] = defineField('carrera_id');
const [periodoId] = defineField('periodo_id');
const [beneficiarioId] = defineField('beneficiario_id');
const [representanteId] = defineField('representante_id');

// Catálogos -> opciones para ComboSelect
const docenteOptions = computed(() => props.docentes.map((d) => ({ value: d.id, label: d.name })));
const carreraOptions = computed(() => props.carreras.map((c) => ({ value: c.id, label: c.nombre })));
const periodoOptions = computed(() => props.periodos.map((p) => ({ value: p.id, label: p.nombre })));
const beneficiarioOptions = computed(() =>
    props.beneficiarios.map((b) => ({
        value: b.id,
        label: b.nombre,
        sublabel: [TIPO_LABEL_CORTO[b.tipo] ?? b.tipo, b.ruc ? `RUC ${b.ruc}` : null].filter(Boolean).join(' · '),
    })),
);
const representanteOptions = computed(() =>
    props.representantes.map((r) => ({
        value: r.id,
        label: r.nombre,
        sublabel: [r.cargo, r.telefono].filter(Boolean).join(' · '),
    })),
);

// Representante: se puede registrar uno nuevo (inline) o reusar uno existente.
const usarRepExistente = ref(isEditing && !!props.actividad?.representante_id);
const repNombre = ref(props.actividad?.representante?.nombre ?? '');
const repCedula = ref(props.actividad?.representante?.cedula ?? '');
const repCargo = ref(props.actividad?.representante?.cargo ?? '');
const repTelefono = ref(props.actividad?.representante?.telefono ?? '');

// Matriz de beneficiarios iniciales
const matriz = ref<Matriz>(
    props.actividad?.conteos_iniciales?.length ? conteosToMatriz(props.actividad.conteos_iniciales) : emptyMatriz(),
);

const processing = ref(false);

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        router.visit(route('vinculacion.actividades'));
    }
};

const onSubmit = handleSubmit((values) => {
    processing.value = true;

    const payload = {
        ...values,
        representante_id: usarRepExistente.value ? values.representante_id : null,
        representante_nombre: usarRepExistente.value ? null : repNombre.value || null,
        representante_cedula: usarRepExistente.value ? null : repCedula.value || null,
        representante_cargo: usarRepExistente.value ? null : repCargo.value || null,
        representante_telefono: usarRepExistente.value ? null : repTelefono.value || null,
        conteos: matrizToConteos(matriz.value),
    };

    const options = {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => setErrors(serverErrors),
        onFinish: () => {
            processing.value = false;
        },
    };

    if (isEditing && props.actividad) {
        router.put(route('vinculacion.actividades.update', props.actividad.id), payload, options);
    } else {
        router.post(route('vinculacion.actividades.store'), payload, options);
    }
});

const inputClass =
    'w-full rounded-md border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-background)] px-3 py-2 text-sm text-[var(--sispaa-text)] focus:border-[var(--sispaa-primary)] focus:outline-none focus:ring-1 focus:ring-[var(--sispaa-primary)]';
</script>

<template>
    <form class="space-y-6" @submit="onSubmit">
        <FormField v-slot="{ componentField }" name="nombre">
            <FormItem>
                <FormLabel>Nombre *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><ClipboardList class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="text" v-bind="componentField" placeholder="Nombre de la actividad" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Roles internos: Supervisor y Líder -->
        <div class="grid gap-5 sm:grid-cols-2">
            <FormField v-slot="{ errorMessage }" name="supervisor_id">
                <FormItem>
                    <FormLabel>Supervisor *</FormLabel>
                    <ComboSelect v-model="supervisorId" :options="docenteOptions" placeholder="Selecciona un supervisor..." search-placeholder="Buscar docente..." empty-text="No se encontraron docentes." />
                    <FormMessage v-if="errorMessage" />
                </FormItem>
            </FormField>

            <FormField v-slot="{ errorMessage }" name="docente_lider_id">
                <FormItem>
                    <FormLabel>Líder *</FormLabel>
                    <ComboSelect v-model="docenteLiderId" :options="docenteOptions" placeholder="Selecciona un líder..." search-placeholder="Buscar docente..." empty-text="No se encontraron docentes." />
                    <FormMessage v-if="errorMessage" />
                </FormItem>
            </FormField>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <FormField v-slot="{ errorMessage }" name="carrera_id">
                <FormItem>
                    <FormLabel>Carrera *</FormLabel>
                    <ComboSelect v-model="carreraId" :options="carreraOptions" placeholder="Selecciona una carrera..." search-placeholder="Buscar carrera..." empty-text="No se encontraron carreras." />
                    <FormMessage v-if="errorMessage" />
                </FormItem>
            </FormField>

            <FormField v-slot="{ errorMessage }" name="periodo_id">
                <FormItem>
                    <FormLabel>Período *</FormLabel>
                    <ComboSelect v-model="periodoId" :options="periodoOptions" placeholder="Selecciona un período..." search-placeholder="Buscar período..." empty-text="No se encontraron períodos." />
                    <FormMessage v-if="errorMessage" />
                </FormItem>
            </FormField>
        </div>

        <!-- Beneficiario (obligatorio; se elige del catálogo de Beneficiarios) -->
        <FormField v-slot="{ errorMessage }" name="beneficiario_id">
            <FormItem>
                <FormLabel>Beneficiario *</FormLabel>
                <ComboSelect
                    v-model="beneficiarioId"
                    :options="beneficiarioOptions"
                    placeholder="Selecciona un beneficiario..."
                    search-placeholder="Buscar beneficiario..."
                    empty-text="No hay beneficiarios. Créalos en la sección Beneficiarios."
                />
                <FormMessage v-if="errorMessage" />
            </FormItem>
        </FormField>

        <!-- Representante de los beneficiarios -->
        <div class="space-y-3 rounded-xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)] p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <span class="text-sm font-medium text-[var(--sispaa-text)]">Representante de los beneficiarios (opcional)</span>
                <label v-if="representantes.length" class="flex cursor-pointer items-center gap-2 text-xs text-[var(--sispaa-text)]">
                    <input type="checkbox" v-model="usarRepExistente" class="accent-[var(--sispaa-primary)]" />
                    Usar uno ya registrado
                </label>
            </div>

            <div v-if="usarRepExistente">
                <ComboSelect
                    v-model="representanteId"
                    :options="representanteOptions"
                    placeholder="Selecciona un representante..."
                    search-placeholder="Buscar representante..."
                    empty-text="No hay representantes registrados."
                    allow-empty
                    empty-label="Sin representante"
                />
            </div>
            <div v-else class="grid gap-3 sm:grid-cols-2">
                <input v-model="repNombre" type="text" :class="inputClass" placeholder="Nombre del representante" />
                <input v-model="repCedula" type="text" inputmode="numeric" maxlength="10" :class="inputClass" placeholder="Cédula" />
                <input v-model="repCargo" type="text" :class="inputClass" placeholder="Cargo o rol (ej. Presidente del barrio)" />
                <input v-model="repTelefono" type="text" inputmode="numeric" maxlength="10" :class="inputClass" placeholder="Teléfono de contacto" />
            </div>
            <p class="text-xs opacity-60 text-[var(--sispaa-text)]">
                Es la persona que representa a los beneficiarios (distinta del líder/supervisor). Se registra aquí y queda disponible para reusar en otras actividades.
            </p>
        </div>

        <FormField v-slot="{ componentField }" name="fecha_inicio">
            <FormItem>
                <FormLabel>Fecha de inicio *</FormLabel>
                <FormControl>
                    <InputGroup>
                        <InputGroupAddon><Calendar class="h-4 w-4" /></InputGroupAddon>
                        <InputGroupInput type="date" v-bind="componentField" />
                    </InputGroup>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Beneficiarios iniciales: matriz género x edad -->
        <div class="space-y-2">
            <div class="flex items-baseline justify-between">
                <span class="text-sm font-medium text-[var(--sispaa-text)]">Beneficiarios iniciales</span>
                <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Puede quedar en 0 y agregarse después.</span>
            </div>
            <MatrizBeneficiarios v-model="matriz" :readonly="isEditing && !enEjecucion" />
            <p v-if="isEditing && enEjecucion" class="text-xs opacity-60 text-[var(--sispaa-text)]">
                Editas el conteo <strong>inicial</strong>. Los avances (adicionales) se agregan desde el detalle de la actividad.
            </p>
        </div>

        <div class="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:items-center">
            <Button
                type="submit"
                :disabled="processing"
                class="w-full bg-[var(--sispaa-primary)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] sm:w-auto"
            >
                {{ processing ? 'Guardando...' : isEditing ? 'Guardar cambios' : 'Registrar actividad' }}
            </Button>
            <Button
                type="button"
                :disabled="processing"
                class="w-full bg-[var(--sispaa-accent)] font-semibold text-white hover:bg-[color:color-mix(in_srgb,var(--sispaa-accent)_85%,black)] sm:w-auto"
                @click="goBack"
            >
                Cancelar
            </Button>
        </div>
    </form>
</template>
