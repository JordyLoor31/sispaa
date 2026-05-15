<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import type { ApexOptions } from 'apexcharts'
import ApexChart from 'vue3-apexcharts'
import {
    FlaskConical,
    Users,
    MapPin,
    BookOpen,
    CalendarDays,
    Microscope,
} from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Laboratorio',
        href: '/laboratorio',
    },
]

/*
|--------------------------------------------------------------------------
| KPI CARDS
|--------------------------------------------------------------------------
*/

const kpis = [
    {
        title: 'Prácticas realizadas',
        value: '148',
        icon: FlaskConical,
        color: '#536493',
        bg: '#53649315',
    },
    {
        title: 'Estudiantes participantes',
        value: '1,284',
        icon: Users,
        color: '#88C273',
        bg: '#88C27315',
    },
    {
        title: 'Laboratorios activos',
        value: '12',
        icon: Microscope,
        color: '#D4BDAC',
        bg: '#D4BDAC25',
    },
    {
        title: 'Carreras involucradas',
        value: '6',
        icon: BookOpen,
        color: '#7C5CFC',
        bg: '#7C5CFC15',
    },
]

/*
|--------------------------------------------------------------------------
| GRÁFICO 1
|--------------------------------------------------------------------------
*/

const practicasSeries = [
    {
        name: 'Prácticas',
        data: [24, 18, 30, 14, 28, 34],
    },
]

const practicasChartOptions: ApexOptions = {
    chart: {
        toolbar: {
            show: false,
        },
        background: 'transparent',
    },
    colors: ['#536493'],
    stroke: {
        curve: 'smooth',
        width: 4,
    },
    grid: {
        borderColor: '#d9d9d9',
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
    dataLabels: {
        enabled: false,
    },
}

/*
|--------------------------------------------------------------------------
| GRÁFICO 2
|--------------------------------------------------------------------------
*/

const carreraSeries = [44, 28, 18, 10]

const carreraChartOptions: ApexOptions = {
    labels: [
        'Agroindustrial',
        'Agronegocios',
        'Agropecuaria',
        'Ambiental',
    ],
    colors: [
        '#536493',
        '#88C273',
        '#D4BDAC',
        '#7C5CFC',
    ],
    legend: {
        position: 'bottom',
    },
    chart: {
        background: 'transparent',
    },
}

/*
|--------------------------------------------------------------------------
| TABLA
|--------------------------------------------------------------------------
*/

const practicas = [
    {
        materia: 'Química Orgánica',
        carrera: 'Agroindustrial',
        laboratorio: 'Lab A',
        estudiantes: 32,
        fecha: '10/07/2026',
        estado: 'Completada',
        badge:
            'bg-green-100 text-green-700',
    },
    {
        materia: 'Microbiología',
        carrera: 'Agropecuaria',
        laboratorio: 'Lab B',
        estudiantes: 24,
        fecha: '12/07/2026',
        estado: 'En proceso',
        badge:
            'bg-yellow-100 text-yellow-700',
    },
    {
        materia: 'Biotecnología',
        carrera: 'Agronegocios',
        laboratorio: 'Lab C',
        estudiantes: 28,
        fecha: '15/07/2026',
        estado: 'Planificada',
        badge:
            'bg-blue-100 text-blue-700',
    },
    {
        materia: 'Análisis de Suelos',
        carrera: 'Ambiental',
        laboratorio: 'Lab D',
        estudiantes: 18,
        fecha: '18/07/2026',
        estado: 'Completada',
        badge:
            'bg-green-100 text-green-700',
    },
]

/*
|--------------------------------------------------------------------------
| UBICACIONES
|--------------------------------------------------------------------------
*/

const ubicaciones = [
    {
        nombre: 'Laboratorio Químico',
        usos: 38,
    },
    {
        nombre: 'Laboratorio Biológico',
        usos: 26,
    },
    {
        nombre: 'Laboratorio de Suelos',
        usos: 18,
    },
    {
        nombre: 'Laboratorio Industrial',
        usos: 14,
    },
]
</script>

<template>
    <Head title="Laboratorio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="min-h-screen p-4 sm:p-6"
            :style="{
                backgroundColor: 'var(--sispaa-background)',
                color: 'var(--sispaa-text)',
            }"
        >
            <div class="mx-auto flex max-w-7xl flex-col gap-6">

                <!-- HEADER -->
                <section
                    class="rounded-3xl p-6 shadow-sm"
                    :style="{
                        backgroundColor: 'var(--sispaa-surface)',
                    }"
                >
                    <div
                        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                    >
                        <div>
                            <p
                                class="text-sm font-semibold uppercase tracking-[0.25em]"
                                :style="{
                                    color: '#536493',
                                }"
                            >
                                Laboratorio
                            </p>

                            <h1
                                class="mt-2 text-3xl font-bold"
                            >
                                Panel General de Prácticas
                            </h1>

                            <p
                                class="mt-2 max-w-2xl text-sm"
                                :style="{ color: '#666' }"
                            >
                                Dashboard visual con indicadores,
                                prácticas registradas, carreras,
                                estudiantes y laboratorios utilizados.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl px-5 py-4"
                            :style="{
                                backgroundColor: '#53649315',
                            }"
                        >
                            <div
                                class="flex items-center gap-3"
                            >
                                <CalendarDays
                                    class="h-10 w-10 text-[#536493]"
                                />

                                <div>
                                    <p
                                        class="text-sm"
                                        :style="{ color: '#666' }"
                                    >
                                        Período activo
                                    </p>

                                    <p
                                        class="text-lg font-semibold"
                                    >
                                        2026 - A
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- KPIS -->
                <section
                    class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <article
                        v-for="item in kpis"
                        :key="item.title"
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div
                            class="flex items-center justify-between"
                        >
                            <div>
                                <p
                                    class="text-sm"
                                    :style="{ color: '#666' }"
                                >
                                    {{ item.title }}
                                </p>

                                <h2
                                    class="mt-2 text-3xl font-bold"
                                >
                                    {{ item.value }}
                                </h2>
                            </div>

                            <div
                                class="rounded-2xl p-4"
                                :style="{
                                    backgroundColor: item.bg,
                                }"
                            >
                                <component
                                    :is="item.icon"
                                    class="h-7 w-7"
                                    :style="{
                                        color: item.color,
                                    }"
                                />
                            </div>
                        </div>
                    </article>
                </section>

                <!-- GRÁFICOS -->
                <section
                    class="grid gap-6 xl:grid-cols-3"
                >
                    <!-- LINE CHART -->
                    <article
                        class="rounded-3xl border p-5 xl:col-span-2"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div class="mb-4">
                            <h2
                                class="text-xl font-semibold"
                            >
                                Prácticas por mes
                            </h2>

                            <p
                                class="text-sm"
                                :style="{ color: '#666' }"
                            >
                                Evolución de prácticas realizadas
                            </p>
                        </div>

                        <ApexChart
                            type="line"
                            height="340"
                            :options="practicasChartOptions"
                            :series="practicasSeries"
                        />
                    </article>

                    <!-- DONUT -->
                    <article
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div class="mb-4">
                            <h2
                                class="text-xl font-semibold"
                            >
                                Distribución por carrera
                            </h2>

                            <p
                                class="text-sm"
                                :style="{ color: '#666' }"
                            >
                                Participación en prácticas
                            </p>
                        </div>

                        <ApexChart
                            type="donut"
                            height="340"
                            :options="carreraChartOptions"
                            :series="carreraSeries"
                        />
                    </article>
                </section>

                <!-- TABLA + UBICACIONES -->
                <section
                    class="grid gap-6 xl:grid-cols-3"
                >
                    <!-- TABLA -->
                    <article
                        class="rounded-3xl border p-5 xl:col-span-2"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div
                            class="mb-4 flex items-center justify-between"
                        >
                            <div>
                                <h2
                                    class="text-xl font-semibold"
                                >
                                    Últimas prácticas
                                </h2>

                                <p
                                    class="text-sm"
                                    :style="{ color: '#666' }"
                                >
                                    Registro reciente
                                </p>
                            </div>
                        </div>

                        <div
                            class="overflow-hidden rounded-2xl border"
                            :style="{
                                borderColor: '#d9d9d9',
                            }"
                        >
                            <table
                                class="min-w-full text-left text-sm"
                            >
                                <thead
                                    :style="{
                                        backgroundColor:
                                            '#efefef',
                                    }"
                                >
                                    <tr>
                                        <th
                                            class="px-4 py-3"
                                        >
                                            Materia
                                        </th>

                                        <th
                                            class="px-4 py-3"
                                        >
                                            Carrera
                                        </th>

                                        <th
                                            class="px-4 py-3"
                                        >
                                            Laboratorio
                                        </th>

                                        <th
                                            class="px-4 py-3"
                                        >
                                            Estudiantes
                                        </th>

                                        <th
                                            class="px-4 py-3"
                                        >
                                            Estado
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr
                                        v-for="item in practicas"
                                        :key="item.materia"
                                        class="border-t"
                                        :style="{
                                            borderColor:
                                                '#e5e5e5',
                                        }"
                                    >
                                        <td
                                            class="px-4 py-4 font-medium"
                                        >
                                            {{ item.materia }}
                                        </td>

                                        <td
                                            class="px-4 py-4"
                                        >
                                            {{ item.carrera }}
                                        </td>

                                        <td
                                            class="px-4 py-4"
                                        >
                                            {{ item.laboratorio }}
                                        </td>

                                        <td
                                            class="px-4 py-4"
                                        >
                                            {{ item.estudiantes }}
                                        </td>

                                        <td
                                            class="px-4 py-4"
                                        >
                                            <span
                                                class="rounded-full px-3 py-1 text-xs font-semibold"
                                                :class="item.badge"
                                            >
                                                {{ item.estado }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </article>

                    <!-- UBICACIONES -->
                    <article
                        class="rounded-3xl border p-5"
                        :style="{
                            backgroundColor:
                                'var(--sispaa-surface)',
                            borderColor: '#d9d9d9',
                        }"
                    >
                        <div class="mb-5">
                            <h2
                                class="text-xl font-semibold"
                            >
                                Uso de laboratorios
                            </h2>

                            <p
                                class="text-sm"
                                :style="{ color: '#666' }"
                            >
                                Espacios más utilizados
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div
                                v-for="lab in ubicaciones"
                                :key="lab.nombre"
                                class="rounded-2xl border p-4"
                                :style="{
                                    borderColor: '#e5e5e5',
                                }"
                            >
                                <div
                                    class="flex items-center justify-between"
                                >
                                    <div
                                        class="flex items-center gap-3"
                                    >
                                        <div
                                            class="rounded-xl bg-[#53649315] p-3"
                                        >
                                            <MapPin
                                                class="h-5 w-5 text-[#536493]"
                                            />
                                        </div>

                                        <div>
                                            <p
                                                class="font-medium"
                                            >
                                                {{ lab.nombre }}
                                            </p>

                                            <p
                                                class="text-xs"
                                                :style="{ color: '#666' }"
                                            >
                                                {{ lab.usos }}
                                                prácticas
                                            </p>
                                        </div>
                                    </div>

                                    <div
                                        class="text-lg font-bold text-[#536493]"
                                    >
                                        {{ lab.usos }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>