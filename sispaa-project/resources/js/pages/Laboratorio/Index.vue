<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FlaskConical, Microscope, Beaker, Users, MapPin } from 'lucide-vue-next';

interface UltimaPractica {
    id: number;
    tema: string;
    materia: string;
    carrera: string;
    docente: string;
    laboratorio: string | null;
    numero_estudiantes: number | null;
    fecha: string | null;
}
interface LaboratorioUso { id: number; nombre: string; usos: number }

defineProps<{
    stats: {
        total_practicas: number;
        laboratorios_activos: number;
        total_equipos: number;
        total_reactivos: number;
        estudiantes_atendidos: number;
    };
    ultimasPracticas: UltimaPractica[];
    laboratoriosMasUsados: LaboratorioUso[];
}>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Laboratorio</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Panel general de prácticas, laboratorios e inventario.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Prácticas</p>
                        <FlaskConical class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_practicas }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Laboratorios activos</p>
                        <MapPin class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.laboratorios_activos }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Equipos</p>
                        <Microscope class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_equipos }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Reactivos</p>
                        <Beaker class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total_reactivos }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold text-slate-500 uppercase">Estudiantes atendidos</p>
                        <Users class="h-5 w-5 text-indigo-500" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.estudiantes_atendidos }}</p>
                </div>
            </div>

            <div class="grid gap-6 xl:grid-cols-3">
                <div class="xl:col-span-2 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-slate-900 dark:text-white">Últimas prácticas</h2>
                        <Link :href="route('laboratorio.practicas')" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Ver todas</Link>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-slate-100 dark:border-slate-800">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-slate-900 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">Tema</th>
                                    <th class="px-4 py-2 text-left">Materia</th>
                                    <th class="px-4 py-2 text-left">Docente</th>
                                    <th class="px-4 py-2 text-left">Laboratorio</th>
                                    <th class="px-4 py-2 text-left">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="p in ultimasPracticas" :key="p.id" class="border-t border-slate-100 dark:border-slate-800">
                                    <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ p.tema }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ p.materia }} · {{ p.carrera }}</td>
                                    <td class="px-4 py-3 text-slate-500">{{ p.docente }}</td>
                                    <td class="px-4 py-3 text-slate-400 text-xs">{{ p.laboratorio ?? '—' }}</td>
                                    <td class="px-4 py-3 text-slate-400 text-xs">{{ p.fecha }}</td>
                                </tr>
                                <tr v-if="ultimasPracticas.length === 0">
                                    <td colspan="5" class="px-4 py-8 text-center text-sm text-slate-400">Aún no hay prácticas registradas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Laboratorios más usados</h2>
                    <div class="space-y-3">
                        <div v-for="l in laboratoriosMasUsados" :key="l.id" class="flex items-center justify-between rounded-xl border border-slate-100 dark:border-slate-800 p-3">
                            <div class="flex items-center gap-2">
                                <MapPin class="h-4 w-4 text-indigo-500" />
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ l.nombre }}</span>
                            </div>
                            <span class="text-sm font-bold text-indigo-600">{{ l.usos }}</span>
                        </div>
                        <div v-if="laboratoriosMasUsados.length === 0" class="text-center text-sm text-slate-400 py-6">
                            No hay laboratorios registrados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
