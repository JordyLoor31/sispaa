<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Search } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import { ref } from 'vue';
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
import { Button } from '@/components/ui/button';
import { makePerfilColumns, type PerfilListado } from './columns';

interface PaginatedData {
    data: PerfilListado[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    perfiles: PaginatedData;
    filters?: { carrera_id?: string; facultad_id?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref('');

const debouncedSearch = useDebounceFn(() => {
    router.get(route('admin.estudiantes.perfiles.index'), { q: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 400);

const columns = makePerfilColumns();

const table = useVueTable({
    get data() { return props.perfiles?.data ?? []; },
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    pageCount: props.perfiles.last_page,
});

const goToPage = (page: number) => {
    router.get(route('admin.estudiantes.perfiles.index'), { page }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Perfiles de Estudiantes" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Perfiles de Estudiantes
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Datos académicos, de residencia y familiares completados por cada estudiante.
                </p>
            </div>

            <div class="w-full space-y-4">
                <div class="relative max-w-sm">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre o correo..."
                        @input="debouncedSearch"
                        class="pl-9 w-full rounded-lg border-slate-200 bg-white dark:bg-slate-950 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-850 dark:text-slate-350"
                    />
                </div>

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
                                    No hay perfiles registrados todavía
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex items-center justify-between">
                    <div class="text-sm text-muted-foreground">
                        Mostrando {{ perfiles.from || 0 }} a {{ perfiles.to || 0 }} de {{ perfiles.total }} resultados
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="perfiles.current_page === 1" @click="goToPage(perfiles.current_page - 1)">
                            Anterior
                        </Button>
                        <span class="text-sm text-muted-foreground">
                            Página {{ perfiles.current_page }} de {{ perfiles.last_page }}
                        </span>
                        <Button variant="outline" size="sm" :disabled="perfiles.current_page === perfiles.last_page" @click="goToPage(perfiles.current_page + 1)">
                            Siguiente
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
