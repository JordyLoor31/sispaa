<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Plus, Search } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                    Asignaturas (Malla Curricular)
                </h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Administra las asignaturas de cada carrera. La gestión de las carreras en sí vive en su propia sección.
                </p>
            </div>

            <!-- Filters & Action -->
            <div class="flex flex-col items-stretch gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex w-full flex-1 flex-col gap-4 sm:flex-row md:w-auto">
                    <div class="relative max-w-md flex-1">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar asignatura por nombre o código..."
                            @input="debouncedSearch"
                            class="pl-9 bg-[var(--sispaa-background)]"
                        />
                    </div>

                    <Select v-model="selectedCarreraFilter" @update:model-value="handleCarreraFilterChange">
                        <SelectTrigger class="w-full rounded-lg sm:w-[200px] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
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

                <Button @click="openAddMateria" class="flex w-full items-center gap-1 font-semibold text-white md:w-auto bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Plus class="h-4 w-4" /> Nueva Asignatura
                </Button>
            </div>

            <!-- Tabla -->
            <div class="overflow-hidden rounded-lg border bg-[var(--sispaa-surface)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                <TableHead v-for="header in headerGroup.headers" :key="header.id" class="h-12 px-4 text-sm font-medium opacity-60 text-[var(--sispaa-text)]">
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_4%,transparent)]">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-24 text-center opacity-60 text-[var(--sispaa-text)]">
                                    No se encontraron asignaturas.
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
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="[
                                link.active
                                    ? 'text-white bg-[var(--sispaa-primary)]'
                                    : 'border text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)] disabled:opacity-50'
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
