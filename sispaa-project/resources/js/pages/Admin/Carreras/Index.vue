<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive, computed } from 'vue';
import { Plus, BookOpen, GraduationCap } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Switch } from '@/components/ui/switch';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { useDebounceFn } from '@vueuse/core';
import makeMateriaColumns from './materia-columns';
import CarreraFormModal from './CarreraFormModal.vue';
import MateriaFormModal from './MateriaFormModal.vue';

interface Coordinator {
    id: number;
    name: string;
}

interface CarreraItem {
    id: number;
    nombre: string;
    codigo: string;
    coordinador_id: number | null;
    activa: boolean;
    coordinador?: Coordinator | null;
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
    coordinadores: Coordinator[];
    materias: PaginatedMaterias;
    filters: {
        carrera_id?: string;
        q?: string;
    };
} >();

const breadcrumbs: BreadcrumbItemType[] = [
    { title: 'Administración', href: '#' },
    { title: 'Malla Curricular', href: '/admin/carreras' },
];

// Active Tabs: 'carreras' | 'materias'
const activeTab = ref('carreras');

// Filter & Search State
const selectedCarreraFilter = ref(props.filters.carrera_id || 'all');
const search = ref(props.filters.q || '');

// Modals State
const showCarreraModal = ref(false);
const editingCarrera = ref<CarreraItem | null>(null);

const showMateriaModal = ref(false);
const editingMateria = ref<MateriaItem | null>(null);

const handleSearch = () => {
    router.get(route('admin.malla.index'), {
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

// Carrera actions
const openAddCarrera = () => {
    editingCarrera.value = null;
    showCarreraModal.value = true;
};

const openEditCarrera = (carrera: CarreraItem) => {
    editingCarrera.value = carrera;
    showCarreraModal.value = true;
};

const toggleCarreraActive = (carrera: CarreraItem) => {
    router.post(route('admin.carreras.toggle-status', carrera.id), {}, {
        preserveScroll: true
    });
};

// Materia actions
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

// TanStack Columns Definition
const columns = makeMateriaColumns({
    onEditMateria: openEditMateria,
    onToggleStatus: toggleMateriaActive,
});

console.log('Props Materias:', props.materias);
console.log('Columns:', columns);

// Reactivity options wrapper to resolve state updates
const table = useVueTable(reactive({
    get data() { return props.materias.data },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));

console.log('Table Row Model:', table.getRowModel().rows);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Configuración de Malla Curricular" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Configuración de Malla Curricular
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Administra los programas académicos de la institución y sus asignaturas correspondientes.
                </p>
            </div>

            <!-- Tabs Navigation -->
            <div class="flex border-b border-slate-200 dark:border-slate-800">
                <button
                    @click="activeTab = 'carreras'"
                    class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors flex items-center gap-1.5"
                    :class="[activeTab === 'carreras' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-350']"
                >
                    <GraduationCap class="h-4 w-4" />
                    Carreras (Programas)
                </button>
                <button
                    @click="activeTab = 'materias'"
                    class="px-4 py-2.5 text-sm font-semibold border-b-2 transition-colors flex items-center gap-1.5"
                    :class="[activeTab === 'materias' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:hover:text-slate-350']"
                >
                    <BookOpen class="h-4 w-4" />
                    Asignaturas (Malla Curricular)
                </button>
            </div>

            <!-- Tab 1: Carreras -->
            <div v-if="activeTab === 'carreras'" class="space-y-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-white">Programas Académicos</h2>
                    <Button @click="openAddCarrera" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold flex items-center gap-1">
                        <Plus class="h-4 w-4" /> Nueva Carrera
                    </Button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="carrera in carreras"
                        :key="carrera.id"
                        class="bg-white dark:bg-slate-950 p-6 rounded-2xl border border-slate-100 dark:border-slate-850 shadow-sm hover:shadow-md transition-all flex flex-col justify-between"
                    >
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <span class="rounded bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 px-2.5 py-1 text-xs font-bold uppercase tracking-wider">
                                    {{ carrera.codigo }}
                                </span>
                                <div class="flex items-center gap-2">
                                    <Switch
                                        :checked="!!carrera.activa"
                                        :model-value="!!carrera.activa"
                                        @update:checked="toggleCarreraActive(carrera)"
                                        @update:model-value="toggleCarreraActive(carrera)"
                                    />
                                    <span class="text-xxs font-semibold" :class="[carrera.activa ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400']">
                                        {{ carrera.activa ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </div>
                            </div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-2 line-clamp-2">
                                {{ carrera.nombre }}
                            </h3>
                            <p class="text-xs text-slate-400 mb-4 flex items-center gap-1">
                                <span class="font-semibold text-slate-500">Coordinador:</span>
                                {{ carrera.coordinador?.name || 'No Asignado' }}
                            </p>
                        </div>

                        <div class="flex justify-end gap-2 border-t border-slate-50 dark:border-slate-900 pt-4">
                            <Button @click="openEditCarrera(carrera)" variant="outline" size="sm" class="text-slate-600 border-slate-200 dark:border-slate-800 dark:text-slate-350">
                                Editar Carrera
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Materias -->
            <div v-if="activeTab === 'materias'" class="space-y-6">
                <!-- Filters & Action -->
                <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                    <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto flex-1">
                        <!-- Search input -->
                        <div class="relative flex-1 max-w-md">
                            <Plus class="absolute left-3 top-2.5 h-4 w-4 rotate-45 text-slate-400" />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Buscar asignatura por nombre o código..."
                                @input="debouncedSearch"
                                class="pl-9 w-full rounded-lg border-slate-200 bg-white dark:bg-slate-950 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-850 dark:bg-slate-950 dark:text-slate-350"
                            />
                        </div>

                        <!-- Carrera filter -->
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

                <!-- TanStack Data Table for Subjects -->
                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="border-b border-slate-100 dark:border-slate-850 bg-slate-50/50 dark:bg-slate-900/30">
                                    <TableHead v-for="header in headerGroup.headers" :key="header.id" class="py-3.5 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-850 text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="py-3.5 px-4">
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

                    <!-- Pagination -->
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
            </div>

            <!-- Carrera Form Modal -->
            <CarreraFormModal
                v-model:open="showCarreraModal"
                :carrera="editingCarrera"
                :coordinadores="coordinadores"
            />

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