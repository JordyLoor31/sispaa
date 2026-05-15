<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import ApexChart from 'vue3-apexcharts';
import type { ApexOptions } from 'apexcharts';

const series = [
    {
        name: 'Prácticas',
        data: [55, 80, 40, 95],
    },
];

const options: ApexOptions = {
    chart: {
        toolbar: {
            show: false,
        },
    },

    colors: ['#88C273'],

    plotOptions: {
        bar: {
            borderRadius: 10,
            columnWidth: '45%',
        },
    },

    xaxis: {
        categories: [
            'Agroindustrial',
            'Agronegocios',
            'Agropecuaria',
            'Sistemas',
        ],
    },

    grid: {
        borderColor: '#e5e7eb',
    },
};

const carreras = [
    {
        nombre: 'Agroindustrial',
        practicas: 55,
        estudiantes: 320,
        laboratorios: 4,
        color: '#536493',
    },

    {
        nombre: 'Agronegocios',
        practicas: 80,
        estudiantes: 280,
        laboratorios: 3,
        color: '#88C273',
    },

    {
        nombre: 'Agropecuaria',
        practicas: 40,
        estudiantes: 210,
        laboratorios: 2,
        color: '#D4BDAC',
    },

    {
        nombre: 'Sistemas',
        practicas: 95,
        estudiantes: 430,
        laboratorios: 5,
        color: '#7C93C3',
    },
];
</script>

<template>
    <Head title="Por carrera" />

    <AppLayout>

        <div class="min-h-screen p-6 space-y-6">

            <div>
                <h1 class="text-3xl font-bold">
                    Indicadores por carrera
                </h1>

                <p class="text-gray-500 mt-2">
                    Estadísticas generales del uso de laboratorios
                </p>
            </div>

            <section class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">

                <div
                    v-for="item in carreras"
                    :key="item.nombre"
                    class="rounded-3xl border bg-white p-6 shadow-sm"
                >
                    <p class="text-sm text-gray-500">
                        {{ item.nombre }}
                    </p>

                    <h2
                        class="text-4xl font-bold mt-3"
                        :style="{ color: item.color }"
                    >
                        {{ item.practicas }}
                    </h2>

                    <p class="text-sm text-gray-500 mt-2">
                        prácticas registradas
                    </p>
                </div>

            </section>

            <section class="grid xl:grid-cols-3 gap-6">

                <div class="xl:col-span-2 rounded-3xl border bg-white p-6">

                    <div class="mb-6">

                        <h2 class="text-xl font-semibold">
                            Comparación de prácticas
                        </h2>

                        <p class="text-gray-500 text-sm mt-1">
                            Total de prácticas realizadas por carrera
                        </p>

                    </div>

                    <ApexChart
                        type="bar"
                        height="380"
                        :options="options"
                        :series="series"
                    />

                </div>

                <div class="rounded-3xl border bg-white p-6">

                    <h2 class="text-xl font-semibold mb-6">
                        Resumen académico
                    </h2>

                    <div
                        v-for="item in carreras"
                        :key="item.nombre"
                        class="mb-5 border-b pb-5"
                    >
                        <div class="flex items-center justify-between">

                            <div>
                                <h3 class="font-semibold">
                                    {{ item.nombre }}
                                </h3>

                                <p class="text-sm text-gray-500 mt-1">
                                    {{ item.estudiantes }} estudiantes
                                </p>
                            </div>

                            <div
                                class="w-4 h-4 rounded-full"
                                :style="{ backgroundColor: item.color }"
                            />

                        </div>

                        <div class="mt-3 text-sm text-gray-600">
                            Laboratorios utilizados:
                            <span class="font-semibold">
                                {{ item.laboratorios }}
                            </span>
                        </div>

                    </div>

                </div>

            </section>

            <section class="rounded-3xl border bg-white p-6">

                <div class="mb-6">
                    <h2 class="text-xl font-semibold">
                        Rendimiento por carrera
                    </h2>
                </div>

                <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">

                    <div
                        v-for="item in carreras"
                        :key="item.nombre"
                        class="border rounded-2xl p-5"
                    >
                        <div class="flex items-center justify-between">

                            <h3 class="font-semibold">
                                {{ item.nombre }}
                            </h3>

                            <div
                                class="w-3 h-3 rounded-full"
                                :style="{ backgroundColor: item.color }"
                            />

                        </div>

                        <div class="mt-5">

                            <p class="text-sm text-gray-500">
                                Estudiantes
                            </p>

                            <h2 class="text-2xl font-bold mt-1">
                                {{ item.estudiantes }}
                            </h2>

                        </div>

                        <div class="mt-4">

                            <p class="text-sm text-gray-500">
                                Laboratorios
                            </p>

                            <h2 class="text-2xl font-bold mt-1">
                                {{ item.laboratorios }}
                            </h2>

                        </div>

                    </div>

                </div>

            </section>

        </div>

    </AppLayout>
</template>