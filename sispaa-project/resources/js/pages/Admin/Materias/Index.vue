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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Asignaturas (Malla Curricular)
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Administra las asignaturas de cada carrera. La gestión de las carreras en sí vive en su propia sección.
                </p>
            </div>

            <!-- Filters & Action -->
            <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto flex-1">
                    <div class="relative flex-1 max-w-md">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar asignatura por nombre o código..."
                            @input="debouncedSearch"
                            class="pl-9 w-full rounded-lg border-slate-200 bg-white dark:bg-slate-950 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-850 dark:text-slate-350"
                        />
                    </div>

                    <Select v-model="selectedCarreraFilter" @update:model-value="handleCarreraFilterChange">
                        <SelectTrigger class="w-[200px] rounded-lg border-slate-200 bg-white dark:bg-slate-950 dark:border-slate-850">
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

                <Button @click="openAddMateria" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold flex items-center gap-1 w-full md:w-auto">
                    <Plus class="h-4 w-4" /> Nueva Asignatura
                </Button>
            </div>

            <!-- Tabla -->
            <div class="rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="border-b border-slate-200/80 dark:border-slate-850">
                                <TableHead v-for="header in headerGroup.headers" :key="header.id" class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-24 text-center text-slate-500">
                                    No se encontraron asignaturas.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-850 px-6 py-4">
                    <span class="text-xs text-slate-500">
                        Mostrando {{ materias.data.length }} de {{ materias.total }} asignaturas
                    </span>
                    <div class="flex items-center gap-1">
                        <button
                            v-for="link in materias.links"
                            :key="link.label"
                            @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active"
                            v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="[
                                link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-350 disabled:opacity-50'
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
