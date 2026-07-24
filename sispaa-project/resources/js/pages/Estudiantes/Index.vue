<template>
  <Head title="Estudiantes" />

  <AppSidebarLayout :breadcrumbs="breadcrumbs">
    <!-- Mismo contenedor (p-6, ancho completo hasta el sidebar) que el resto
         de vistas del módulo (Matriculados, Faltas, Justificaciones): antes
         esta vista usaba mx-auto max-w-7xl, lo que dejaba un espacio extra
         entre el sidebar y el contenido que no coincidía con las demás. -->
    <div class="flex h-full flex-1 flex-col gap-6 p-6" :style="{ backgroundColor: 'var(--sispaa-background)', color: 'var(--sispaa-text)' }">
      <header class="rounded-3xl p-6 shadow-sm" :style="{ backgroundColor: 'var(--sispaa-surface)' }">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
          <div>
            <p class="text-sm font-medium uppercase tracking-[0.24em]" :style="{ color: 'var(--sispaa-primary)' }">Estudiantes</p>
           <!-- <h1 class="mt-2 text-3xl font-semibold sm:text-4xl" :style="{ color: 'var(--sispaa-text)' }"></h1>-->
            <p class="mt-2 max-w-2xl text-sm" :style="{ color: '#555' }">
              Resumen visual para matrícula, faltas y distribución académica.
            </p>
          </div>

          <div class="grid grid-cols-2 gap-3 text-sm">
            <div class="rounded-2xl border px-4 py-3" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-background)' }">
              <p :style="{ color: '#666' }">Total</p>
              <p class="mt-1 text-2xl font-semibold" :style="{ color: 'var(--sispaa-text)' }">{{ stats.total.toLocaleString('es-EC') }}</p>
            </div>
            <div class="rounded-2xl border px-4 py-3" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-background)' }">
              <p :style="{ color: '#666' }">Activos</p>
              <p class="mt-1 text-2xl font-semibold" :style="{ color: '#88C273' }">{{ stats.activos.toLocaleString('es-EC') }}</p>
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
            v-if="stats.total > 0"
            type="donut"
            height="320"
            :options="statusChartOptions"
            :series="statusSeries"
          />
          <p v-else class="py-16 text-center text-sm" :style="{ color: '#888' }">Aún no hay estudiantes registrados.</p>
        </article>

        <article class="rounded-3xl border p-5 xl:col-span-2" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-surface)' }">
          <div class="mb-4 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Matrícula por carrera</h2>
              <p class="text-sm" :style="{ color: '#666' }">Comparación entre carreras activas</p>
            </div>
          </div>
          <ApexChart
            v-if="porCarrera.length > 0"
            type="bar"
            height="320"
            :options="careerChartOptions"
            :series="careerSeries"
          />
          <p v-else class="py-16 text-center text-sm" :style="{ color: '#888' }">Aún no hay estudiantes matriculados por carrera.</p>
        </article>
      </section>

      <section class="grid gap-6 lg:grid-cols-3">
        <article class="rounded-3xl border p-5 lg:col-span-2" :style="{ borderColor: '#c8c8c8', backgroundColor: 'var(--sispaa-surface)' }">
          <h2 class="text-lg font-semibold" :style="{ color: 'var(--sispaa-text)' }">Últimos registros</h2>
          <p class="mt-1 text-sm" :style="{ color: '#666' }">Los 5 estudiantes registrados más recientemente</p>

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
                <tr v-for="student in ultimosRegistros" :key="student.name">
                  <td class="px-4 py-3 font-medium">{{ student.name }}</td>
                  <td class="px-4 py-3">{{ student.career }}</td>
                  <td class="px-4 py-3">
                    <span
                      class="rounded-full px-3 py-1 text-xs font-medium"
                      :class="badgeClass(student.status)"
                    >
                      {{ student.status }}
                    </span>
                  </td>
                  <td class="px-4 py-3" :style="{ color: '#666' }">{{ student.lastActivity }}</td>
                </tr>
                <tr v-if="ultimosRegistros.length === 0">
                  <td colspan="4" class="px-4 py-6 text-center" :style="{ color: '#888' }">Aún no hay estudiantes registrados.</td>
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
  </AppSidebarLayout>
</template>

<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ApexOptions } from 'apexcharts';
import ApexChart from 'vue3-apexcharts';

interface Stats {
    total: number;
    activos: number;
}

interface EstadoMatricula {
    activos: number;
    retirados: number;
}

interface CarreraCount {
    carrera: string;
    total: number;
}

interface RegistroReciente {
    name: string;
    career: string;
    status: string;
    lastActivity: string;
}

const props = defineProps<{
    stats: Stats;
    estadoMatricula: EstadoMatricula;
    porCarrera: CarreraCount[];
    ultimosRegistros: RegistroReciente[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const statusSeries = computed(() => [
    props.estadoMatricula.activos,
    props.estadoMatricula.retirados,
]);

const statusChartOptions: ApexOptions = {
  chart: {
    background: 'transparent',
    foreColor: '#353535',
  },
  labels: ['Activos', 'Retirados'],
  colors: ['#88C273', '#536493'],
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

const careerSeries = computed(() => [
  {
    name: 'Estudiantes',
    data: props.porCarrera.map((c) => c.total),
  },
]);

const careerChartOptions = computed<ApexOptions>(() => ({
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
    categories: props.porCarrera.map((c) => c.carrera),
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
}));

const badgeClass = (status: string) => {
    const map: Record<string, string> = {
        Activo: 'bg-[#88C273]/20 text-[#3b6b2b]',
        Retirado: 'bg-[#e0e0e0] text-[#555]',
        Egresado: 'bg-[#536493]/20 text-[#2e3f69]',
    };
    return map[status] ?? 'bg-[#e0e0e0] text-[#555]';
};
</script>

<style scoped>
</style>
