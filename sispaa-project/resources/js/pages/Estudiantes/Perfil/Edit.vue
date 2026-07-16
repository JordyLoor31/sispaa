<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm as useInertiaForm } from '@inertiajs/vue3';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm } from 'vee-validate';
import * as z from 'zod';
import { computed, ref, watch } from 'vue';
import {
    ArrowLeft, ArrowRight, Check, ChevronsUpDown, Save, Users, GraduationCap,
    Home, IdCard, Plus, X, Building2,
} from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Switch } from '@/components/ui/switch';
import { InputGroup, InputGroupInput } from '@/components/ui/input-group';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form';
import {
    Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput,
    ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger,
} from '@/components/ui/combobox';
import {
    Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger,
} from '@/components/ui/stepper';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { useProvinciasCiudadesEcuador } from '@/composables/provincias_ciudades_ecuador';
import { makeFamiliarColumns, PARENTESCO_LABELS, type Familiar, type Parentesco } from './columns';

interface Catalogo { id: number; nombre: string }

interface Perfil {
    tipo_alumno: string | null;
    nivel: string | null;
    sede: string | null;
    carrera_id: number | null;
    anio_ingreso: number | null;
    graduado_pregrado: boolean;
    fecha_graduacion: string | null;
    colegio: string | null;
    anio_graduacion_colegio: number | null;
    provincia_colegio: string | null;
    universidad_procedencia: string | null;
    provincia_universidad: string | null;
    residente: boolean;
    direccion: string | null;
    provincia_residencia: string | null;
    canton_residencia: string | null;
    telefono_casa: string | null;
}

interface DatosAdicionales {
    religion: string | null;
    estado_civil: string | null;
    cantidad_hijos: number | null;
    etnia: string | null;
    tipo_beca: string | null;
    nacionalidad: string | null;
    pais_nacimiento: string | null;
    provincia_nacimiento: string | null;
    canton_nacimiento: string | null;
    empresa: string | null;
    direccion_empresa: string | null;
    telefono_empresa: string | null;
    cargo: string | null;
    ciudad_laboral: string | null;
}

const props = defineProps<{
    estudiante: { id: number; name: string; email: string; cedula: string | null };
    perfil: Perfil | null;
    datosAdicionales: DatosAdicionales | null;
    familiares: Familiar[];
    facultadNombre: string;
    carreras: Catalogo[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Estudiante', href: '/dashboard' },
    { title: 'Mi Perfil', href: '/estudiante/perfil' },
    { title: 'Completar Perfil', href: '/estudiante/perfil/editar' },
];

const { provincias: PROVINCIAS_ECUADOR, ciudadesDeProvincia } = useProvinciasCiudadesEcuador();

const SEDES_ULEAM = ['Matriz Manta', 'Extensión Bahía de Caráquez', 'Extensión Chone', 'Extensión El Carmen', 'Extensión Pedernales', 'Extensión Sucre'];
const SEDE_DEFECTO = 'Matriz Manta';

const ESTADOS_CIVILES: { value: string; label: string }[] = [
    { value: 'soltero', label: 'Soltero/a' },
    { value: 'casado', label: 'Casado/a' },
    { value: 'divorciado', label: 'Divorciado/a' },
    { value: 'viudo', label: 'Viudo/a' },
    { value: 'union_libre', label: 'Unión libre' },
];

/* ---------------------------------------------------------------
 | Wizard: pasos 1-3 (perfil + datos adicionales) via vee-validate + zod.
 | El paso 4 (familiares) se maneja aparte porque persiste fila por fila.
 | facultad_id no se pide: todos los estudiantes pertenecen hoy a una sola
 | facultad, forzada en el servidor (ver PerfilEstudianteController).
 |----------------------------------------------------------------*/

const steps = [
    { step: 1, title: 'Académico', description: 'Carrera y trayectoria', icon: GraduationCap },
    { step: 2, title: 'Residencia', description: 'Dirección actual', icon: Home },
    { step: 3, title: 'Adicionales', description: 'Datos personales', icon: IdCard },
    { step: 4, title: 'Familiares', description: 'Contactos familiares', icon: Users },
];

const STEP_FIELDS: Record<number, string[]> = {
    1: ['tipo_alumno', 'nivel', 'sede', 'carrera_id', 'anio_ingreso', 'graduado_pregrado', 'fecha_graduacion', 'colegio', 'anio_graduacion_colegio', 'provincia_colegio', 'universidad_procedencia', 'provincia_universidad'],
    2: ['residente', 'direccion', 'provincia_residencia', 'canton_residencia', 'telefono_casa'],
    3: ['religion', 'estado_civil', 'cantidad_hijos', 'etnia', 'tipo_beca', 'nacionalidad', 'pais_nacimiento', 'provincia_nacimiento', 'canton_nacimiento', 'empresa', 'direccion_empresa', 'telefono_empresa', 'cargo', 'ciudad_laboral'],
    4: [],
};

const currentStep = ref(1);
const highestStepVisited = ref(1);
const processing = ref(false);

const formSchema = toTypedSchema(
    z.object({
        tipo_alumno: z.string({ required_error: 'Requerido.' }).min(1, 'Requerido.').max(100),
        nivel: z.string({ required_error: 'Requerido.' }).min(1, 'Requerido.').max(100),
        sede: z.string({ required_error: 'Requerido.' }).min(1, 'Requerido.').max(150),
        carrera_id: z.string({ required_error: 'Requerido.' }).min(1, 'Requerido.'),
        anio_ingreso: z.string({ required_error: 'Requerido.' }).min(4, 'Año inválido.').max(4, 'Año inválido.'),
        graduado_pregrado: z.boolean(),
        fecha_graduacion: z.string().optional().nullable(),
        colegio: z.string().optional().nullable(),
        anio_graduacion_colegio: z.string().optional().nullable(),
        provincia_colegio: z.string().optional().nullable(),
        universidad_procedencia: z.string().optional().nullable(),
        provincia_universidad: z.string().optional().nullable(),

        residente: z.boolean(),
        direccion: z.string().optional().nullable(),
        provincia_residencia: z.string().optional().nullable(),
        canton_residencia: z.string().optional().nullable(),
        telefono_casa: z.string().optional().nullable(),

        religion: z.string().optional().nullable(),
        estado_civil: z.string().optional().nullable(),
        cantidad_hijos: z.string().optional().nullable(),
        etnia: z.string().optional().nullable(),
        tipo_beca: z.string().optional().nullable(),
        nacionalidad: z.string().optional().nullable(),
        pais_nacimiento: z.string().optional().nullable(),
        provincia_nacimiento: z.string().optional().nullable(),
        canton_nacimiento: z.string().optional().nullable(),
        empresa: z.string().optional().nullable(),
        direccion_empresa: z.string().optional().nullable(),
        telefono_empresa: z.string().optional().nullable(),
        cargo: z.string().optional().nullable(),
        ciudad_laboral: z.string().optional().nullable(),
    }),
);

const { handleSubmit, setErrors, defineField, validateField } = useForm({
    validationSchema: formSchema,
    initialValues: {
        tipo_alumno: props.perfil?.tipo_alumno ?? '',
        nivel: props.perfil?.nivel ?? '',
        sede: props.perfil?.sede ?? SEDE_DEFECTO,
        carrera_id: props.perfil?.carrera_id ? String(props.perfil.carrera_id) : '',
        anio_ingreso: props.perfil?.anio_ingreso ? String(props.perfil.anio_ingreso) : '',
        graduado_pregrado: props.perfil?.graduado_pregrado ?? false,
        fecha_graduacion: props.perfil?.fecha_graduacion ?? '',
        colegio: props.perfil?.colegio ?? '',
        anio_graduacion_colegio: props.perfil?.anio_graduacion_colegio ? String(props.perfil.anio_graduacion_colegio) : '',
        provincia_colegio: props.perfil?.provincia_colegio ?? '',
        universidad_procedencia: props.perfil?.universidad_procedencia ?? '',
        provincia_universidad: props.perfil?.provincia_universidad ?? '',

        residente: props.perfil?.residente ?? false,
        direccion: props.perfil?.direccion ?? '',
        provincia_residencia: props.perfil?.provincia_residencia ?? '',
        canton_residencia: props.perfil?.canton_residencia ?? '',
        telefono_casa: props.perfil?.telefono_casa ?? '',

        religion: props.datosAdicionales?.religion ?? '',
        estado_civil: props.datosAdicionales?.estado_civil ?? '',
        cantidad_hijos: props.datosAdicionales?.cantidad_hijos != null ? String(props.datosAdicionales.cantidad_hijos) : '0',
        etnia: props.datosAdicionales?.etnia ?? '',
        tipo_beca: props.datosAdicionales?.tipo_beca ?? '',
        nacionalidad: props.datosAdicionales?.nacionalidad ?? '',
        pais_nacimiento: props.datosAdicionales?.pais_nacimiento ?? '',
        provincia_nacimiento: props.datosAdicionales?.provincia_nacimiento ?? '',
        canton_nacimiento: props.datosAdicionales?.canton_nacimiento ?? '',
        empresa: props.datosAdicionales?.empresa ?? '',
        direccion_empresa: props.datosAdicionales?.direccion_empresa ?? '',
        telefono_empresa: props.datosAdicionales?.telefono_empresa ?? '',
        cargo: props.datosAdicionales?.cargo ?? '',
        ciudad_laboral: props.datosAdicionales?.ciudad_laboral ?? '',
    },
});

const [tipoAlumno] = defineField('tipo_alumno');
const [nivel] = defineField('nivel');
const [sede] = defineField('sede');
const [carreraIdField] = defineField('carrera_id');
const [anioIngreso] = defineField('anio_ingreso');
const [graduadoPregrado] = defineField('graduado_pregrado');
const [fechaGraduacion] = defineField('fecha_graduacion');
const [colegio] = defineField('colegio');
const [anioGraduacionColegio] = defineField('anio_graduacion_colegio');
const [provinciaColegio] = defineField('provincia_colegio');
const [universidadProcedencia] = defineField('universidad_procedencia');
const [provinciaUniversidad] = defineField('provincia_universidad');

const [residente] = defineField('residente');
const [direccion] = defineField('direccion');
const [provinciaResidencia] = defineField('provincia_residencia');
const [cantonResidencia] = defineField('canton_residencia');
const [telefonoCasa] = defineField('telefono_casa');

const [religion] = defineField('religion');
const [estadoCivil] = defineField('estado_civil');
const [cantidadHijos] = defineField('cantidad_hijos');
const [etnia] = defineField('etnia');
const [tipoBeca] = defineField('tipo_beca');
const [nacionalidad] = defineField('nacionalidad');
const [paisNacimiento] = defineField('pais_nacimiento');
const [provinciaNacimiento] = defineField('provincia_nacimiento');
const [cantonNacimiento] = defineField('canton_nacimiento');
const [empresa] = defineField('empresa');
const [direccionEmpresa] = defineField('direccion_empresa');
const [telefonoEmpresa] = defineField('telefono_empresa');
const [cargo] = defineField('cargo');
const [ciudadLaboral] = defineField('ciudad_laboral');

/* ---------------------------------------------------------------
 | Combobox: proxy genérico para campos de texto libre donde value===label
 | (sede, provincias, cantones). Sincroniza con el campo vee-validate real.
 |----------------------------------------------------------------*/
function comboProxy(fieldRef: { value: string | null | undefined }) {
    const selected = ref<{ value: string; label: string } | null>(
        fieldRef.value ? { value: fieldRef.value, label: fieldRef.value } : null,
    );
    watch(selected, (v) => { fieldRef.value = v ? v.value : ''; });
    watch(() => fieldRef.value, (v) => { if (!v) selected.value = null; });
    return selected;
}

const selectedSede = comboProxy(sede);
const selectedProvinciaColegio = comboProxy(provinciaColegio);
const selectedProvinciaUniversidad = comboProxy(provinciaUniversidad);
const selectedProvinciaResidencia = comboProxy(provinciaResidencia);
const selectedCantonResidencia = comboProxy(cantonResidencia);
const selectedProvinciaNacimiento = comboProxy(provinciaNacimiento);
const selectedCantonNacimiento = comboProxy(cantonNacimiento);

// Estado civil: value !== label, se maneja aparte con su propia lista.
const selectedEstadoCivil = ref<{ value: string; label: string } | null>(
    ESTADOS_CIVILES.find((e) => e.value === estadoCivil.value) ?? null,
);
watch(selectedEstadoCivil, (v) => { estadoCivil.value = v ? v.value : ''; });

// Cascada provincia -> cantón: al cambiar la provincia, se limpia el cantón
// (solo cuando el usuario cambia de provincia después de montar la vista).
const cantonesResidenciaDisponibles = computed(() => ciudadesDeProvincia(provinciaResidencia.value));
watch(provinciaResidencia, () => { cantonResidencia.value = ''; selectedCantonResidencia.value = null; });

const cantonesNacimientoDisponibles = computed(() => ciudadesDeProvincia(provinciaNacimiento.value));
watch(provinciaNacimiento, () => { cantonNacimiento.value = ''; selectedCantonNacimiento.value = null; });

// Combobox de Carrera (mismo patrón local-object + watch usado en CarreraForm)
const selectedCarrera = ref<{ value: string; label: string } | null>(
    props.perfil?.carrera_id
        ? { value: String(props.perfil.carrera_id), label: props.carreras.find((c) => c.id === props.perfil?.carrera_id)?.nombre ?? '' }
        : null,
);
watch(selectedCarrera, (newVal) => { carreraIdField.value = newVal ? newVal.value : ''; });

async function irAlPaso(destino: number) {
    if (destino <= highestStepVisited.value) {
        currentStep.value = destino;
        return;
    }

    // Solo se puede avanzar (no saltar) validando el paso actual primero.
    const camposPaso = STEP_FIELDS[currentStep.value] ?? [];
    let valido = true;
    for (const campo of camposPaso) {
        const resultado = await validateField(campo as never);
        if (!resultado.valid) valido = false;
    }

    if (valido) {
        highestStepVisited.value = Math.max(highestStepVisited.value, destino);
        currentStep.value = destino;
    }
}

const siguiente = () => irAlPaso(currentStep.value + 1);
const anterior = () => { currentStep.value = Math.max(1, currentStep.value - 1); };

const onSubmit = handleSubmit((formValues) => {
    processing.value = true;

    const payload = {
        ...formValues,
        carrera_id: formValues.carrera_id ? Number(formValues.carrera_id) : null,
        anio_ingreso: formValues.anio_ingreso ? Number(formValues.anio_ingreso) : null,
        anio_graduacion_colegio: formValues.anio_graduacion_colegio ? Number(formValues.anio_graduacion_colegio) : null,
        cantidad_hijos: formValues.cantidad_hijos ? Number(formValues.cantidad_hijos) : 0,
        fecha_graduacion: formValues.fecha_graduacion || null,
    };

    router.put(route('student.perfil.update'), payload, {
        preserveScroll: true,
        onError: (serverErrors: Record<string, string>) => {
            setErrors(serverErrors);
            processing.value = false;
        },
        onFinish: () => { processing.value = false; },
    });
});

/* ---------------------------------------------------------------
 | Paso 4: Familiares — CRUD inline, persistencia inmediata por fila,
 | listado vía @tanstack/vue-table + columns.ts (mismo patrón que
 | Admin/Carreras e Admin/Periodos).
 |----------------------------------------------------------------*/

const editandoFamiliarId = ref<number | null>(null);

const familiarForm = useInertiaForm({
    parentesco: 'padre' as Parentesco,
    nombres: '',
    cedula: '',
    telefono: '',
    correo: '',
    ocupacion: '',
    empresa: '',
});

const selectedParentesco = ref<{ value: Parentesco; label: string }>({ value: 'padre', label: 'Padre' });
watch(selectedParentesco, (newVal) => { familiarForm.parentesco = newVal.value; });

function nuevoFamiliar() {
    editandoFamiliarId.value = null;
    familiarForm.reset();
    familiarForm.clearErrors();
    selectedParentesco.value = { value: 'padre', label: 'Padre' };
}

function editarFamiliar(familiar: Familiar) {
    editandoFamiliarId.value = familiar.id;
    familiarForm.parentesco = familiar.parentesco;
    familiarForm.nombres = familiar.nombres;
    familiarForm.cedula = familiar.cedula ?? '';
    familiarForm.telefono = familiar.telefono ?? '';
    familiarForm.correo = familiar.correo ?? '';
    familiarForm.ocupacion = familiar.ocupacion ?? '';
    familiarForm.empresa = familiar.empresa ?? '';
    familiarForm.clearErrors();
    selectedParentesco.value = { value: familiar.parentesco, label: PARENTESCO_LABELS[familiar.parentesco] };
}

function guardarFamiliar() {
    const opciones = {
        preserveScroll: true,
        onSuccess: () => nuevoFamiliar(),
    };

    if (editandoFamiliarId.value) {
        familiarForm.put(route('student.familiares.update', editandoFamiliarId.value), opciones);
    } else {
        familiarForm.post(route('student.familiares.store'), opciones);
    }
}

function eliminarFamiliar(familiar: Familiar) {
    if (!confirm(`¿Eliminar a ${familiar.nombres} de tus familiares registrados?`)) return;
    router.delete(route('student.familiares.destroy', familiar.id), { preserveScroll: true });
}

const familiarColumns = makeFamiliarColumns({ onEdit: editarFamiliar, onDelete: eliminarFamiliar });

const familiarTable = useVueTable({
    get data() { return props.familiares ?? []; },
    columns: familiarColumns,
    getCoreRowModel: getCoreRowModel(),
});

const inputClass = 'flex h-9 w-full rounded-md border border-slate-200 bg-transparent px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350';
const comboboxTriggerClass = 'w-full justify-between text-left text-sm font-normal';
const comboboxListClass = 'w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950';
</script>

<template>
    <Head title="Completar Mi Perfil" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Completar Mi Perfil
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    {{ estudiante.name }} — completa tus datos académicos, de residencia y familiares.
                </p>
            </div>

            <div class="max-w-4xl w-full mx-auto rounded-2xl border border-slate-200/80 bg-white p-6 sm:p-8 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <!-- Indicador de pasos -->
                <Stepper :model-value="currentStep" class="flex w-full items-start gap-2 mb-8">
                    <StepperItem
                        v-for="s in steps"
                        :key="s.step"
                        :step="s.step"
                        class="relative flex w-full flex-col items-center justify-center"
                        :class="s.step <= highestStepVisited ? '' : 'opacity-60'"
                    >
                        <StepperSeparator
                            v-if="s.step !== steps.length"
                            class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full"
                            :class="s.step < highestStepVisited ? 'bg-indigo-500' : 'bg-slate-200 dark:bg-slate-800'"
                        />
                        <StepperTrigger as-child>
                            <button
                                type="button"
                                class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold transition-colors"
                                :class="[
                                    s.step === currentStep ? 'border-indigo-600 bg-indigo-600 text-white' :
                                    s.step < currentStep ? 'border-indigo-500 bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40' :
                                    'border-slate-200 text-slate-400 dark:border-slate-800',
                                    s.step <= highestStepVisited ? 'cursor-pointer' : 'cursor-not-allowed',
                                ]"
                                @click="irAlPaso(s.step)"
                            >
                                <Check v-if="s.step < currentStep" class="h-4 w-4" />
                                <component :is="s.icon" v-else class="h-4 w-4" />
                            </button>
                        </StepperTrigger>
                        <div class="mt-2 flex flex-col items-center text-center">
                            <StepperTitle class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ s.title }}</StepperTitle>
                            <StepperDescription class="hidden sm:block text-[11px] text-slate-400">{{ s.description }}</StepperDescription>
                        </div>
                    </StepperItem>
                </Stepper>

                <form @submit="onSubmit">
                    <!-- Paso 1: Académico -->
                    <div v-show="currentStep === 1" class="space-y-5">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos Académicos</h3>

                        <div class="flex items-center gap-3 rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 dark:border-indigo-900/40 dark:bg-indigo-950/20">
                            <Building2 class="h-5 w-5 text-indigo-500 shrink-0" />
                            <div>
                                <p class="text-xs font-bold text-indigo-700 dark:text-indigo-400 uppercase tracking-wide">Facultad</p>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ facultadNombre }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField name="tipo_alumno">
                                <FormItem>
                                    <FormLabel>Tipo de Alumno *</FormLabel>
                                    <FormControl><input v-model="tipoAlumno" type="text" :class="inputClass" placeholder="Ej: Regular" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="nivel">
                                <FormItem>
                                    <FormLabel>Nivel *</FormLabel>
                                    <FormControl><input v-model="nivel" type="text" :class="inputClass" placeholder="Ej: Pregrado" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="sede">
                                <FormItem>
                                    <FormLabel>Sede *</FormLabel>
                                    <Combobox v-model="selectedSede" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedSede ? selectedSede.label : 'Selecciona una sede' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron sedes.</ComboboxEmpty>
                                            <ComboboxGroup class="p-1">
                                                <ComboboxItem v-for="s in SEDES_ULEAM" :key="s" :value="{ value: s, label: s }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ s }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                            <FormField name="anio_ingreso">
                                <FormItem>
                                    <FormLabel>Año de Ingreso *</FormLabel>
                                    <FormControl><input v-model="anioIngreso" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" :class="inputClass" placeholder="2024" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="carrera_id">
                                <FormItem class="sm:col-span-2">
                                    <FormLabel>Carrera *</FormLabel>
                                    <Combobox v-model="selectedCarrera" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedCarrera ? selectedCarrera.label : 'Selecciona una carrera' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border border-slate-100 bg-white shadow-lg dark:border-slate-900 dark:bg-slate-950">
                                            <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron carreras.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in carreras" :key="c.id" :value="{ value: String(c.id), label: c.nombre }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ c.nombre }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 dark:border-slate-850 dark:bg-slate-900">
                            <Switch v-model="graduadoPregrado" />
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">¿Ya te graduaste de un pregrado?</p>
                                <p class="text-xs text-slate-400">Actívalo si ya cuentas con un título de tercer nivel previo.</p>
                            </div>
                        </div>
                        <FormField v-if="graduadoPregrado" name="fecha_graduacion">
                            <FormItem class="max-w-xs">
                                <FormLabel>Fecha de Graduación *</FormLabel>
                                <FormControl><input v-model="fechaGraduacion" type="date" :class="inputClass" /></FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <h3 class="text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-850">Trayectoria Educativa Previa</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField name="colegio">
                                <FormItem>
                                    <FormLabel>Colegio de Procedencia</FormLabel>
                                    <FormControl><input v-model="colegio" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="anio_graduacion_colegio">
                                <FormItem>
                                    <FormLabel>Año de Graduación del Colegio</FormLabel>
                                    <FormControl><input v-model="anioGraduacionColegio" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="4" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="provincia_colegio">
                                <FormItem>
                                    <FormLabel>Provincia del Colegio</FormLabel>
                                    <Combobox v-model="selectedProvinciaColegio" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedProvinciaColegio ? selectedProvinciaColegio.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>

                            <FormField name="universidad_procedencia">
                                <FormItem>
                                    <FormLabel>Universidad de Procedencia (si aplica)</FormLabel>
                                    <FormControl><input v-model="universidadProcedencia" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="provincia_universidad">
                                <FormItem>
                                    <FormLabel>Provincia de esa Universidad</FormLabel>
                                    <Combobox v-model="selectedProvinciaUniversidad" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedProvinciaUniversidad ? selectedProvinciaUniversidad.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                        </div>
                    </div>

                    <!-- Paso 2: Residencia -->
                    <div v-show="currentStep === 2" class="space-y-5">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos de Residencia</h3>
                        <div class="flex items-center gap-3 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 dark:border-slate-850 dark:bg-slate-900">
                            <Switch v-model="residente" />
                            <div>
                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">¿Resides en la ciudad de tu sede?</p>
                                <p class="text-xs text-slate-400">Desactívalo si viajas desde otra ciudad o cantón.</p>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField name="direccion">
                                <FormItem class="sm:col-span-2">
                                    <FormLabel>Dirección</FormLabel>
                                    <FormControl><input v-model="direccion" type="text" :class="inputClass" placeholder="Calle principal y secundaria, referencia" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="provincia_residencia">
                                <FormItem>
                                    <FormLabel>Provincia de Residencia</FormLabel>
                                    <Combobox v-model="selectedProvinciaResidencia" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedProvinciaResidencia ? selectedProvinciaResidencia.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="canton_residencia">
                                <FormItem>
                                    <FormLabel>Cantón de Residencia</FormLabel>
                                    <Combobox v-model="selectedCantonResidencia" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :disabled="!provinciaResidencia" :class="comboboxTriggerClass">
                                                        {{ selectedCantonResidencia ? selectedCantonResidencia.label : (provinciaResidencia ? 'Selecciona un cantón' : 'Elige primero la provincia') }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar cantón..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron cantones.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in cantonesResidenciaDisponibles" :key="c" :value="{ value: c, label: c }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ c }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>

                            <FormField name="telefono_casa">
                                <FormItem>
                                    <FormLabel>Teléfono de Casa</FormLabel>
                                    <FormControl>
                                        <InputGroup>
                                            <InputGroupInput v-model="telefonoCasa" type="text" />
                                        </InputGroup>
                                    </FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>
                    </div>

                    <!-- Paso 3: Adicionales -->
                    <div v-show="currentStep === 3" class="space-y-5">
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos Personales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField name="religion">
                                <FormItem>
                                    <FormLabel>Religión</FormLabel>
                                    <FormControl><input v-model="religion" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="estado_civil">
                                <FormItem>
                                    <FormLabel>Estado Civil</FormLabel>
                                    <Combobox v-model="selectedEstadoCivil" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedEstadoCivil ? selectedEstadoCivil.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxGroup class="p-1">
                                                <ComboboxItem v-for="e in ESTADOS_CIVILES" :key="e.value" :value="e" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ e.label }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>

                            <FormField name="cantidad_hijos">
                                <FormItem>
                                    <FormLabel>Cantidad de Hijos</FormLabel>
                                    <FormControl><input v-model="cantidadHijos" type="text" inputmode="numeric" pattern="[0-9]*" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="etnia">
                                <FormItem>
                                    <FormLabel>Etnia</FormLabel>
                                    <FormControl><input v-model="etnia" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="tipo_beca">
                                <FormItem>
                                    <FormLabel>Tipo de Beca</FormLabel>
                                    <FormControl><input v-model="tipoBeca" type="text" :class="inputClass" placeholder="Ej: Excelencia académica" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="nacionalidad">
                                <FormItem>
                                    <FormLabel>Nacionalidad</FormLabel>
                                    <FormControl><input v-model="nacionalidad" type="text" :class="inputClass" placeholder="Ecuatoriana" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="pais_nacimiento">
                                <FormItem>
                                    <FormLabel>País de Nacimiento</FormLabel>
                                    <FormControl><input v-model="paisNacimiento" type="text" :class="inputClass" placeholder="Ecuador" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="provincia_nacimiento">
                                <FormItem>
                                    <FormLabel>Provincia de Nacimiento</FormLabel>
                                    <Combobox v-model="selectedProvinciaNacimiento" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedProvinciaNacimiento ? selectedProvinciaNacimiento.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>

                            <FormField v-slot="{ errorMessage }" name="canton_nacimiento">
                                <FormItem>
                                    <FormLabel>Cantón de Nacimiento</FormLabel>
                                    <Combobox v-model="selectedCantonNacimiento" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :disabled="!provinciaNacimiento" :class="comboboxTriggerClass">
                                                        {{ selectedCantonNacimiento ? selectedCantonNacimiento.label : (provinciaNacimiento ? 'Selecciona un cantón' : 'Elige primero la provincia') }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar cantón..." class="w-full border-0 border-b border-slate-105 bg-transparent px-3 py-2.5 text-sm focus:ring-0 dark:border-slate-850" />
                                            <ComboboxEmpty class="py-2 text-center text-xs text-slate-400">No se encontraron cantones.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in cantonesNacimientoDisponibles" :key="c" :value="{ value: c, label: c }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800">
                                                    {{ c }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                        </div>

                        <h3 class="text-sm font-bold text-slate-900 dark:text-white pt-2 border-t border-slate-100 dark:border-slate-850">Datos Laborales</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField name="empresa">
                                <FormItem>
                                    <FormLabel>Empresa donde Laboras</FormLabel>
                                    <FormControl><input v-model="empresa" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="cargo">
                                <FormItem>
                                    <FormLabel>Cargo</FormLabel>
                                    <FormControl><input v-model="cargo" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="direccion_empresa">
                                <FormItem>
                                    <FormLabel>Dirección de la Empresa</FormLabel>
                                    <FormControl><input v-model="direccionEmpresa" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="telefono_empresa">
                                <FormItem>
                                    <FormLabel>Teléfono de la Empresa</FormLabel>
                                    <FormControl><input v-model="telefonoEmpresa" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="ciudad_laboral">
                                <FormItem>
                                    <FormLabel>Ciudad donde Laboras</FormLabel>
                                    <FormControl><input v-model="ciudadLaboral" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                        </div>
                    </div>

                    <!-- Navegación pasos 1-3 -->
                    <div v-show="currentStep < 4" class="flex items-center justify-between border-t border-slate-100 pt-5 mt-6 dark:border-slate-850">
                        <Button type="button" variant="outline" :disabled="currentStep === 1" @click="anterior">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Atrás
                        </Button>
                        <Button type="button" class="bg-indigo-600 hover:bg-indigo-500 text-white" @click="siguiente">
                            Siguiente <ArrowRight class="ml-1.5 h-4 w-4" />
                        </Button>
                    </div>
                </form>

                <!-- Paso 4: Familiares (fuera del <form> del wizard: persiste aparte) -->
                <div v-show="currentStep === 4" class="space-y-5">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Familiares y Representantes</h3>

                    <div class="rounded-xl border border-slate-100 dark:border-slate-850 overflow-hidden">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="headerGroup in familiarTable.getHeaderGroups()" :key="headerGroup.id">
                                    <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <template v-if="familiarTable.getRowModel().rows.length">
                                    <TableRow v-for="row in familiarTable.getRowModel().rows" :key="row.id">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="familiarColumns.length" class="h-20 text-center text-slate-400">
                                        Aún no has registrado familiares.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <form @submit.prevent="guardarFamiliar" class="rounded-xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-850 dark:bg-slate-900 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold text-slate-500 uppercase">
                                {{ editandoFamiliarId ? 'Editando familiar' : 'Agregar familiar' }}
                            </p>
                            <Button v-if="editandoFamiliarId" type="button" variant="ghost" size="sm" class="h-7 gap-1 text-xs" @click="nuevoFamiliar">
                                <X class="h-3.5 w-3.5" /> Cancelar edición
                            </Button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Parentesco *</label>
                                <Combobox v-model="selectedParentesco" by="value">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal bg-white dark:bg-slate-950">
                                                {{ selectedParentesco.label }}
                                                <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                            </Button>
                                        </ComboboxTrigger>
                                    </ComboboxAnchor>
                                    <ComboboxList :class="comboboxListClass">
                                        <ComboboxGroup class="p-1">
                                            <ComboboxItem
                                                v-for="(label, value) in PARENTESCO_LABELS"
                                                :key="value"
                                                :value="{ value, label }"
                                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm hover:bg-slate-50 data-[state=checked]:bg-slate-100 dark:hover:bg-slate-900 dark:data-[state=checked]:bg-slate-800"
                                            >
                                                {{ label }}
                                                <ComboboxItemIndicator><Check class="h-4 w-4 text-indigo-650" /></ComboboxItemIndicator>
                                            </ComboboxItem>
                                        </ComboboxGroup>
                                    </ComboboxList>
                                </Combobox>
                                <p v-if="familiarForm.errors.parentesco" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.parentesco }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nombres Completos *</label>
                                <input v-model="familiarForm.nombres" type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950" />
                                <p v-if="familiarForm.errors.nombres" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.nombres }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cédula</label>
                                <input v-model="familiarForm.cedula" type="text" maxlength="10" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950" />
                                <p v-if="familiarForm.errors.cedula" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.cedula }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Teléfono</label>
                                <input v-model="familiarForm.telefono" type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950" />
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Correo</label>
                                <input v-model="familiarForm.correo" type="email" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950" />
                                <p v-if="familiarForm.errors.correo" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.correo }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Ocupación</label>
                                <input v-model="familiarForm.ocupacion" type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Empresa donde Labora</label>
                                <input v-model="familiarForm.empresa" type="text" class="flex h-9 w-full rounded-md border border-slate-200 bg-white px-3 py-1 text-sm shadow-sm focus:ring-1 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="familiarForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white">
                                <Plus v-if="!editandoFamiliarId" class="mr-1.5 h-4 w-4" />
                                <Save v-else class="mr-1.5 h-4 w-4" />
                                {{ editandoFamiliarId ? 'Guardar Cambios' : 'Agregar Familiar' }}
                            </Button>
                        </div>
                    </form>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-5 dark:border-slate-850">
                        <Button type="button" variant="outline" @click="anterior">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Atrás
                        </Button>
                        <Button type="button" :disabled="processing" class="bg-emerald-600 hover:bg-emerald-500 text-white" @click="onSubmit">
                            <Save class="mr-1.5 h-4 w-4" /> Finalizar y Guardar Perfil
                        </Button>
                    </div>
                </div>
            </div>

            <div class="max-w-4xl w-full mx-auto text-center">
                <Link :href="route('student.perfil')" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-300">
                    Volver a Mi Perfil
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
