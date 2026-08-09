<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Search, GraduationCap, Users } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import { DataTable } from '@/components/ui/data-table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
import makeEstudianteColumns, { type EstudianteRow } from './columns';

interface PaginatedEstudiantes {
    data: EstudianteRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Carrera {
    id: number;
    nombre: string;
}

const props = defineProps<{
    students: PaginatedEstudiantes;
    carreras: Carrera[];
    filters: { carrera_id?: string | number; q?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterCarrera = ref(props.filters.carrera_id ? String(props.filters.carrera_id) : 'all');

const applyFilters = () =>
    router.get(
        route('estudiantes.index'),
        {
            q: search.value || undefined,
            carrera_id: filterCarrera.value !== 'all' ? filterCarrera.value : undefined,
            per_page: props.students.per_page,
        },
        { preserveState: true, replace: true },
    );
const debouncedSearch = useDebounceFn(applyFilters, 300);

const columns = makeEstudianteColumns();

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Estudiantes" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <!-- Header -->
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                    <GraduationCap class="h-5 w-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Estudiantes</h1>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-primary)_60%,var(--sispaa-text))]">
                            {{ students.total }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Listado de los estudiantes registrados en el sistema.
                    </p>
                </div>
            </div>

            <div class="w-full space-y-4">
                <!-- Toolbar -->
                <div class="flex flex-wrap gap-3">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Buscar estudiante, cédula o correo..."
                            class="rounded-lg pl-9 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"
                        />
                    </div>
                    <Select v-model="filterCarrera" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full sm:w-[220px] bg-[var(--sispaa-background)]">
                            <SelectValue placeholder="Carrera" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- DataTable -->
                <DataTable :columns="columns" :data="students.data">
                    <template #empty>
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                            <Users class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                        </div>
                        <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No hay estudiantes para mostrar.</p>
                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Intenta cambiar los filtros de búsqueda.</p>
                    </template>
                </DataTable>

                <!-- Paginación -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">
                        Mostrando {{ students.data.length }} de {{ students.total }} estudiantes
                    </span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="link in students.links"
                            :key="link.label"
                            @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[
                                link.active
                                    ? 'text-white shadow-sm bg-[var(--sispaa-primary)]'
                                    : 'border text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:border-[var(--sispaa-primary)] hover:text-[var(--sispaa-primary)] disabled:opacity-50'
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
