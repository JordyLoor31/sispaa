<template>
<AppSidebarLayout :breadcrumbs="breadcrumbs">

<div class="p-6 bg-gray-100 min-h-screen">

    <!-- ✅ ENCABEZADO -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Líderes de vinculación
            </h1>
            <p class="text-sm text-gray-600">
                Asignación de docentes líderes por carrera y período
            </p>
        </div>

<!--
        <button class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg">
            Asignar docente
        </button>
        -->
    </div>

    <!-- ✅ MÉTRICAS -->
    <div class="grid grid-cols-4 gap-4 mb-6">

        <div class="card">
            <p class="label">Total asignados</p>
            <h2 class="value">{{ lideres.length }}</h2>
        </div>

        <div class="card">
            <p class="label">Alto</p>
            <h2 class="value text-green-600">{{ contar('alto') }}</h2>
        </div>

        <div class="card">
            <p class="label">Parcial</p>
            <h2 class="value text-yellow-600">{{ contar('parcial') }}</h2>
        </div>

        <div class="card">
            <p class="label">Bajo</p>
            <h2 class="value text-red-600">{{ contar('bajo') }}</h2>
        </div>

    </div>

    <!-- ✅ FILTROS -->
    <div class="flex gap-4 mb-4">

        <select v-model="filtroCarrera" class="input">
            <option value="">Todas las carreras</option>
            <option value="Agropecuaria">Agropecuaria</option>
            <option value="Agroindustria">Agroindustria</option>
            <option value="Agronegocios">Agronegocios</option>
        </select>

        <input v-model="busqueda" class="input flex-1" placeholder="Buscar docente..." />

    </div>

    <!-- ✅ TABLA -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">

        <table class="w-full text-sm">

            <thead class="bg-gray-50">
                <tr>
                    <th class="th">Docente</th>
                    <th class="th">Carrera</th>
                    <th class="th">Proyectos</th>
                    <th class="th">Cumplimiento</th>
                    <th class="th">Estado</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="l in lideresFiltrados" :key="l.id" class="hover:bg-gray-50">

                    <td class="td font-medium">{{ l.nombre }}</td>
                    <td class="td">{{ l.carrera }}</td>
                    <td class="td">{{ l.proyectos }}</td>

                    <!-- progreso -->
                    <td class="td">
                        <div class="w-24 bg-gray-200 rounded-full h-2">
                            <div 
                                class="h-2 rounded-full"
                                :style="{ width: l.cumplimiento + '%', backgroundColor: color(l.cumplimiento) }"
                            ></div>
                        </div>
                        <span class="text-xs">{{ l.cumplimiento }}%</span>
                    </td>

                    <!-- estado -->
                    <td class="td">
                        <span :class="badgeClass(l.cumplimiento)">
                            {{ label(l.cumplimiento) }}
                        </span>
                    </td>

                </tr>
            </tbody>

        </table>

    </div>

</div>

</AppSidebarLayout>
</template>

<script setup>
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue'
import { ref, computed } from 'vue'

// breadcrumbs
const breadcrumbs = [
    { title: 'Vinculación', href: '/vinculacion' },
    { title: 'Líderes', href: '/vinculacion/lideres' },
]

// datos simulados
const lideres = ref([
    { id:1, nombre:'Carlos Ramírez', carrera:'Agropecuaria', proyectos:4, cumplimiento:80 },
    { id:2, nombre:'Ana Torres', carrera:'Agronegocios', proyectos:2, cumplimiento:50 },
    { id:3, nombre:'María Díaz', carrera:'Agroindustria', proyectos:5, cumplimiento:90 },
    { id:4, nombre:'Roberto Díaz', carrera:'Agropecuaria', proyectos:1, cumplimiento:30 },
])

// filtros
const filtroCarrera = ref('')
const busqueda = ref('')

// filtrado
const lideresFiltrados = computed(() => {
    return lideres.value.filter(l =>
        (!filtroCarrera.value || l.carrera === filtroCarrera.value) &&
        (!busqueda.value || l.nombre.toLowerCase().includes(busqueda.value.toLowerCase()))
    )
})

// conteos
const contar = (tipo) => {
    return lideres.value.filter(l => {
        if(tipo==='alto') return l.cumplimiento >= 75
        if(tipo==='parcial') return l.cumplimiento >=45 && l.cumplimiento <75
        return l.cumplimiento <45
    }).length
}

// helpers UI
const color = (v) => v>=75 ? '#4CAF50' : v>=45 ? '#f59e0b' : '#ef4444'

const label = (v) => v>=75 ? 'Alto' : v>=45 ? 'Parcial' : 'Bajo'

const badgeClass = (v) =>
    `px-2 py-1 rounded text-xs text-white ${
        v>=75 ? 'bg-green-500' :
        v>=45 ? 'bg-yellow-500' :
        'bg-red-500'
    }`
</script>

<style scoped>
.card {
    background: white;
    padding: 15px;
    border-radius: 12px;
}

.label {
    font-size: 12px;
    color: gray;
}

.value {
    font-size: 24px;
    font-weight: bold;
}

.input {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.th {
    text-align: left;
    padding: 10px;
}

.td {
    padding: 10px;
}
</style>