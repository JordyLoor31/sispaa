<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { User, Mail, Shield, Phone, Bookmark, BookOpen } from 'lucide-vue-next';

interface UserData {
    name: string;
    email: string;
    cedula: string;
    telefono: string;
    carrera: string;
    carrera_codigo: string | null;
}

defineProps<{
    user_data: UserData;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Estudiante',
        href: '/dashboard',
    },
    {
        title: 'Mi Perfil',
        href: '/estudiante/perfil',
    },
];
</script>

<template>
    <Head title="Mi Perfil" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Mi Perfil
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Visualiza y corrobora tus datos personales e institucionales registrados en el sistema.
                </p>
            </div>

            <!-- Profile Info Cards -->
            <div class="max-w-3xl rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                <!-- User Initials / Avatar Circle -->
                <div class="flex flex-col sm:flex-row items-center gap-6 pb-8 border-b border-slate-100 dark:border-slate-800/85">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-indigo-500 to-purple-500 text-white text-3xl font-black shadow-lg">
                        {{ user_data.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() }}
                    </div>
                    <div class="text-center sm:text-left space-y-1">
                        <h3 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">
                            {{ user_data.name }}
                        </h3>
                        <div class="flex flex-wrap justify-center sm:justify-start items-center gap-2 mt-2">
                            <span class="rounded-full bg-indigo-50 px-3 py-0.5 text-xs font-semibold text-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-400">
                                Estudiante Universitario
                            </span>
                            <span v-if="user_data.carrera_codigo" class="rounded-full bg-slate-100 px-3 py-0.5 text-xs font-semibold text-slate-700 dark:bg-slate-900 dark:text-slate-400">
                                Carrera: {{ user_data.carrera_codigo }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detalle de campos -->
                <div class="grid gap-6 md:grid-cols-2 mt-8">
                    <!-- Nombre Completo -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 border border-slate-150 text-slate-500 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-400">
                            <User class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Nombre Completo</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ user_data.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Correo Electrónico -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 border border-slate-150 text-slate-500 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-400">
                            <Mail class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Correo Institucional</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ user_data.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Cédula -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 border border-slate-150 text-slate-500 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-400">
                            <Shield class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Identificación / Cédula</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ user_data.cedula }}
                            </p>
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 border border-slate-150 text-slate-500 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-400">
                            <Phone class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Teléfono de Contacto</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ user_data.telefono }}
                            </p>
                        </div>
                    </div>

                    <!-- Carrera Completa -->
                    <div class="flex items-start gap-3 md:col-span-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 border border-slate-150 text-slate-500 dark:bg-slate-900 dark:border-slate-800 dark:text-slate-400">
                            <BookOpen class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Carrera / Programa de Estudio</p>
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200 mt-0.5">
                                {{ user_data.carrera }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
