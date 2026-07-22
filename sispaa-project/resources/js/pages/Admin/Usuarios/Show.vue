<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, FileText, GraduationCap, IdCard, Pencil, Phone, Shield } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { getInitials, getRoleBadgeStyle, type Usuario } from './columns';

const props = defineProps<{
    usuario: Usuario;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const formatDate = (date?: string) => {
    if (!date) return '—';
    return new Date(date).toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const esEstudiante = props.usuario.roles.some((rol) => rol.name === 'estudiante');

const infoCards = [
    { label: 'Identificación', icon: IdCard, value: props.usuario.cedula ?? '—' },
    { label: 'Teléfono', icon: Phone, value: props.usuario.telefono ?? '—' },
    { label: 'Carrera', icon: GraduationCap, value: props.usuario.carrera?.nombre ?? 'General / Institucional' },
    { label: 'Cuenta creada', icon: CalendarDays, value: formatDate(props.usuario.created_at) },
];
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="props.usuario.name" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <!-- Hero del usuario -->
            <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <!-- Franja superior con el degradado institucional -->
                <div
                    class="h-16"
                    style="background: linear-gradient(135deg, var(--sispaa-primary), color-mix(in srgb, var(--sispaa-primary) 45%, var(--sispaa-secondary)))"
                ></div>

                <div class="flex flex-col gap-4 p-5 sm:p-6 md:flex-row md:items-end md:justify-between">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <div
                            class="-mt-14 flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl text-2xl font-bold text-white shadow-md ring-4 ring-[var(--sispaa-background)]"
                            style="background: linear-gradient(135deg, var(--sispaa-primary), color-mix(in srgb, var(--sispaa-primary) 45%, var(--sispaa-secondary)))"
                        >
                            {{ getInitials(usuario.name) }}
                        </div>
                        <div>
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                                    {{ usuario.name }}
                                </h1>
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-bold"
                                    :class="usuario.is_active
                                        ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_15%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))] ring-1 ring-inset ring-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)]'
                                        : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)] opacity-60 text-[var(--sispaa-text)] ring-1 ring-inset ring-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]'"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full"
                                        :class="usuario.is_active ? 'bg-[var(--sispaa-secondary)]' : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_40%,transparent)]'"
                                    ></span>
                                    {{ usuario.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </div>
                            <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">{{ usuario.email }}</p>
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                <span
                                    v-for="rol in usuario.roles"
                                    :key="rol.id"
                                    class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold"
                                    :style="getRoleBadgeStyle(rol.name)"
                                >
                                    <Shield class="h-3 w-3" />
                                    {{ rol.name.charAt(0).toUpperCase() + rol.name.slice(1) }}
                                </span>
                                <span v-if="usuario.roles.length === 0" class="text-xs opacity-50 text-[var(--sispaa-text)]">Sin roles asignados</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Button as-child variant="outline" class="rounded-lg">
                            <Link :href="route('admin.usuarios.index')">
                                <ArrowLeft class="mr-1.5 h-4 w-4" /> Volver
                            </Link>
                        </Button>
                        <Button v-if="esEstudiante" as-child variant="outline" class="rounded-lg">
                            <Link :href="route('admin.estudiantes.perfiles.show', usuario.id)">
                                <FileText class="mr-1.5 h-4 w-4" /> Ver Datos Adicionales
                            </Link>
                        </Button>
                        <Button as-child class="rounded-lg text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                            <Link :href="route('admin.usuarios.edit', usuario.id)">
                                <Pencil class="mr-1.5 h-4 w-4" /> Editar
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Datos de la cuenta -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    v-for="card in infoCards"
                    :key="card.label"
                    class="flex items-start gap-3.5 rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)]">
                        <component :is="card.icon" class="h-5 w-5 text-[color:color-mix(in_srgb,var(--sispaa-primary)_70%,var(--sispaa-text))]" />
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-xs font-bold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">{{ card.label }}</h4>
                        <p class="mt-1 truncate text-sm font-semibold opacity-90 text-[var(--sispaa-text)]" :title="card.value">
                            {{ card.value }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
