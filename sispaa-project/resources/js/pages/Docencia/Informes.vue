<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Informes de Asignatura" />
        <div class="min-h-screen pl-3 pr-3 pt-2" :style="{ backgroundColor: 'var(--sispaa-background)' }">
            <!-- Header and Filters -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-2xl font-bold" :style="{ color: 'var(--sispaa-text)' }">Informes de Asignatura</h1>
                    <p class="mt-2 text-sm" :style="{ color: 'var(--sispaa-text)', opacity: 0.7 }">
                        Visualización y control de informes de asignatura por docentes
                    </p>
                </div>

                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium" :style="{ color: 'var(--sispaa-text)' }">Carrera:</span>
                        <Select v-model="selectedCarrera">
                            <SelectTrigger class="w-[180px] rounded-xl border-[var(--sispaa-text)]/20 bg-[var(--sispaa-surface)]">
                                <SelectValue placeholder="Todas las carreras" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="all">Todas las carreras</SelectItem>
                                <SelectItem v-for="carrera in carreras" :key="carrera.id" :value="carrera.id.toString()">
                                    {{ carrera.nombre }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium" :style="{ color: 'var(--sispaa-text)' }">Estado de informe:</span>
                        <Select v-model="selectedEstado">
                            <SelectTrigger class="w-[180px] rounded-xl border-[var(--sispaa-text)]/20 bg-[var(--sispaa-surface)]">
                                <SelectValue placeholder="Todos los estados" />
                            </SelectTrigger>
                            <SelectContent class="rounded-xl">
                                <SelectItem value="all">Todos los estados</SelectItem>
                                <SelectItem value="Cumplido">Cumplidos</SelectItem>
                                <SelectItem value="Pendiente">Pendientes</SelectItem>
                                <SelectItem value="Incumplido">Incumplidos</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </div>

            <!-- Content grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: List of reports -->
                <div class="lg:col-span-2 flex flex-col gap-4">
                    <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Listado de Informes</h2>
                    
                    <div
                        v-for="informe in filteredInformes"
                        :key="informe.id"
                        class="transform transition duration-300 hover:translate-x-2 hover:shadow-md rounded-xl bg-[var(--sispaa-surface)] p-5"
                    >
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="mb-2 flex flex-wrap gap-2">
                                    <span 
                                        class="inline-block px-3 py-1 text-xs font-semibold rounded-lg"
                                        :style="{ backgroundColor: 'var(--sispaa-primary)', color: '#FFFFFF' }"
                                    >
                                        {{ informe.carrera }}
                                    </span>
                                    <span 
                                        class="inline-block px-3 py-1 text-xs font-semibold rounded-lg"
                                        :style="getEstadoStyles(informe.estado)"
                                    >
                                        {{ informe.estado }}
                                    </span>
                                </div>
                                <h3 class="font-bold text-lg" :style="{ color: 'var(--sispaa-text)' }">
                                    {{ informe.materia }}
                                </h3>
                                <p class="text-sm mt-1" :style="{ color: 'var(--sispaa-text)', opacity: 0.8 }">
                                    <span class="font-medium">Docente:</span> {{ informe.docente }}
                                </p>
                            </div>
                            
                            <!-- Botón de Archivo -->
                            <div class="flex-shrink-0">
                                <a 
                                    v-if="informe.archivo_url" 
                                    :href="informe.archivo_url" 
                                    target="_blank"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-colors"
                                    :style="{ backgroundColor: 'var(--sispaa-secondary)', color: 'var(--sispaa-text)' }"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Ver Informe
                                </a>
                                <span 
                                    v-else
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium border border-[var(--sispaa-text)]/20"
                                    :style="{ color: 'var(--sispaa-text)', opacity: 0.5, backgroundColor: 'var(--sispaa-surface)' }"
                                >
                                    No adjuntado
                                </span>
                            </div>
                        </div>
                    </div>

                    <div v-if="filteredInformes.length === 0" class="flex flex-col items-center justify-center p-10 text-center rounded-xl bg-[var(--sispaa-surface)]/20">
                        <p class="text-lg font-medium" :style="{ color: 'var(--sispaa-text)', opacity: 0.6 }">No se encontraron informes para los filtros seleccionados</p>
                    </div>
                </div>

                <!-- Right: Chart -->
                <div class="lg:col-span-1 lg:pt-[44px]">
                    <div class="sticky top-6 rounded-xl border border-[var(--sispaa-text)]/10 bg-[var(--sispaa-surface)] p-5 shadow-sm">
                        <h2 class="text-md font-semibold mb-3 text-left" :style="{ color: 'var(--sispaa-text)' }">Resumen de Cumplimiento</h2>
                        <h3 class="text-sm font-normal mb-4 text-left" :style="{ color: 'var(--sispaa-text)' }">Porcentajes por estado de informe</h3>

                        <div v-if="chartSeries.reduce((a, b:any) => a + b, 0) > 0" class="flex justify-center">
                            <ApexChart
                                type="pie"
                                width="100%"
                                height="350"
                                :options="chartOptions"
                                :series="chartSeries"
                            />
                        </div>
                        <div v-else class="text-center py-10" :style="{ color: 'var(--sispaa-text)', opacity: 0.5 }">
                            No hay datos para graficar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>

<script setup lang="ts">
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import type { ApexOptions } from 'apexcharts';
import { computed, onMounted, ref } from 'vue';
import ApexChart from 'vue3-apexcharts';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    docentes: any[];
    materias: any[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Docencia', href: '/docencia' },
    { title: 'Informes de Asignatura', href: '/docencia/informes' },
];

const selectedCarrera = ref<string>('all');
const selectedEstado = ref<string>('all');
const informes = ref<any[]>([]);

onMounted(() => {
    // Generar datos simulados
    const estados = ['Cumplido', 'Pendiente', 'Incumplido'];
    const fakeInformes = [];
    
    // Generar 30 informes simulados para tener volumen de datos
    for (let i = 0; i < 30; i++) {
        const materiaIndex = Math.floor(Math.random() * props.materias.length);
        const docenteIndex = Math.floor(Math.random() * props.docentes.length);
        
        if(props.materias.length === 0 || props.docentes.length === 0) break;
        
        const materia = props.materias[materiaIndex];
        const docente = props.docentes[docenteIndex];
        
        // Distribución: ~60% Cumplido, 30% Pendiente, 10% Incumplido
        const rand = Math.random();
        let estado = 'Cumplido';
        if (rand > 0.6 && rand <= 0.9) estado = 'Pendiente';
        else if (rand > 0.9) estado = 'Incumplido';
        
        fakeInformes.push({
            id: i + 1,
            docente: docente.name,
            materia: materia.nombre,
            carrera: materia.carrera?.nombre || 'General',
            carrera_id: materia.carrera?.id || 1,
            estado: estado,
            archivo_url: estado === 'Cumplido' ? '#' : null,
        });
    }
    informes.value = fakeInformes;
});

const carreras = computed(() => {
    const unique = new Map();
    if(props.materias){
        props.materias.forEach(m => {
            if (m.carrera) unique.set(m.carrera.id, m.carrera.nombre);
        });
    }
    return Array.from(unique.entries()).map(([id, nombre]) => ({ id, nombre }));
});

const filteredInformes = computed(() => {
    return informes.value.filter(inf => {
        const matchCarrera = selectedCarrera.value === 'all' || inf.carrera_id.toString() === selectedCarrera.value;
        const matchEstado = selectedEstado.value === 'all' || inf.estado === selectedEstado.value;
        return matchCarrera && matchEstado;
    });
});

const chartSeries = computed(() => {
    const counts: Record<string, number> = { Cumplido: 0, Pendiente: 0, Incumplido: 0 };
    filteredInformes.value.forEach(inf => {
        if(counts[inf.estado] !== undefined) {
             counts[inf.estado]++;
        }
    });
    return [counts['Cumplido'], counts['Pendiente'], counts['Incumplido']];
});

const chartOptions = computed<ApexOptions>(() => ({
    chart: {
        type: 'pie',
        background: 'transparent',
        foreColor: '#353535',
    },
    labels: ['Cumplidos', 'Pendientes', 'Incumplidos'],
    colors: ['#72c184', '#E9C46A', '#e07153'], // Acorde a la paleta principal
    stroke: {
        colors: ['transparent'],
    },
    legend: {
        position: 'bottom',
        labels: {
            colors: 'var(--sispaa-text)',
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function (val: number) {
            return Math.round(val) + "%"
        },
        dropShadow: {
            enabled: true,
            top: 1,
            left: 1,
            blur: 1,
            color: '#000',
            opacity: 0.45
        }
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function(value: number) {
                return value + " docente(s)";
            }
        }
    }
}));

const getEstadoStyles = (estado: string) => {
    switch (estado) {
        case 'Cumplido':
            return { backgroundColor: '#72c184', color: '#1a4025' }; 
        case 'Pendiente':
            return { backgroundColor: '#E9C46A', color: '#5C4314' }; 
        case 'Incumplido':
            return { backgroundColor: '#E07A5F', color: '#FFFFFF' }; 
        default:
            return { backgroundColor: 'var(--sispaa-surface)', color: 'var(--sispaa-text)' };
    }
};
</script>
<style shadow>
</style>
