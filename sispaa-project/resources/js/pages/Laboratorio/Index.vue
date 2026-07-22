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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Laboratorio</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Panel general de prácticas, laboratorios e inventario.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Prácticas</p>
                        <FlaskConical class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total_practicas }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Laboratorios activos</p>
                        <MapPin class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.laboratorios_activos }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Equipos</p>
                        <Microscope class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total_equipos }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Reactivos</p>
                        <Beaker class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total_reactivos }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Estudiantes atendidos</p>
                        <Users class="h-5 w-5 text-[var(--sispaa-primary)]" />
                    </div>
                    <p class="mt-2 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.estudiantes_atendidos }}</p>
                </div>
            </div>

            <div class="grid gap-4 sm:gap-6 xl:grid-cols-3">
                <div class="xl:col-span-2 rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-sm font-bold text-[var(--sispaa-text)]">Últimas prácticas</h2>
                        <Link :href="route('laboratorio.practicas')" class="text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80">Ver todas</Link>
                    </div>
                    <div class="overflow-hidden rounded-xl border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-[var(--sispaa-background)] text-xs uppercase opacity-60 text-[var(--sispaa-text)]">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Tema</th>
                                        <th class="px-4 py-2 text-left">Materia</th>
                                        <th class="px-4 py-2 text-left">Docente</th>
                                        <th class="px-4 py-2 text-left">Laboratorio</th>
                                        <th class="px-4 py-2 text-left">Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="p in ultimasPracticas" :key="p.id" class="border-t border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                                        <td class="px-4 py-3 font-semibold text-[var(--sispaa-text)]">{{ p.tema }}</td>
                                        <td class="px-4 py-3 opacity-70 text-[var(--sispaa-text)]">{{ p.materia }} · {{ p.carrera }}</td>
                                        <td class="px-4 py-3 opacity-70 text-[var(--sispaa-text)]">{{ p.docente }}</td>
                                        <td class="px-4 py-3 opacity-50 text-xs text-[var(--sispaa-text)]">{{ p.laboratorio ?? '—' }}</td>
                                        <td class="px-4 py-3 opacity-50 text-xs text-[var(--sispaa-text)]">{{ p.fecha }}</td>
                                    </tr>
                                    <tr v-if="ultimasPracticas.length === 0">
                                        <td colspan="5" class="px-4 py-8 text-center text-sm opacity-50 text-[var(--sispaa-text)]">Aún no hay prácticas registradas.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <h2 class="text-sm font-bold text-[var(--sispaa-text)] mb-4">Laboratorios más usados</h2>
                    <div class="space-y-3">
                        <div v-for="l in laboratoriosMasUsados" :key="l.id" class="flex items-center justify-between rounded-xl p-3 bg-[var(--sispaa-background)]">
                            <div class="flex items-center gap-2">
                                <MapPin class="h-4 w-4 text-[var(--sispaa-primary)]" />
                                <span class="text-sm font-semibold text-[var(--sispaa-text)]">{{ l.nombre }}</span>
                            </div>
                            <span class="text-sm font-bold text-[var(--sispaa-primary)]">{{ l.usos }}</span>
                        </div>
                        <div v-if="laboratoriosMasUsados.length === 0" class="text-center text-sm opacity-50 text-[var(--sispaa-text)] py-6">
                            No hay laboratorios registrados.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
