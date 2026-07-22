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
import {
    AlertDialog, AlertDialogAction, AlertDialogContent, AlertDialogDescription,
    AlertDialogFooter, AlertDialogHeader, AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { useProvinciasCiudadesEcuador } from '@/composables/provincias_ciudades_ecuador';
import { makeFamiliarColumns, PARENTESCO_LABELS, type Familiar, type Parentesco } from './columns';
import paisesData from '@/data/countries.json';
import etniasData from '@/data/etnias_ecuador.json';

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

const { provincias: PROVINCIAS_ECUADOR, ciudadesDeProvincia, todasLasCiudades: CIUDADES_ECUADOR } = useProvinciasCiudadesEcuador();

const NIVELES_ACADEMICOS = [
    '1er Semestre', '2do Semestre', '3er Semestre', '4to Semestre', '5to Semestre',
    '6to Semestre', '7mo Semestre', '8vo Semestre', '9no Semestre', '10mo Semestre',
];

const SEDES_ULEAM = ['Matriz Manta', 'Extensión Bahía de Caráquez', 'Extensión Chone', 'Extensión El Carmen', 'Extensión Pedernales', 'Extensión Sucre'];
const SEDE_DEFECTO = 'Matriz Manta';

const ESTADOS_CIVILES: { value: string; label: string }[] = [
    { value: 'soltero', label: 'Soltero/a' },
    { value: 'casado', label: 'Casado/a' },
    { value: 'divorciado', label: 'Divorciado/a' },
    { value: 'viudo', label: 'Viudo/a' },
    { value: 'union_libre', label: 'Unión libre' },
];

interface Pais { code: string; nombre: string; gentilicio: string }
interface Etnia { code: string; nombre: string }

const PAISES = paisesData as Pais[];
const capitalizar = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

const NOMBRES_PAISES = [...PAISES].map((p) => p.nombre).sort((a, b) => a.localeCompare(b, 'es'));
const GENTILICIOS = [...new Set(PAISES.map((p) => capitalizar(p.gentilicio)))].sort((a, b) => a.localeCompare(b, 'es'));
const NOMBRES_ETNIAS = (etniasData as Etnia[]).map((e) => e.nombre);

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
    3: ['religion', 'estado_civil', 'cantidad_hijos', 'etnia', 'tipo_beca', 'nacionalidad', 'pais_nacimiento', 'provincia_nacimiento', 'canton_nacimiento', 'trabaja_actualmente', 'empresa', 'direccion_empresa', 'telefono_empresa', 'cargo', 'ciudad_laboral'],
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
        trabaja_actualmente: z.boolean(),
        empresa: z.string().optional().nullable(),
        direccion_empresa: z.string().optional().nullable(),
        telefono_empresa: z.string().optional().nullable(),
        cargo: z.string().optional().nullable(),
        ciudad_laboral: z.string().optional().nullable(),
    }).superRefine((data, ctx) => {
        // El switch "¿Trabajas actualmente?" hace obligatorios estos 5
        // campos solo cuando está activado; si no, quedan libres.
        if (data.trabaja_actualmente) {
            const camposLaborales: { key: 'empresa' | 'cargo' | 'direccion_empresa' | 'telefono_empresa' | 'ciudad_laboral' }[] = [
                { key: 'empresa' }, { key: 'cargo' }, { key: 'direccion_empresa' }, { key: 'telefono_empresa' }, { key: 'ciudad_laboral' },
            ];
            for (const campo of camposLaborales) {
                if (!data[campo.key]) {
                    ctx.addIssue({ code: z.ZodIssueCode.custom, path: [campo.key], message: 'Requerido.' });
                }
            }
        }

        // Espejo del required_if:graduado_pregrado,1 del servidor: si no se
        // valida también aquí, el estudiante puede pasar los 4 pasos sin
        // darse cuenta de que falta la fecha, y el guardado se rechaza recién
        // en el servidor sin que el wizard lo señale en el paso correcto.
        if (data.graduado_pregrado && !data.fecha_graduacion) {
            ctx.addIssue({ code: z.ZodIssueCode.custom, path: ['fecha_graduacion'], message: 'Requerido.' });
        }
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
        // Siempre arranca en "off": aunque ya haya datos laborales guardados,
        // el estudiante debe activar el switch explícitamente para verlos/editarlos.
        trabaja_actualmente: false,
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
const [trabajaActualmente] = defineField('trabaja_actualmente');
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
const selectedNivel = comboProxy(nivel);
const selectedCiudadLaboral = comboProxy(ciudadLaboral);
const selectedProvinciaColegio = comboProxy(provinciaColegio);
const selectedProvinciaUniversidad = comboProxy(provinciaUniversidad);
const selectedProvinciaResidencia = comboProxy(provinciaResidencia);
const selectedCantonResidencia = comboProxy(cantonResidencia);
const selectedProvinciaNacimiento = comboProxy(provinciaNacimiento);
const selectedCantonNacimiento = comboProxy(cantonNacimiento);
const selectedPaisNacimiento = comboProxy(paisNacimiento);
const selectedNacionalidad = comboProxy(nacionalidad);
const selectedEtnia = comboProxy(etnia);

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

// Paso 4 (familiares) no vive en el schema de vee-validate porque se
// persiste aparte; se exige al menos un familiar completo antes de poder
// finalizar (también se valida en el servidor como defensa adicional).
const tieneFamiliares = computed(() => (props.familiares?.length ?? 0) > 0);

// Alert-dialog con el detalle de qué falta llenar: antes esto solo saltaba
// de paso en silencio y el estudiante no entendía por qué no se guardaba.
const mostrarAlertaFaltantes = ref(false);
const camposFaltantes = ref<string[]>([]);

const ETIQUETAS_CAMPOS: Record<string, string> = {
    tipo_alumno: 'Tipo de Alumno',
    nivel: 'Nivel',
    sede: 'Sede',
    carrera_id: 'Carrera',
    anio_ingreso: 'Año de Ingreso',
    fecha_graduacion: 'Fecha de Graduación',
    empresa: 'Empresa donde Laboras',
    cargo: 'Cargo',
    direccion_empresa: 'Dirección de la Empresa',
    telefono_empresa: 'Teléfono de la Empresa',
    ciudad_laboral: 'Ciudad donde Laboras',
};

function abrirAlertaFaltantes(campos: string[]) {
    camposFaltantes.value = campos;
    mostrarAlertaFaltantes.value = true;
}

// Salta al primer paso (1-3) que tenga un campo dentro de las claves dadas,
// para que el estudiante caiga justo donde está el dato que falta.
function irAlPrimerPasoConError(claves: string[]) {
    const primerPasoConError = ([1, 2, 3] as const).find((paso) =>
        STEP_FIELDS[paso].some((campo) => claves.includes(campo)),
    );
    if (primerPasoConError) {
        currentStep.value = primerPasoConError;
        highestStepVisited.value = Math.max(highestStepVisited.value, primerPasoConError);
    } else if (claves.includes('familiares')) {
        currentStep.value = 4;
        highestStepVisited.value = Math.max(highestStepVisited.value, 4);
    }
}

const onSubmit = handleSubmit(
    (formValues) => {
        if (!tieneFamiliares.value) {
            abrirAlertaFaltantes(['Al menos un familiar con sus datos completos (paso 4: Familiares)']);
            irAlPrimerPasoConError(['familiares']);
            return;
        }

        processing.value = true;

        const payload = {
            ...formValues,
            carrera_id: formValues.carrera_id ? Number(formValues.carrera_id) : null,
            anio_ingreso: formValues.anio_ingreso ? Number(formValues.anio_ingreso) : null,
            anio_graduacion_colegio: formValues.anio_graduacion_colegio ? Number(formValues.anio_graduacion_colegio) : null,
            cantidad_hijos: formValues.cantidad_hijos ? Number(formValues.cantidad_hijos) : 0,
            fecha_graduacion: formValues.fecha_graduacion || null,
        };

        // eslint-disable-next-line no-console
        console.log('[Perfil] Enviando al servidor:', payload);

        router.put(route('student.perfil.update'), payload, {
            preserveScroll: true,
            onError: (serverErrors: Record<string, string>) => {
                const claves = Object.keys(serverErrors);
                if (claves.length > 0) {
                    // eslint-disable-next-line no-console
                    console.warn('[Perfil] El servidor rechazó el guardado, datos faltantes/ inválidos:', serverErrors);
                    abrirAlertaFaltantes(
                        serverErrors.familiares
                            ? [serverErrors.familiares]
                            : claves.map((campo) => ETIQUETAS_CAMPOS[campo] ?? campo),
                    );
                    irAlPrimerPasoConError(claves);
                }
                setErrors(serverErrors);
                processing.value = false;
            },
            onFinish: () => { processing.value = false; },
        });
    },
    ({ errors }) => {
        // Envío inválido: en vez de saltar de paso en silencio, se explica
        // exactamente qué falta antes de mover al estudiante al paso correcto.
        const claves = Object.keys(errors);
        // eslint-disable-next-line no-console
        console.warn('[Perfil] Validación del formulario falló antes de enviar, campos faltantes/inválidos:', errors);
        abrirAlertaFaltantes(claves.map((campo) => ETIQUETAS_CAMPOS[campo] ?? campo));
        irAlPrimerPasoConError(claves);
    },
);

/* ---------------------------------------------------------------
 | Paso 4: Familiares — CRUD inline, persistencia inmediata por fila,
 | listado vía @tanstack/vue-table + columns.ts (mismo patrón que
 | Admin/Carreras e Admin/Periodos).
 |----------------------------------------------------------------*/

const editandoFamiliarId = ref<number | null>(null);

const familiarForm = useInertiaForm({
    parentesco: 'padre' as Parentesco,
    nombres: '',
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

const inputClass = 'flex h-9 w-full rounded-md border bg-[var(--sispaa-background)] text-[var(--sispaa-text)] px-3 py-1 text-sm shadow-sm border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:ring-1 focus:ring-[var(--sispaa-primary)] focus:border-[var(--sispaa-primary)]';
const comboboxTriggerClass = 'w-full justify-between text-left text-sm font-normal';
const comboboxListClass = 'w-[var(--reka-combobox-trigger-width)] min-w-[220px] rounded-lg border bg-[var(--sispaa-background)] shadow-lg border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]';
</script>

<template>
    <Head title="Completar Mi Perfil" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Completar Mi Perfil
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    {{ estudiante.name }} — completa tus datos académicos, de residencia y familiares.
                </p>
            </div>

            <div class="mx-auto w-full max-w-4xl rounded-2xl p-5 shadow-sm sm:p-8 bg-[var(--sispaa-surface)]">
                <!-- Indicador de pasos -->
                <Stepper :model-value="currentStep" class="mb-8 flex w-full items-start gap-2">
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
                            :class="s.step < highestStepVisited ? 'bg-[var(--sispaa-primary)]' : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]'"
                        />
                        <StepperTrigger as-child>
                            <button
                                type="button"
                                class="z-10 flex h-9 w-9 shrink-0 items-center justify-center rounded-full border-2 text-sm font-bold transition-colors"
                                :class="[
                                    s.step === currentStep ? 'border-[var(--sispaa-primary)] bg-[var(--sispaa-primary)] text-white' :
                                    s.step < currentStep ? 'border-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]' :
                                    'border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] opacity-50 text-[var(--sispaa-text)]',
                                    s.step <= highestStepVisited ? 'cursor-pointer' : 'cursor-not-allowed',
                                ]"
                                @click="irAlPaso(s.step)"
                            >
                                <Check v-if="s.step < currentStep" class="h-4 w-4" />
                                <component :is="s.icon" v-else class="h-4 w-4" />
                            </button>
                        </StepperTrigger>
                        <div class="mt-2 flex flex-col items-center text-center">
                            <StepperTitle class="text-xs font-bold opacity-80 text-[var(--sispaa-text)]">{{ s.title }}</StepperTitle>
                            <StepperDescription class="hidden text-[11px] opacity-50 text-[var(--sispaa-text)] sm:block">{{ s.description }}</StepperDescription>
                        </div>
                    </StepperItem>
                </Stepper>

                <p class="mb-5 text-xs opacity-50 text-[var(--sispaa-text)]">
                    Los campos marcados con <span class="font-bold text-rose-500">*</span> son obligatorios.
                </p>

                <form @submit="onSubmit">
                    <!-- Paso 1: Académico -->
                    <div v-show="currentStep === 1" class="space-y-5">
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Datos Académicos</h3>

                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3 bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] border-[color:color-mix(in_srgb,var(--sispaa-primary)_25%,transparent)]">
                            <Building2 class="h-5 w-5 shrink-0 text-[var(--sispaa-primary)]" />
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[var(--sispaa-primary)]">Facultad</p>
                                <p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ facultadNombre }}</p>
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
                            <FormField v-slot="{ errorMessage }" name="nivel">
                                <FormItem>
                                    <FormLabel>Nivel *</FormLabel>
                                    <Combobox v-model="selectedNivel" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedNivel ? selectedNivel.label : 'Selecciona un nivel' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron niveles.</ComboboxEmpty>
                                            <ComboboxGroup class="p-1">
                                                <ComboboxItem v-for="n in NIVELES_ACADEMICOS" :key="n" :value="{ value: n, label: n }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ n }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
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
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron sedes.</ComboboxEmpty>
                                            <ComboboxGroup class="p-1">
                                                <ComboboxItem v-for="s in SEDES_ULEAM" :key="s" :value="{ value: s, label: s }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ s }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                                        <ComboboxList class="w-[var(--reka-combobox-trigger-width)] min-w-[250px] rounded-lg border shadow-lg bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                            <ComboboxInput placeholder="Buscar carrera..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron carreras.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in carreras" :key="c.id" :value="{ value: String(c.id), label: c.nombre }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ c.nombre }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                        </div>

                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3 bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Switch v-model="graduadoPregrado" />
                            <div>
                                <p class="text-sm font-semibold text-[var(--sispaa-text)]">¿Ya te graduaste de un pregrado?</p>
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Actívalo si ya cuentas con un título de tercer nivel previo.</p>
                            </div>
                        </div>
                        <FormField v-if="graduadoPregrado" name="fecha_graduacion">
                            <FormItem class="max-w-xs">
                                <FormLabel>Fecha de Graduación *</FormLabel>
                                <FormControl><input v-model="fechaGraduacion" type="date" :class="inputClass" /></FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <h3 class="text-sm font-bold text-[var(--sispaa-text)] pt-2 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">Trayectoria Educativa Previa</h3>
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
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Datos de Residencia</h3>
                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3 bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Switch v-model="residente" />
                            <div>
                                <p class="text-sm font-semibold text-[var(--sispaa-text)]">¿Resides en la ciudad de tu sede?</p>
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Desactívalo si viajas desde otra ciudad o cantón.</p>
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
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                                            <ComboboxInput placeholder="Buscar cantón..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron cantones.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in cantonesResidenciaDisponibles" :key="c" :value="{ value: c, label: c }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ c }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Datos Personales</h3>
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
                                                <ComboboxItem v-for="e in ESTADOS_CIVILES" :key="e.value" :value="e" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ e.label }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                            <FormField v-slot="{ errorMessage }" name="etnia">
                                <FormItem>
                                    <FormLabel>Etnia</FormLabel>
                                    <Combobox v-model="selectedEtnia" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedEtnia ? selectedEtnia.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron etnias.</ComboboxEmpty>
                                            <ComboboxGroup class="p-1">
                                                <ComboboxItem v-for="e in NOMBRES_ETNIAS" :key="e" :value="{ value: e, label: e }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ e }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                            <FormField name="tipo_beca">
                                <FormItem>
                                    <FormLabel>Tipo de Beca</FormLabel>
                                    <FormControl><input v-model="tipoBeca" type="text" :class="inputClass" placeholder="Ej: Excelencia académica" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ errorMessage }" name="nacionalidad">
                                <FormItem>
                                    <FormLabel>Nacionalidad</FormLabel>
                                    <Combobox v-model="selectedNacionalidad" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedNacionalidad ? selectedNacionalidad.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar nacionalidad..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron nacionalidades.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="g in GENTILICIOS" :key="g" :value="{ value: g, label: g }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ g }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ errorMessage }" name="pais_nacimiento">
                                <FormItem>
                                    <FormLabel>País de Nacimiento</FormLabel>
                                    <Combobox v-model="selectedPaisNacimiento" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedPaisNacimiento ? selectedPaisNacimiento.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar país..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron países.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in NOMBRES_PAISES" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
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
                                            <ComboboxInput placeholder="Buscar provincia..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron provincias.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="p in PROVINCIAS_ECUADOR" :key="p" :value="{ value: p, label: p }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ p }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
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
                                            <ComboboxInput placeholder="Buscar cantón..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron cantones.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in cantonesNacimientoDisponibles" :key="c" :value="{ value: c, label: c }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ c }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                        </div>

                        <h3 class="text-sm font-bold text-[var(--sispaa-text)] pt-2 border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">Datos Laborales</h3>
                        <div class="flex items-center gap-3 rounded-xl border px-4 py-3 bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Switch v-model="trabajaActualmente" />
                            <div>
                                <p class="text-sm font-semibold text-[var(--sispaa-text)]">¿Trabajas actualmente?</p>
                                <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Actívalo para registrar dónde laboras; al hacerlo, esos datos pasan a ser obligatorios.</p>
                            </div>
                        </div>
                        <div v-if="trabajaActualmente" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <FormField name="empresa">
                                <FormItem>
                                    <FormLabel>Empresa donde Laboras *</FormLabel>
                                    <FormControl><input v-model="empresa" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="cargo">
                                <FormItem>
                                    <FormLabel>Cargo *</FormLabel>
                                    <FormControl><input v-model="cargo" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="direccion_empresa">
                                <FormItem>
                                    <FormLabel>Dirección de la Empresa *</FormLabel>
                                    <FormControl><input v-model="direccionEmpresa" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField name="telefono_empresa">
                                <FormItem>
                                    <FormLabel>Teléfono de la Empresa *</FormLabel>
                                    <FormControl><input v-model="telefonoEmpresa" type="text" :class="inputClass" /></FormControl>
                                    <FormMessage />
                                </FormItem>
                            </FormField>
                            <FormField v-slot="{ errorMessage }" name="ciudad_laboral">
                                <FormItem>
                                    <FormLabel>Ciudad donde Laboras *</FormLabel>
                                    <Combobox v-model="selectedCiudadLaboral" by="value">
                                        <ComboboxAnchor as-child>
                                            <ComboboxTrigger as-child>
                                                <FormControl>
                                                    <Button type="button" variant="outline" :class="comboboxTriggerClass">
                                                        {{ selectedCiudadLaboral ? selectedCiudadLaboral.label : 'Sin especificar' }}
                                                        <ChevronsUpDown class="h-4 w-4 opacity-50" />
                                                    </Button>
                                                </FormControl>
                                            </ComboboxTrigger>
                                        </ComboboxAnchor>
                                        <ComboboxList :class="comboboxListClass">
                                            <ComboboxInput placeholder="Buscar ciudad..." class="w-full border-0 border-b bg-transparent px-3 py-2.5 text-sm text-[var(--sispaa-text)] focus:ring-0 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]" />
                                            <ComboboxEmpty class="py-2 text-center text-xs opacity-50 text-[var(--sispaa-text)]">No se encontraron ciudades.</ComboboxEmpty>
                                            <ComboboxGroup class="max-h-60 overflow-y-auto p-1">
                                                <ComboboxItem v-for="c in CIUDADES_ECUADOR" :key="c" :value="{ value: c, label: c }" class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]">
                                                    {{ c }}
                                                    <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                                </ComboboxItem>
                                            </ComboboxGroup>
                                        </ComboboxList>
                                    </Combobox>
                                    <FormMessage v-if="errorMessage" />
                                </FormItem>
                            </FormField>
                        </div>
                    </div>

                    <!-- Navegación pasos 1-3 -->
                    <div v-show="currentStep < 4" class="mt-6 flex items-center justify-between border-t pt-5 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <Button type="button" variant="outline" :disabled="currentStep === 1" @click="anterior">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Atrás
                        </Button>
                        <Button type="button" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]" @click="siguiente">
                            Siguiente <ArrowRight class="ml-1.5 h-4 w-4" />
                        </Button>
                    </div>
                </form>

                <!-- Paso 4: Familiares (fuera del <form> del wizard: persiste aparte) -->
                <div v-show="currentStep === 4" class="space-y-5">
                    <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Familiares y Representantes</h3>
                    <p class="text-xs opacity-50 text-[var(--sispaa-text)]">
                        Debes registrar al menos <span class="font-bold text-rose-500">*</span> un familiar con sus datos completos para poder finalizar.
                    </p>

                    <div v-if="!tieneFamiliares" class="rounded-xl border px-4 py-3 text-sm bg-[color:color-mix(in_srgb,#E4BC57_25%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_65%,black)] border-[color:color-mix(in_srgb,#E4BC57_45%,transparent)]">
                        Aún no has agregado ningún familiar. Agrega al menos uno con el formulario de abajo antes de finalizar tu perfil.
                    </div>

                    <div class="overflow-hidden rounded-xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
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
                                    <TableCell :colspan="familiarColumns.length" class="h-20 text-center opacity-50 text-[var(--sispaa-text)]">
                                        Aún no has registrado familiares.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <form @submit.prevent="guardarFamiliar" class="space-y-4 rounded-xl border p-4 bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <div class="flex items-center justify-between">
                            <p class="text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">
                                {{ editandoFamiliarId ? 'Editando familiar' : 'Agregar familiar' }}
                            </p>
                            <Button v-if="editandoFamiliarId" type="button" variant="ghost" size="sm" class="h-7 gap-1 text-xs" @click="nuevoFamiliar">
                                <X class="h-3.5 w-3.5" /> Cancelar edición
                            </Button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">Parentesco *</label>
                                <Combobox v-model="selectedParentesco" by="value">
                                    <ComboboxAnchor as-child>
                                        <ComboboxTrigger as-child>
                                            <Button type="button" variant="outline" class="w-full justify-between text-left text-sm font-normal bg-[var(--sispaa-background)]">
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
                                                class="flex cursor-pointer items-center justify-between rounded-md px-3 py-2 text-sm text-[var(--sispaa-text)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] data-[state=checked]:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]"
                                            >
                                                {{ label }}
                                                <ComboboxItemIndicator><Check class="h-4 w-4 text-[var(--sispaa-primary)]" /></ComboboxItemIndicator>
                                            </ComboboxItem>
                                        </ComboboxGroup>
                                    </ComboboxList>
                                </Combobox>
                                <p v-if="familiarForm.errors.parentesco" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.parentesco }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">Nombres Completos *</label>
                                <input v-model="familiarForm.nombres" type="text" class="flex h-9 w-full rounded-md border bg-[var(--sispaa-background)] px-3 py-1 text-sm text-[var(--sispaa-text)] shadow-sm border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:ring-1 focus:ring-[var(--sispaa-primary)]" />
                                <p v-if="familiarForm.errors.nombres" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.nombres }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">Teléfono</label>
                                <input v-model="familiarForm.telefono" type="text" class="flex h-9 w-full rounded-md border bg-[var(--sispaa-background)] px-3 py-1 text-sm text-[var(--sispaa-text)] shadow-sm border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:ring-1 focus:ring-[var(--sispaa-primary)]" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">Correo</label>
                                <input v-model="familiarForm.correo" type="email" class="flex h-9 w-full rounded-md border bg-[var(--sispaa-background)] px-3 py-1 text-sm text-[var(--sispaa-text)] shadow-sm border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:ring-1 focus:ring-[var(--sispaa-primary)]" />
                                <p v-if="familiarForm.errors.correo" class="mt-1 text-xs text-rose-500">{{ familiarForm.errors.correo }}</p>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">Ocupación</label>
                                <input v-model="familiarForm.ocupacion" type="text" class="flex h-9 w-full rounded-md border bg-[var(--sispaa-background)] px-3 py-1 text-sm text-[var(--sispaa-text)] shadow-sm border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:ring-1 focus:ring-[var(--sispaa-primary)]" />
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold uppercase opacity-60 text-[var(--sispaa-text)]">Empresa donde Labora</label>
                                <input v-model="familiarForm.empresa" type="text" class="flex h-9 w-full rounded-md border bg-[var(--sispaa-background)] px-3 py-1 text-sm text-[var(--sispaa-text)] shadow-sm border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)] focus:ring-1 focus:ring-[var(--sispaa-primary)]" />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <Button type="submit" :disabled="familiarForm.processing" class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                                <Plus v-if="!editandoFamiliarId" class="mr-1.5 h-4 w-4" />
                                <Save v-else class="mr-1.5 h-4 w-4" />
                                {{ editandoFamiliarId ? 'Guardar Cambios' : 'Agregar Familiar' }}
                            </Button>
                        </div>
                    </form>

                    <div class="flex items-center justify-between border-t pt-5 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <Button type="button" variant="outline" @click="anterior">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Atrás
                        </Button>
                        <Button
                            type="button"
                            :disabled="processing"
                            class="text-white disabled:opacity-50 bg-[var(--sispaa-secondary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_85%,black)]"
                            @click="onSubmit"
                        >
                            <Save class="mr-1.5 h-4 w-4" /> Finalizar y Guardar Perfil
                        </Button>
                    </div>
                </div>
            </div>

            <div class="mx-auto w-full max-w-4xl text-center">
                <Link :href="route('student.perfil')" class="text-xs opacity-50 text-[var(--sispaa-text)] hover:opacity-100">
                    Volver a Mi Perfil
                </Link>
            </div>
        </div>

        <AlertDialog v-model:open="mostrarAlertaFaltantes">
            <AlertDialogContent>
                <AlertDialogHeader>
                    <AlertDialogTitle>Faltan datos por completar</AlertDialogTitle>
                    <AlertDialogDescription>
                        Antes de guardar tu perfil, completa lo siguiente:
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <ul class="list-disc space-y-1 pl-5 text-sm opacity-80 text-[var(--sispaa-text)]">
                    <li v-for="campo in camposFaltantes" :key="campo">{{ campo }}</li>
                </ul>
                <AlertDialogFooter>
                    <AlertDialogAction @click="mostrarAlertaFaltantes = false">Entendido</AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    </AppLayout>
</template>
