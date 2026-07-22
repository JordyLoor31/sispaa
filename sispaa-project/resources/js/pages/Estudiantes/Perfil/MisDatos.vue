<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, ClipboardEdit, GraduationCap, Home, IdCard, Users } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { PARENTESCO_LABELS, type Familiar } from './columns';

interface PerfilEstudiante {
    tipo_alumno: string | null;
    nivel: string | null;
    sede: string | null;
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
    facultad?: { id: number; nombre: string } | null;
    carrera?: { id: number; nombre: string } | null;
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

interface EstudianteDetalle {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    perfilEstudiante: PerfilEstudiante | null;
    datosAdicionales: DatosAdicionales | null;
    familiares: Familiar[];
}

const props = defineProps<{
    estudiante: EstudianteDetalle;
}>();

const perfil = props.estudiante.perfilEstudiante;
const datos = props.estudiante.datosAdicionales;

const dato = (valor: string | number | null | undefined) => (valor === null || valor === undefined || valor === '' ? '—' : String(valor));

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Estudiante', href: '/dashboard' },
    { title: 'Mi Perfil', href: '/estudiante/perfil' },
    { title: 'Mis Datos', href: '/estudiante/perfil/datos' },
];
</script>

<template>
    <Head title="Mis Datos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Mis Datos
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Todo lo que has registrado en tu perfil académico, de residencia, adicional y familiar.
                    </p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('student.perfil')">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                        </Link>
                    </Button>
                    <Button as-child class="text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('student.perfil.edit')">
                            <ClipboardEdit class="mr-1.5 h-4 w-4" /> Editar Perfil
                        </Link>
                    </Button>
                </div>
            </div>

            <div v-if="!perfil" class="rounded-2xl border p-6 text-sm bg-[color:color-mix(in_srgb,#E4BC57_25%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_65%,black)] border-[color:color-mix(in_srgb,#E4BC57_45%,transparent)]">
                Todavía no has completado tu perfil.
                <Link :href="route('student.perfil.edit')" class="font-semibold underline">Complétalo aquí</Link>.
            </div>

            <template v-else>
                <!-- Académico -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="mb-4 flex items-center gap-2">
                        <GraduationCap class="h-4 w-4 text-[var(--sispaa-primary)]" />
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Datos Académicos</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Facultad</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.facultad?.nombre) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Carrera</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.carrera?.nombre) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Tipo de Alumno</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.tipo_alumno) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Nivel</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.nivel) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Sede</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.sede) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Año de Ingreso</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.anio_ingreso) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Graduado de Pregrado</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ perfil.graduado_pregrado ? 'Sí' : 'No' }}</p></div>
                        <div v-if="perfil.graduado_pregrado"><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Fecha de Graduación</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.fecha_graduacion) }}</p></div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-4 border-t pt-4 sm:grid-cols-3 lg:grid-cols-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Colegio</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.colegio) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Año Graduación Colegio</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.anio_graduacion_colegio) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Provincia del Colegio</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.provincia_colegio) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Universidad de Procedencia</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.universidad_procedencia) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Provincia de esa Universidad</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.provincia_universidad) }}</p></div>
                    </div>
                </div>

                <!-- Residencia -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="mb-4 flex items-center gap-2">
                        <Home class="h-4 w-4 text-[var(--sispaa-primary)]" />
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Residencia</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">¿Reside en la sede?</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ perfil.residente ? 'Sí' : 'No' }}</p></div>
                        <div class="sm:col-span-2"><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Dirección</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.direccion) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Provincia</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.provincia_residencia) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Cantón</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.canton_residencia) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Teléfono de Casa</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(perfil.telefono_casa) }}</p></div>
                    </div>
                </div>

                <!-- Adicionales -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="mb-4 flex items-center gap-2">
                        <IdCard class="h-4 w-4 text-[var(--sispaa-primary)]" />
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Datos Adicionales</h3>
                    </div>
                    <div v-if="datos" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Religión</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.religion) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Estado Civil</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.estado_civil) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Cantidad de Hijos</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.cantidad_hijos) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Etnia</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.etnia) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Tipo de Beca</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.tipo_beca) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Nacionalidad</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.nacionalidad) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">País de Nacimiento</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.pais_nacimiento) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Provincia de Nacimiento</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.provincia_nacimiento) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Cantón de Nacimiento</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.canton_nacimiento) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Empresa</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.empresa) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Cargo</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.cargo) }}</p></div>
                        <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Ciudad Laboral</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(datos.ciudad_laboral) }}</p></div>
                    </div>
                    <p v-else class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin datos adicionales registrados.</p>
                </div>

                <!-- Familiares -->
                <div class="rounded-2xl p-5 shadow-sm sm:p-6 bg-[var(--sispaa-surface)]">
                    <div class="mb-4 flex items-center gap-2">
                        <Users class="h-4 w-4 text-[var(--sispaa-primary)]" />
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">Familiares y Representantes</h3>
                    </div>
                    <div v-if="estudiante.familiares.length" class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                        <div v-for="familiar in estudiante.familiares" :key="familiar.id" class="grid grid-cols-2 gap-4 py-3 sm:grid-cols-4">
                            <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Nombres</p><p class="text-sm font-semibold text-[var(--sispaa-text)]">{{ familiar.nombres }}</p></div>
                            <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Parentesco</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ PARENTESCO_LABELS[familiar.parentesco] }}</p></div>
                            <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Teléfono</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(familiar.telefono) }}</p></div>
                            <div><p class="text-xs font-bold uppercase opacity-50 text-[var(--sispaa-text)]">Correo</p><p class="text-sm opacity-80 text-[var(--sispaa-text)]">{{ dato(familiar.correo) }}</p></div>
                        </div>
                    </div>
                    <p v-else class="text-sm opacity-50 text-[var(--sispaa-text)]">Sin familiares registrados.</p>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
