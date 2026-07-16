<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, GraduationCap, Home, IdCard, Users } from 'lucide-vue-next';
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
    breadcrumbs?: BreadcrumbItemType[];
}>();

const perfil = props.estudiante.perfilEstudiante;
const datos = props.estudiante.datosAdicionales;

const dato = (valor: string | number | null | undefined) => (valor === null || valor === undefined || valor === '' ? '—' : String(valor));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Perfil de ${estudiante.name}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        {{ estudiante.name }}
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        {{ estudiante.email }} <span v-if="estudiante.cedula">· Cédula {{ estudiante.cedula }}</span>
                    </p>
                </div>
                <Button as-child variant="outline">
                    <Link :href="route('admin.estudiantes.perfiles.index')">
                        <ArrowLeft class="h-4 w-4 mr-1.5" /> Volver
                    </Link>
                </Button>
            </div>

            <div v-if="!perfil" class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-700 dark:border-amber-900/40 dark:bg-amber-950/20 dark:text-amber-400">
                Este estudiante todavía no ha completado su perfil.
            </div>

            <template v-else>
                <!-- Académico -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="mb-4 flex items-center gap-2">
                        <GraduationCap class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos Académicos</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Facultad</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.facultad?.nombre) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Carrera</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.carrera?.nombre) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Tipo de Alumno</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.tipo_alumno) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Nivel</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.nivel) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Sede</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.sede) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Año de Ingreso</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.anio_ingreso) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Graduado de Pregrado</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ perfil.graduado_pregrado ? 'Sí' : 'No' }}</p></div>
                        <div v-if="perfil.graduado_pregrado"><p class="text-xs font-bold text-slate-400 uppercase">Fecha de Graduación</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.fecha_graduacion) }}</p></div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-4 sm:grid-cols-3 lg:grid-cols-4 dark:border-slate-850">
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Colegio</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.colegio) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Año Graduación Colegio</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.anio_graduacion_colegio) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Provincia del Colegio</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.provincia_colegio) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Universidad de Procedencia</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.universidad_procedencia) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Provincia de esa Universidad</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.provincia_universidad) }}</p></div>
                    </div>
                </div>

                <!-- Residencia -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="mb-4 flex items-center gap-2">
                        <Home class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Residencia</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div><p class="text-xs font-bold text-slate-400 uppercase">¿Reside en la sede?</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ perfil.residente ? 'Sí' : 'No' }}</p></div>
                        <div class="sm:col-span-2"><p class="text-xs font-bold text-slate-400 uppercase">Dirección</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.direccion) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Provincia</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.provincia_residencia) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Cantón</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.canton_residencia) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Teléfono de Casa</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(perfil.telefono_casa) }}</p></div>
                    </div>
                </div>

                <!-- Adicionales -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="mb-4 flex items-center gap-2">
                        <IdCard class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Datos Adicionales</h3>
                    </div>
                    <div v-if="datos" class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Religión</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.religion) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Estado Civil</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.estado_civil) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Cantidad de Hijos</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.cantidad_hijos) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Etnia</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.etnia) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Tipo de Beca</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.tipo_beca) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Nacionalidad</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.nacionalidad) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">País de Nacimiento</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.pais_nacimiento) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Provincia de Nacimiento</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.provincia_nacimiento) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Cantón de Nacimiento</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.canton_nacimiento) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Empresa</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.empresa) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Cargo</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.cargo) }}</p></div>
                        <div><p class="text-xs font-bold text-slate-400 uppercase">Ciudad Laboral</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(datos.ciudad_laboral) }}</p></div>
                    </div>
                    <p v-else class="text-sm text-slate-400">Sin datos adicionales registrados.</p>
                </div>

                <!-- Familiares -->
                <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="mb-4 flex items-center gap-2">
                        <Users class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Familiares y Representantes</h3>
                    </div>
                    <div v-if="estudiante.familiares.length" class="divide-y divide-slate-100 dark:divide-slate-850">
                        <div v-for="familiar in estudiante.familiares" :key="familiar.id" class="grid grid-cols-2 gap-4 py-3 sm:grid-cols-4">
                            <div><p class="text-xs font-bold text-slate-400 uppercase">Nombres</p><p class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ familiar.nombres }}</p></div>
                            <div><p class="text-xs font-bold text-slate-400 uppercase">Parentesco</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ PARENTESCO_LABELS[familiar.parentesco] }}</p></div>
                            <div><p class="text-xs font-bold text-slate-400 uppercase">Teléfono</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(familiar.telefono) }}</p></div>
                            <div><p class="text-xs font-bold text-slate-400 uppercase">Correo</p><p class="text-sm text-slate-700 dark:text-slate-300">{{ dato(familiar.correo) }}</p></div>
                        </div>
                    </div>
                    <p v-else class="text-sm text-slate-400">Sin familiares registrados.</p>
                </div>
            </template>
        </div>
    </AppSidebarLayout>
</template>
