<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useDebounceFn } from '@vueuse/core';
import {
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { makeCarreraColumns, type Carrera } from './columns';

interface PaginatedData {
    data: Carrera[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    carreras: PaginatedData;
    filters?: { q?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters?.q ?? '');

const debouncedSearch = useDebounceFn(() => {
    router.get(route('admin.carreras.index'), { q: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 400);

const toggleStatus = (carrera: Carrera) => {
    router.post(route('admin.carreras.toggle-status', carrera.id), {}, { preserveScroll: true });
};

const columns = makeCarreraColumns({ onToggleStatus: toggleStatus });

const table = useVueTable({
    get data() { return props.carreras?.data || [] },
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    pageCount: props.carreras.last_page,
});

const goToPage = (page: number) => {
    router.get(route('admin.carreras.index'), { page, q: search.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Carreras" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        Carreras
                    </h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Programas académicos de la institución. La malla de asignaturas se gestiona en su propia sección.
                    </p>
                </div>
                <Button as-child class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('admin.carreras.create')">
                        <Plus class="h-4 w-4 mr-1.5" /> Nueva Carrera
                    </Link>
                </Button>
            </div>

            <div class="max-w-5xl w-full space-y-4">
                <!-- Búsqueda -->
                <div class="relative max-w-sm">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o código..."
                        @input="debouncedSearch"
                        class="pl-9 w-full rounded-lg border-slate-200 bg-white dark:bg-slate-950 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-850 dark:text-slate-350"
                    />
                </div>

                <!-- Tabla -->
                <div class="rounded-lg border border-border bg-card">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <TableHead v-for="header in headerGroup.headers" :key="header.id">
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <template v-if="table.getRowModel().rows.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-24 text-center">
                                    No hay carreras registradas
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        Mostrando {{ carreras.from || 0 }} a {{ carreras.to || 0 }} de {{ carreras.total }} resultados
                    </div>
                    <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" :disabled="carreras.current_page === 1" @click="goToPage(carreras.current_page - 1)">
                            Anterior
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            Página {{ carreras.current_page }} de {{ carreras.last_page }}
                        </span>
                        <Button variant="outline" size="sm" :disabled="carreras.current_page === carreras.last_page" @click="goToPage(carreras.current_page + 1)">
                            Siguiente
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
