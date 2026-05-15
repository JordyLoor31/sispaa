<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { computed, ref } from 'vue'

// Breadcrumb
const breadcrumbs = [
    { title: 'Vinculación', href: '/vinculacion' },
    { title: 'Actividades', href: '/vinculacion/actividades' },
]

// Filtros
const selectedCarrera = ref('all')
const selectedEstado = ref('all')

// Actividades simuladas (tipo sistema real)
const actividades = ref([
    {
        id: 1,
        nombre: 'Impacto de técnicas agrícolas sostenibles en productores locales',
        descripcion: 'Aplicación de métodos sostenibles para mejorar la productividad en comunidades rurales',
        carrera: 'agropecuaria',
        docente: 'Ing. Carlos Ramírez',
        estado: 'ejecutada',
    },
    {
        id: 2,
        nombre: 'Optimización de procesos en producción de lácteos',
        descripcion: 'Mejoras en técnicas de producción para aumentar la calidad de derivados lácteos',
        carrera: 'agroindustria',
        docente: 'Ing. María Díaz',
        estado: 'pendiente',
    },
    {
        id: 3,
        nombre: 'Estrategias de comercialización agrícola',
        descripcion: 'Diseño de estrategias para mejorar la venta de productos agrícolas',
        carrera: 'agronegocios',
        docente: 'Lic. Ana Torres',
        estado: 'ejecutada',
    },
    {
        id: 4,
        nombre: 'Producción de abonos orgánicos',
        descripcion: 'Implementación de técnicas ecológicas para la elaboración de fertilizantes naturales',
        carrera: 'agropecuaria',
        docente: 'Ing. Roberto Díaz',
        estado: 'pendiente',
    },
])

// Filtro dinámico
const actividadesFiltradas = computed(() => {
    return actividades.value.filter(a => {
        const okCarrera = selectedCarrera.value === 'all' || a.carrera === selectedCarrera.value
        const okEstado = selectedEstado.value === 'all' || a.estado === selectedEstado.value
        return okCarrera && okEstado
    })
})

// ✅ CONTADORES (LO NUEVO)
const total = computed(() => actividades.value.length)
const ejecutadas = computed(() => actividades.value.filter(a => a.estado === 'ejecutada').length)
const pendientes = computed(() => actividades.value.filter(a => a.estado === 'pendiente').length)

// Formato carrera
const formatCarrera = (c) => ({
    agropecuaria: 'Agropecuaria',
    agroindustria: 'Agroindustria',
    agronegocios: 'Agronegocios'
}[c])

// Color estado
const estadoColor = (estado) => {
    return estado === 'ejecutada'
        ? '#86B86B'
        : '#5C6C9E'
}
</script>

<template>
<AppSidebarLayout :breadcrumbs="breadcrumbs">

<div class="min-h-screen pt-2 px-3" style="background-color:#f5f5f5">

    <!-- ✅ HEADER -->
    <div class="mb-6 flex justify-between items-center">

        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Actividades de Vinculación
            </h1>
            <p class="text-sm text-gray-600">
                Filtra por carrera o estado
            </p>
        </div>

        <!-- Filtros -->
        <div class="flex items-center gap-3">

            <span class="text-sm text-gray-700">Filtrar por:</span>

            <Select v-model="selectedCarrera">
                <SelectTrigger class="w-44 bg-gray-200 border-0" style="border-radius:12px">
                    <SelectValue placeholder="Todas las carreras"/>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todas las carreras</SelectItem>
                    <SelectItem value="agropecuaria">Agropecuaria</SelectItem>
                    <SelectItem value="agroindustria">Agroindustria</SelectItem>
                    <SelectItem value="agronegocios">Agronegocios</SelectItem>
                </SelectContent>
            </Select>

            <Select v-model="selectedEstado">
                <SelectTrigger class="w-44 bg-gray-200 border-0" style="border-radius:12px">
                    <SelectValue placeholder="Todos"/>
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem value="ejecutada">Ejecutadas</SelectItem>
                    <SelectItem value="pendiente">Pendientes</SelectItem>
                </SelectContent>
            </Select>

        </div>
    </div>

    <!-- ✅ RESUMEN (LO QUE PEDISTE) -->
    <div class="mb-6 px-2">

        <p class="text-sm text-gray-700">
            Total de actividades: <strong>{{ total }}</strong>
        </p>

        <p class="text-sm text-green-600">
            Actividades ejecutadas: <strong>{{ ejecutadas }}</strong>
        </p>

        <p class="text-sm text-blue-600">
            Actividades pendientes: <strong>{{ pendientes }}</strong>
        </p>

    </div>

    <!-- ✅ LISTA -->
    <div class="flex flex-col gap-4">

        <div
            v-for="a in actividadesFiltradas"
            :key="a.id"
            class="bg-gray-200 p-5 rounded-xl"
        >

            <!-- Carrera -->
            <div class="mb-3">
                <span class="px-3 py-1 text-xs font-semibold text-white"
                    style="background-color:#536493; border-radius:8px">
                    {{ formatCarrera(a.carrera) }}
                </span>
            </div>

            <!-- Nombre -->
            <h3 class="text-lg font-bold text-gray-800 mb-2">
                {{ a.nombre }}
            </h3>

            <!-- Descripción -->
            <p class="text-sm text-gray-700 mb-3">
                {{ a.descripcion }}
            </p>

            <!-- Docente -->
            <p class="text-sm text-gray-600 mb-2">
                <strong>Docente:</strong> {{ a.docente }}
            </p>

            <!-- Estado -->
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="font-medium">Estado:</span>

                <span class="px-2 py-1 text-white text-xs"
                    :style="{
                        backgroundColor: estadoColor(a.estado),
                        borderRadius: '6px'
                    }">
                    {{ a.estado }}
                </span>
            </div>

        </div>

    </div>

</div>

</AppSidebarLayout>
</template>