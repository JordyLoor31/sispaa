<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Search } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
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
import { makePeriodoColumns, type Periodo } from './columns';

interface PaginatedData {
    data: Periodo[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

const props = defineProps<{
    periodos: PaginatedData;
    filters?: { q?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters?.q ?? '');

const debouncedSearch = useDebounceFn(() => {
    router.get(route('admin.periodos.index'), { q: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}, 400);

const activar = (periodo: Periodo) => {
    router.post(route('admin.periodos.activar', periodo.id), {}, { preserveScroll: true });
};

// 'Finalizar' se movió a la vista de Edición del periodo (ya no vive en la tabla, a pedido).
const columns = makePeriodoColumns({ onActivar: activar });

const table = useVueTable({
    get data() { return props.periodos?.data || [] },
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualPagination: true,
    pageCount: props.periodos.last_page,
});

const goToPage = (page: number) => {
    router.get(route('admin.periodos.index'), { page, q: search.value }, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Gestión de Periodos" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                        Gestión de Periodos
                    </h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Crea y administra los periodos académicos, su estado y sus fechas límite de sílabos e informes.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('admin.periodos.create')">
                        <Plus class="h-4 w-4" /> Nuevo Periodo
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <!-- Búsqueda -->
                <div class="relative max-w-sm">
                    <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                    <Input
                        v-model="search"
                        type="text"
                        placeholder="Buscar por nombre..."
                        @input="debouncedSearch"
                        class="pl-9 bg-[var(--sispaa-background)]"
                    />
                </div>

                <!-- Tabla -->
                <div class="overflow-x-auto rounded-lg border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] bg-[var(--sispaa-surface)]">
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
                                    No hay periodos registrados
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm opacity-60 text-[var(--sispaa-text)]">
                        Mostrando {{ periodos.from || 0 }} a {{ periodos.to || 0 }} de {{ periodos.total }} resultados
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="periodos.current_page === 1" @click="goToPage(periodos.current_page - 1)">
                            Anterior
                        </Button>
                        <span class="text-sm opacity-60 text-[var(--sispaa-text)]">
                            Página {{ periodos.current_page }} de {{ periodos.last_page }}
                        </span>
                        <Button variant="outline" size="sm" :disabled="periodos.current_page === periodos.last_page" @click="goToPage(periodos.current_page + 1)">
                            Siguiente
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
