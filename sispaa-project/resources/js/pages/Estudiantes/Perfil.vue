<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { User, Mail, Shield, Phone, Bookmark, BookOpen, ClipboardEdit, FileText } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

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
        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Mi Perfil
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Visualiza y corrobora tus datos personales e institucionales registrados en el sistema.
                    </p>
                </div>
                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <Button as-child variant="outline">
                        <Link :href="route('student.perfil.show')">
                            <FileText class="mr-1.5 h-4 w-4" /> Mis Datos
                        </Link>
                    </Button>
                    <Button as-child class="font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                        <Link :href="route('student.perfil.edit')">
                            <ClipboardEdit class="mr-1.5 h-4 w-4" /> Completar / Editar Perfil
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Profile Info Cards -->
            <div class="mx-auto w-full max-w-3xl rounded-2xl p-5 shadow-sm sm:p-8 bg-[var(--sispaa-surface)]">
                <!-- User Initials / Avatar Circle -->
                <div class="flex flex-col items-center gap-6 border-b pb-8 sm:flex-row border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full text-3xl font-black text-white shadow-lg bg-[var(--sispaa-primary)]">
                        {{ user_data.name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() }}
                    </div>
                    <div class="space-y-1 text-center sm:text-left">
                        <h3 class="text-2xl font-black leading-tight text-[var(--sispaa-text)]">
                            {{ user_data.name }}
                        </h3>
                        <div class="mt-2 flex flex-wrap items-center justify-center gap-2 sm:justify-start">
                            <span class="rounded-full px-3 py-0.5 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)] text-[var(--sispaa-primary)]">
                                Estudiante Universitario
                            </span>
                            <span v-if="user_data.carrera_codigo" class="rounded-full px-3 py-0.5 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[var(--sispaa-text)]">
                                Carrera: {{ user_data.carrera_codigo }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Detalle de campos -->
                <div class="mt-8 grid gap-6 md:grid-cols-2">
                    <!-- Nombre Completo -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-80 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <User class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Nombre Completo</p>
                            <p class="mt-0.5 text-sm font-bold text-[var(--sispaa-text)]">
                                {{ user_data.name }}
                            </p>
                        </div>
                    </div>

                    <!-- Correo Electrónico -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-80 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Mail class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Correo Institucional</p>
                            <p class="mt-0.5 text-sm font-bold text-[var(--sispaa-text)]">
                                {{ user_data.email }}
                            </p>
                        </div>
                    </div>

                    <!-- Cédula -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-80 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Shield class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Identificación / Cédula</p>
                            <p class="mt-0.5 text-sm font-bold text-[var(--sispaa-text)]">
                                {{ user_data.cedula }}
                            </p>
                        </div>
                    </div>

                    <!-- Teléfono -->
                    <div class="flex items-start gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-80 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <Phone class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Teléfono de Contacto</p>
                            <p class="mt-0.5 text-sm font-bold text-[var(--sispaa-text)]">
                                {{ user_data.telefono }}
                            </p>
                        </div>
                    </div>

                    <!-- Carrera Completa -->
                    <div class="flex items-start gap-3 md:col-span-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-80 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <BookOpen class="h-4 w-4" />
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Carrera / Programa de Estudio</p>
                            <p class="mt-0.5 text-sm font-bold text-[var(--sispaa-text)]">
                                {{ user_data.carrera }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
