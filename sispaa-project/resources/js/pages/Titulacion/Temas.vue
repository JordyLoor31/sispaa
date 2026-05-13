<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <!-- Contenedor principal -->
        <div class="min-h-screen pt-2 pr-3 pl-3" :style="{ backgroundColor: 'var(--sispaa-background)' }">
            <!-- Encabezado con título y filtro -->
            <div class="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: 'var(--sispaa-text)' }">Temas en Desarrollo</h1>
                    <p class="mt-2 text-sm" :style="{ color: '#666' }">Selecciona una carrera para filtrar los temas</p>
                </div>

                <!-- Filtro de Carrera -->
                <div class="flex items-center gap-4">
                    <span class="font-medium" :style="{ color: 'var(--sispaa-text)' }">Filtrar por:</span>
                    <Select v-model="selectedCarrera">
                        <SelectTrigger
                            class="w-47 border-0 focus:ring-0 focus:ring-offset-0 focus-visible:ring-0 focus-visible:ring-offset-0"
                            :style="{
                                borderRadius: '12px',
                                backgroundColor: 'var(--sispaa-surface)',
                            }"
                        >
                            <SelectValue placeholder="Selecciona una carrera" />
                        </SelectTrigger>
                        <SelectContent :style="{ borderRadius: '12px' }">
                            <SelectItem value="all">
                                <span>Todas las carreras</span>
                            </SelectItem>
                            <SelectItem value="agropecuaria">
                                <span>Agropecuaria</span>
                            </SelectItem>
                            <SelectItem value="agroindustria">
                                <span>Agroindustria</span>
                            </SelectItem>
                            <SelectItem value="agronegocios">
                                <span>Agronegocios</span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Lista de Temas -->
            <div class="flex flex-col gap-4">
                <div
                    v-for="tema in filteredTemas"
                    :key="tema.id"
                    class="transform transition duration-300 hover:translate-x-2 hover:shadow-md"
                    :style="{
                        backgroundColor: 'var(--sispaa-surface)',
                        borderRadius: '12px',
                        padding: '20px',
                    }"
                >
                    <!-- Etiqueta de carrera -->
                    <div class="mb-3 inline-block" :style="{ backgroundColor: 'var(--sispaa-primary)', borderRadius: '8px' }">
                        <span class="px-3 py-1 text-xs font-semibold" :style="{ color: 'white' }">
                            {{ formatCarrera(tema.carrera) }}
                        </span>
                    </div>

                    <!-- Título del tema -->
                    <h3 class="mb-3 text-lg font-bold" :style="{ color: 'var(--sispaa-text)' }">
                        {{ tema.titulo }}
                    </h3>

                    <!-- Descripción -->
                    <!-- ERROR CORREGIDO: Faltaba cerrar comillas en :style -->
                    <p class="mb-4 text-sm" :style="{ color: '#444' }">
                        {{ tema.descripcion }}
                    </p>

                    <!-- ERROR CORREGIDO: Falta de llaves en :style -->
                    <div class="mb-4 flex items-center gap-2 text-sm" :style="{ color: '#666' }">
                        <span class="font-medium">Docente:</span>
                        <span>{{ tema.docente }}</span>
                    </div>

                    <!-- Estado -->
                    <!-- ERROR CORREGIDO: Faltaba cerrar comillas en :style -->
                    <div class="flex items-center gap-2 text-sm" :style="{ color: '#666' }">
                        <span class="font-medium">Estado:</span>
                        <span
                            class="px-2 py-1"
                            :style="{
                                backgroundColor: getEstadoColor(tema.estado),
                                color: 'white',
                                borderRadius: '6px',
                                fontSize: '11px',
                            }"
                        >
                            {{ tema.estado }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Mensaje cuando no hay temas -->
            <div v-if="filteredTemas.length === 0" class="flex flex-col items-center justify-center py-16 text-center">
                <p class="text-lg font-medium" :style="{ color: '#666' }">No hay temas en desarrollo para la carrera seleccionada</p>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { computed, ref } from 'vue';

interface Tema {
    id: number;
    titulo: string;
    descripcion: string;
    carrera: string;
    docente: string;
    estado: string;
}

// Breadcrumbs
const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Titulación', href: '/titulacion' },
    { title: 'Temas en Desarrollo', href: '/titulacion/temas' },
];

// Datos de temas de ejemplo
const temas = ref<Tema[]>([
    {
        id: 1,
        titulo: 'Impacto de la rotación de cultivos en la sostenibilidad del suelo',
        descripcion: 'Análisis del efecto de diferentes sistemas de rotación de cultivos en la preservación de la fertilidad del suelo',
        carrera: 'agropecuaria',
        docente: 'Ing. Carlos Rodríguez',
        estado: 'Propuesta',
    },
    {
        id: 2,
        titulo: 'Optimización de procesos de fermentación en productos lácteos',
        descripcion: 'Mejora de técnicas de fermentación para aumentar la calidad y vida útil de productos derivados',
        carrera: 'agroindustria',
        docente: 'Dra. María González',
        estado: 'En desarrollo',
    },
    {
        id: 3,
        titulo: 'Estrategias de marketing digital para productos agroecológicos',
        descripcion: 'Implementación de plataformas digitales para comercialización de productos sostenibles',
        carrera: 'agronegocios',
        docente: 'Lic. Juan Martínez',
        estado: 'Propuesta',
    },
    {
        id: 4,
        titulo: 'Manejo integrado de plagas en cultivos de maíz',
        descripcion: 'Desarrollo de estrategias de control biológico y químico sostenible para maximizar rendimientos',
        carrera: 'agropecuaria',
        docente: 'Ing. Roberto Silva',
        estado: 'En desarrollo',
    },
    {
        id: 5,
        titulo: 'Diseño de empaques sostenibles para productos agroindustriales',
        descripcion: 'Creación de soluciones de embalaje biodegradable que mantenga la calidad del producto', // CORREGIDO: "mantengla"
        carrera: 'agroindustria',
        docente: 'Ing. Ana López',
        estado: 'Propuesta',
    },
    {
        id: 6,
        titulo: 'Análisis económico de cadenas de suministro agrícola',
        descripcion: 'Evaluación de costos y eficiencia en la distribución de productos del agro',
        carrera: 'agronegocios',
        docente: 'Econ. Pedro Flores',
        estado: 'En desarrollo',
    },
    {
        id: 7,
        titulo: 'Producción de abono orgánico a partir de residuos agrícolas',
        descripcion: 'Aprovechamiento de desechos para crear fertilizantes naturales de alta calidad',
        carrera: 'agropecuaria',
        docente: 'Ing. Diego Hernández',
        estado: 'En desarrollo',
    },
    {
        id: 8,
        titulo: 'Automatización de procesos en agroindustria',
        descripcion: 'Implementación de tecnología para mejorar la eficiencia en líneas de producción',
        carrera: 'agroindustria',
        docente: 'Ing. Técnico Raúl Gutiérrez',
        estado: 'Propuesta',
    },
    {
        id: 9,
        titulo: 'Modelo de negocio para exportación de productos agrícolas',
        descripcion: 'Desarrollo de estrategia de internacionalización para pequeños productores',
        carrera: 'agronegocios',
        docente: 'Lic. Sofía Mendoza',
        estado: 'En desarrollo',
    },
]);

// Estado reactivo del filtro
const selectedCarrera = ref<string>('all');

// Temas filtrados
const filteredTemas = computed(() => {
    if (selectedCarrera.value === 'all') {
        return temas.value;
    }
    return temas.value.filter((tema) => tema.carrera === selectedCarrera.value);
});

// Funciones auxiliares
const formatCarrera = (carrera: string): string => {
    const carreras: Record<string, string> = {
        agropecuaria: 'Agropecuaria',
        agroindustria: 'Agroindustria',
        agronegocios: 'Agronegocios',
    };
    return carreras[carrera] || carrera;
};

const getEstadoColor = (estado: string): string => {
    const colores: Record<string, string> = {
        Propuesta: '#88C273',
        'En desarrollo': '#536493',
        Completado: '#4CAF50',
    };
    return colores[estado] || '#888';
};
</script>

<style scoped>
/* 
   Nota: Definir :root dentro de un style scoped puede dar problemas 
   de alcance. Es mejor mover estas variables a un archivo CSS global.
*/
:root {
    --sispaa-primary-green: #88c273;
    --sispaa-light-beige: #fff1db;
    --sispaa-dark-beige: #d4bdac;
    --sispaa-sidebar-blue: #536493;
    --sispaa-text-dark: #1a1a1a;
}

div {
    box-sizing: border-box;
}
</style>
