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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Indicadores por Carrera</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Uso de laboratorios y prácticas realizadas por carrera.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="c in carreras" :key="c.id" class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <div class="flex items-center gap-2 mb-3">
                        <BookOpen class="h-4 w-4 text-[var(--sispaa-primary)]" />
                        <h3 class="text-sm font-bold text-[var(--sispaa-text)]">{{ c.nombre }}</h3>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs opacity-70 text-[var(--sispaa-text)]">
                            <span class="flex items-center gap-1.5"><FlaskConical class="h-3.5 w-3.5" /> Prácticas</span>
                            <span class="font-bold opacity-100 text-[var(--sispaa-text)]">{{ c.practicas }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs opacity-70 text-[var(--sispaa-text)]">
                            <span class="flex items-center gap-1.5"><Users class="h-3.5 w-3.5" /> Estudiantes</span>
                            <span class="font-bold opacity-100 text-[var(--sispaa-text)]">{{ c.estudiantes }}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs opacity-70 text-[var(--sispaa-text)]">
                            <span class="flex items-center gap-1.5"><MapPin class="h-3.5 w-3.5" /> Laboratorios</span>
                            <span class="font-bold opacity-100 text-[var(--sispaa-text)]">{{ c.laboratorios }}</span>
                        </div>
                    </div>
                </div>

                <div v-if="carreras.length === 0" class="col-span-full rounded-2xl border border-dashed p-10 text-center text-sm opacity-50 border-[color:color-mix(in_srgb,var(--sispaa-text)_25%,transparent)] text-[var(--sispaa-text)]">
                    No hay carreras registradas.
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
