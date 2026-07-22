<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { BookOpen, Plus, Search } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
import { BRAND_GRADIENT } from '@/lib/brand';
import makeMateriaColumns from './materia-columns';
import MateriaFormModal from './MateriaFormModal.vue';

interface CarreraItem {
    id: number;
    nombre: string;
}

interface MateriaItem {
    id: number;
    nombre: string;
    codigo: string;
    creditos: number;
    nivel: number;
    carrera_id: number;
    activa?: boolean;
    carrera?: CarreraItem;
}

interface PaginatedMaterias {
    data: MateriaItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: any[];
}

const props = defineProps<{
    carreras: CarreraItem[];
    materias: PaginatedMaterias;
    filters: {
        carrera_id?: string;
        q?: string;
    };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const selectedCarreraFilter = ref(props.filters.carrera_id || 'all');
const search = ref(props.filters.q || '');

const showMateriaModal = ref(false);
const editingMateria = ref<MateriaItem | null>(null);

const handleSearch = () => {
    router.get(route('admin.materias.index'), {
        carrera_id: selectedCarreraFilter.value,
        q: search.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const debouncedSearch = useDebounceFn(() => {
    handleSearch();
}, 300);

const handleCarreraFilterChange = (val: string) => {
    selectedCarreraFilter.value = val;
    handleSearch();
};

const openAddMateria = () => {
    editingMateria.value = null;
    showMateriaModal.value = true;
};

const openEditMateria = (materia: MateriaItem) => {
    editingMateria.value = materia;
    showMateriaModal.value = true;
};

const toggleMateriaActive = (materia: MateriaItem) => {
    router.post(route('admin.materias.toggle-status', materia.id), {}, {
        preserveScroll: true
    });
};

const navigateToPage = (url: string | null) => {
    if (url) {
        router.get(url, {}, { preserveState: true });
    }
};

const columns = makeMateriaColumns({
    onEditMateria: openEditMateria,
    onToggleStatus: toggleMateriaActive,
});

const table = useVueTable(reactive({
    get data() { return props.materias.data },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Asignaturas" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                        <BookOpen class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                                Asignaturas
                            </h1>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-primary)_60%,var(--sispaa-text))]">
                                {{ materias.total }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                            Administra la malla curricular de cada carrera. La gestión de las carreras en sí vive en su propia sección.
                        </p>
                    </div>
                </div>
                <Button @click="openAddMateria" class="flex w-full items-center gap-1.5 rounded-lg font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] transition-all md:w-auto bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] hover:shadow-lg">
                    <Plus class="h-4 w-4" /> Nueva Asignatura
                </Button>
            </div>

            <!-- Tarjeta única: toolbar + tabla + paginación -->
            <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <!-- Toolbar de filtros -->
                <div class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar asignatura por nombre o código..."
                            @input="debouncedSearch"
                            class="rounded-lg pl-9 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"
                        />
                    </div>

                    <Select v-model="selectedCarreraFilter" @update:model-value="handleCarreraFilterChange">
                        <SelectTrigger class="w-full rounded-lg sm:w-[200px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <SelectValue placeholder="Todas las carreras" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg">
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="c.id.toString()">
                                {{ c.nombre }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow
                                v-for="headerGroup in table.getHeaderGroups()"
                                :key="headerGroup.id"
                                class="border-b bg-[color:color-mix(in_srgb,var(--sispaa-text)_3%,transparent)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                <TableHead
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    class="h-9 px-3 text-xs font-semibold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]"
                                >
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)] text-sm">
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                    class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]"
                                >
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-3 py-2">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-40">
                                    <div class="flex flex-col items-center justify-center gap-2 text-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]">
                                            <BookOpen class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                                        </div>
                                        <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No se encontraron asignaturas.</p>
                                        <p class="text-xs opacity-50 text-[var(--sispaa-text)]">Prueba con otro término o cambia el filtro de carrera.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex flex-col gap-2 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">
                        Mostrando {{ materias.data.length }} de {{ materias.total }} asignaturas
                    </span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="link in materias.links"
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
                        ></button>
                    </div>
                </div>
            </div>

            <!-- Materia Form Modal -->
            <MateriaFormModal
                v-model:open="showMateriaModal"
                :materia="editingMateria"
                :carreras="carreras"
                :defaultCarreraId="selectedCarreraFilter"
            />
        </div>
    </AppSidebarLayout>
</template>
