<template>
  <Head title="Estudiantes" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen px-4 py-6 sm:px-6 lg:px-8" :style="{ backgroundColor: 'var(--sispaa-background)', color: 'var(--sispaa-text)' }">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-6">
      <header class="rounded-3xl p-6 shadow-sm" :style="{ backgroundColor: 'var(--sispaa-surface)' }">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.24em]" :style="{ color: 'var(--sispaa-primary)' }">Estudiantes</p>
            <h1 class="mt-2 text-3xl font-semibold sm:text-4xl" :style="{ color: 'var(--sispaa-text)' }">Panel de estudiantes</h1>
            <p class="mt-2 max-w-2xl text-sm" :style="{ color: '#555' }">
              Resumen visual para matrícula, faltas y distribución académica.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
            <div class="rounded-2xl border px-4 py-3" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-background)' }">
              <p :style="{ color: '#666' }">Total</p>
              <p class="mt-1 text-2xl font-semibold" :style="{ color: 'var(--sispaa-text)' }">1,248</p>
            </div>
            <div class="rounded-2xl border px-4 py-3" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-background)' }">
              <p :style="{ color: '#666' }">Activos</p>
              <p class="mt-1 text-2xl font-semibold" :style="{ color: '#88C273' }">1,102</p>
            </div>
            <div class="rounded-2xl border px-4 py-3" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-background)' }">
              <p :style="{ color: '#666' }">Faltas</p>
              <p class="mt-1 text-2xl font-semibold" :style="{ color: '#D4BDAC' }">86</p>
            </div>
            <div class="rounded-2xl border px-4 py-3" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-background)' }">
              <p :style="{ color: '#666' }">Justificadas</p>
              <p class="mt-1 text-2xl font-semibold" :style="{ color: '#536493' }">24</p>
            </div>
          </div>
        </div>
      </header>

      <section class="grid gap-6 xl:grid-cols-3">
        <article class="rounded-3xl border p-5 xl:col-span-1" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-surface)' }">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Estado de matrícula</h2>
              <p class="text-sm" :style="{ color: '#666' }">Distribución general de estudiantes</p>
            </div>
          </div>
          <ApexChart
            type="donut"
            height="320"
            :options="statusChartOptions"
            :series="statusSeries"
          />
        </article>

        <article class="rounded-3xl border p-5 xl:col-span-2" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-surface)' }">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Matrícula por carrera</h2>
              <p class="text-sm" :style="{ color: '#666' }">Comparación entre carreras activas</p>
            </div>
          </div>
          <ApexChart
            type="bar"
            height="320"
            :options="careerChartOptions"
            :series="careerSeries"
          />
        </article>
      </section>

      <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-3xl border p-5 lg:col-span-2" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-surface)' }">
          <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Últimos registros</h2>
          <p class="mt-1 text-sm" :style="{ color: '#666' }">Ejemplo de tabla para alimentar el index</p>

          <div class="mt-4 overflow-hidden rounded-2xl border" :style="{ borderColor: '#c8c8c8' }">
            <table class="min-w-full text-left text-sm">
              <thead :style="{ backgroundColor: '#ededed', color: '#555' }">
                <tr>
                  <th class="px-4 py-3 font-medium">Estudiante</th>
                  <th class="px-4 py-3 font-medium">Carrera</th>
                  <th class="px-4 py-3 font-medium">Estado</th>
                  <th class="px-4 py-3 font-medium">Última actividad</th>
                </tr>
              </thead>
              <tbody :style="{ backgroundColor: 'var(--sispaa-background)', color: 'var(--sispaa-text)' }">
                <tr v-for="student in recentStudents" :key="student.name">
                  <td class="px-4 py-3 font-medium">{{ student.name }}</td>
                  <td class="px-4 py-3">{{ student.career }}</td>
                  <td class="px-4 py-3">
                    <span
                      class="rounded-full px-3 py-1 text-xs font-medium"
                      :class="student.badgeClass"
                    >
                      {{ student.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3" :style="{ color: '#666' }">{{ student.lastActivity }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </article>

       <!--<article class="rounded-3xl border p-5" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-surface)' }">
          <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Idea de uso</h2>
          <p class="mt-2 text-sm leading-6" :style="{ color: '#555' }">
            Esta vista sirve como panel principal de estudiantes. Desde el controlador puedes pasar
            conteos reales y colecciones agrupadas por carrera, estado o período, y aquí solo pintas
            los datos con ApexCharts.
          </p>
          <div class="mt-5 rounded-2xl border p-4 text-sm" :style="{ borderColor: '#53649355', backgroundColor: '#53649315', color: '#536493' }">
            Recomendación: usa este paquete para dashboards y deja Chart.js solo si vas a hacer
            gráficas muy simples.
          </div>
        </article>--> 
      </section>
    </div>
  </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import type { ApexOptions } from 'apexcharts';
import ApexChart from 'vue3-apexcharts';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Estudiantes',
    href: '/estudiantes',
  },{
    title: 'Panel de estudiantes',
    href: '/estudiantes/panel de estudiantes',
  }
];

const statusSeries = [1102, 86, 60];

const statusChartOptions: ApexOptions = {
  chart: {
    background: 'transparent',
    foreColor: '#353535',
  },
  labels: ['Activos', 'Con faltas', 'Retirados'],
  colors: ['#88C273', '#D4BDAC', '#536493'],
  legend: {
    position: 'bottom',
    labels: {
      colors: '#555',
    },
  },
  dataLabels: {
    enabled: true,
  },
  stroke: {
    colors: ['#ffffff'],
  },
  plotOptions: {
    pie: {
      donut: {
        size: '68%',
      },
    },
  },
};

const careerSeries = [
  {
    name: 'Estudiantes',
    data: [420, 310, 260],
  },
];

const careerChartOptions: ApexOptions = {
  chart: {
    background: 'transparent',
    foreColor: '#353535',
    toolbar: {
      show: false,
    },
  },
  colors: ['#536493'],
  plotOptions: {
    bar: {
      borderRadius: 10,
      columnWidth: '48%',
    },
  },
  grid: {
    borderColor: '#cfcfcf',
  },
  xaxis: {
    categories: ['Agroindustrial', 'Agronegocios', 'Agropecuaria'],
    labels: {
      style: {
        colors: '#666',
      },
    },
  },
  yaxis: {
    labels: {
      style: {
        colors: '#666',
      },
    },
  },
  dataLabels: {
    enabled: false,
  },
  tooltip: {
    theme: 'dark',
  },
};

const recentStudents = [
  {
    name: 'Andrea Paredes',
    career: 'Agroindustrial',
    status: 'Activo',
    lastActivity: 'Matrícula 2026-A',
    badgeClass: 'bg-[#88C273]/20 text-[#3b6b2b]',
  },
  {
    name: 'Carlos Mena',
    career: 'Agronegocios',
    status: 'Con faltas',
    lastActivity: '3 faltas este mes',
    badgeClass: 'bg-[#D4BDAC]/30 text-[#7c5c47]',
  },
  {
    name: 'María López',
    career: 'Agropecuaria',
    status: 'Justificada',
    lastActivity: 'Solicitud aprobada',
    badgeClass: 'bg-[#536493]/20 text-[#2e3f69]',
  },
];
</script>

<style scoped>
</style>
