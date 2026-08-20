<script setup lang="ts">
import MiPerfilForm from './MiPerfilForm.vue';
import type { BreadcrumbItem } from '@/types';
import type { Familiar } from './columns';

interface Perfil {
    tipo_alumno: string | null; nivel: string | null; sede: string | null;
    carrera_id: number | null; anio_ingreso: number | null; graduado_pregrado: boolean;
    fecha_graduacion: string | null; colegio: string | null;
    anio_graduacion_colegio: number | null; provincia_colegio: string | null;
    universidad_procedencia: string | null; provincia_universidad: string | null;
    residente: boolean; direccion: string | null;
    provincia_residencia: string | null; canton_residencia: string | null;
    telefono_casa: string | null;
}

interface DatosAdicionales {
    religion: string | null; estado_civil: string | null; cantidad_hijos: number | null;
    etnia: string | null; tipo_beca: string | null; nacionalidad: string | null;
    pais_nacimiento: string | null; provincia_nacimiento: string | null;
    canton_nacimiento: string | null; empresa: string | null;
    direccion_empresa: string | null; telefono_empresa: string | null;
    cargo: string | null; ciudad_laboral: string | null;
}

const props = defineProps<{
    estudiante: { id: number; name: string; email: string; cedula: string | null };
    perfil: Perfil | null;
    datosAdicionales: DatosAdicionales | null;
    familiares: Familiar[];
    facultadNombre: string;
    carreras: { id: number; nombre: string }[];
    periodoActivo: { id: number; nombre: string } | null;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Estudiante', href: '/dashboard' },
    { title: 'Mi Perfil', href: '/estudiante/perfil' },
    { title: 'Completar Perfil', href: '' },
];
</script>

<template>
    <MiPerfilForm
        titulo="Completar Mi Perfil"
        :subtitulo="`${props.estudiante.name} — completa tus datos académicos, de residencia y familiares.`"
        :breadcrumbs="breadcrumbs"
        :estudiante="props.estudiante"
        :perfil="props.perfil"
        :datos-adicionales="props.datosAdicionales"
        :familiares="props.familiares"
        :facultad-nombre="props.facultadNombre"
        :carreras="props.carreras"
        :periodo-activo="props.periodoActivo"
    />
</template>
