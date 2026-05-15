<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApexChart from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';

const kpis = [
    {
        title: 'Prácticas totales',
        value: '128',
        color: '#536493',
    },
    {
        title: 'Docentes',
        value: '24',
        color: '#88C273',
    },
    {
        title: 'Laboratorios',
        value: '12',
        color: '#D4BDAC',
    },
    {
        title: 'Estudiantes',
        value: '1,240',
        color: '#7C93C3',
    },
];

const series = [
    {
        name: 'Prácticas',
        data: [12, 18, 22, 30, 25, 21],
    },
];

const options: ApexOptions = {
    chart: {
        toolbar: {
            show: false,
        },
    },

    colors: ['#536493'],

    stroke: {
        curve: 'smooth',
        width: 4,
    },

    xaxis: {
        categories: [
            'Ene',
            'Feb',
            'Mar',
            'Abr',
            'May',
            'Jun',
        ],
    },

    grid: {
        borderColor: '#e5e7eb',
    },
};

const practicas = [
    {
        tema: 'Análisis microbiológico',
        carrera: 'Agroindustrial',
        docente: 'Juan Pérez',
        estudiantes: 32,
        laboratorio: 'Lab Química',
        estado: 'Completada',
        badge: 'bg-green-100 text-green-700',
    },

    {
        tema: 'Microscopía vegetal',
        carrera: 'Agropecuaria',
        docente: 'Ana López',
        estudiantes: 26,
        laboratorio: 'Lab Biología',
        estado: 'En proceso',
        badge: 'bg-yellow-100 text-yellow-700',
    },

    {
        tema: 'Redes básicas',
        carrera: 'Sistemas',
        docente: 'Carlos Mena',
        estudiantes: 40,
        laboratorio: 'Lab Computación',
        estado: 'Completada',
        badge: 'bg-blue-100 text-blue-700',
    },

    {
        tema: 'Análisis de suelos',
        carrera: 'Agronegocios',
        docente: 'María Torres',
        estudiantes: 28,
        laboratorio: 'Lab Ambiental',
        estado: 'Pendiente',
        badge: 'bg-red-100 text-red-700',
    },
];
</script>

<template>
    <Head title="Prácticas" />

    <AppLayout>

        <div class="min-h-screen p-6 space-y-6">

            <div>
                <h1 class="text-3xl font-bold">
                    Gestión de prácticas
                </h1>

                <p class="text-gray-500 mt-2">
                    Panel visual de prácticas de laboratorio
                </p>
            </div>

            <section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

                <div
                    v-for="item in kpis"
                    :key="item.title"
                    class="rounded-3xl border p-6 bg-white shadow-sm"
                >
                    <p class="text-gray-500 text-sm">
                        {{ item.title }}
                    </p>

                    <h2
                        class="text-4xl font-bold mt-2"
                        :style="{ color: item.color }"
                    >
                        {{ item.value }}
                    </h2>
                </div>

            </section>

            <section class="grid xl:grid-cols-3 gap-6">

                <div class="xl:col-span-2 rounded-3xl border bg-white p-6">

                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold">
                                Tendencia de prácticas
                            </h2>

                            <p class="text-sm text-gray-500">
                                Comparación mensual
                            </p>
                        </div>
                    </div>

                    <ApexChart
                        type="line"
                        height="320"
                        :options="options"
                        :series="series"
                    />

                </div>

                <div class="rounded-3xl border bg-white p-6">

                    <h2 class="text-xl font-semibold mb-6">
                        Estado general
                    </h2>

                    <div class="space-y-5">

                        <div>
                            <div class="flex justify-between mb-2">
                                <span>Completadas</span>
                                <span>78%</span>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full w-[78%]" />
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-2">
                                <span>En proceso</span>
                                <span>14%</span>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-yellow-500 h-3 rounded-full w-[14%]" />
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between mb-2">
                                <span>Pendientes</span>
                                <span>8%</span>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-red-500 h-3 rounded-full w-[8%]" />
                            </div>
                        </div>

                    </div>

                </div>

            </section>

            <section class="rounded-3xl border bg-white overflow-hidden">

                <div class="p-6 border-b">

                    <h2 class="text-xl font-semibold">
                        Últimas prácticas registradas
                    </h2>

                </div>

                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead class="bg-gray-100 text-gray-600">

                            <tr>
                                <th class="p-4">Tema</th>
                                <th>Carrera</th>
                                <th>Docente</th>
                                <th>Estudiantes</th>
                                <th>Laboratorio</th>
                                <th>Estado</th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="item in practicas"
                                :key="item.tema"
                                class="border-t hover:bg-gray-50"
                            >
                                <td class="p-4 font-medium">
                                    {{ item.tema }}
                                </td>

                                <td>
                                    {{ item.carrera }}
                                </td>

                                <td>
                                    {{ item.docente }}
                                </td>

                                <td>
                                    {{ item.estudiantes }}
                                </td>

                                <td>
                                    {{ item.laboratorio }}
                                </td>

                                <td>
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold"
                                        :class="item.badge"
                                    >
                                        {{ item.estado }}
                                    </span>
                                </td>
                            </tr>

                        </tbody>

                    </table>

                </div>

            </section>

        </div>

    </AppLayout>
</template>