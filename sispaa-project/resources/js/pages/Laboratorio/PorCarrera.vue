<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookOpen, FlaskConical, Users, MapPin } from 'lucide-vue-next';

interface CarreraStat { id: number; nombre: string; practicas: number; estudiantes: number; laboratorios: number }

defineProps<{ carreras: CarreraStat[] }>();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Laboratorio', href: route('laboratorio.index') },
    { title: 'Por Carrera', href: route('laboratorio.porCarrera') },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Laboratorio por Carrera" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Indicadores por Carrera</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Uso de laboratorios y prácticas realizadas por carrera.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div v-for="c in carreras" :key="c.id" class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <div class="flex items-center gap-2 mb-3">
                        <BookOpen class="h-4 w-4 text-indigo-500" />
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">{{ c.nombre }}</h3>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1.5"><FlaskConical class="h-3.5 w-3.5" /> Prácticas</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ c.practicas }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1.5"><Users class="h-3.5 w-3.5" /> Estudiantes</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ c.estudiantes }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-500">
                            <span class="flex items-center gap-1.5"><MapPin class="h-3.5 w-3.5" /> Laboratorios</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ c.laboratorios }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="carreras.length === 0" class="col-span-full rounded-2xl border border-dashed border-slate-200 dark:border-slate-800 p-10 text-center text-sm text-slate-400">
                    No hay carreras registradas.
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
