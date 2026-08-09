<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Search, GraduationCap, Users } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
import { BRAND_GRADIENT } from '@/lib/brand';
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

const table = useVueTable(
    reactive({
        get data() {
            return props.students.data;
        },
        columns,
        getCoreRowModel: getCoreRowModel(),
    }),
);

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

            <!-- Tarjeta única: toolbar + tabla + paginación -->
            <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:flex-wrap sm:items-center border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
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
                        <SelectTrigger class="w-full rounded-lg sm:w-[220px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <SelectValue placeholder="Carrera" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg">
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow
                                v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                class="border-b bg-[color:color-mix(in_srgb,var(--sispaa-text)_3%,transparent)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                <TableHead
                                    v-for="header in hg.headers" :key="header.id"
                                    class="h-9 px-3 text-xs font-semibold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]"
                                >
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)] text-sm text-[var(--sispaa-text)]">
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow
                                    v-for="row in table.getRowModel().rows" :key="row.id"
                                    class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]"
                                >
                                    <TableCell
                                        v-for="cell in row.getVisibleCells()" :key="cell.id"
                                        class="px-3 py-2"
                                    >
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-40">
                                    <div class="flex flex-col items-center justify-center gap-2 text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                                            <Users class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                                        </div>
                                        <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No hay estudiantes para mostrar.</p>
                                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Intenta cambiar los filtros de búsqueda.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex flex-col gap-2 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
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
