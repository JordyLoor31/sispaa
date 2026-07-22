<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { GraduationCap, Plus, Search } from 'lucide-vue-next';
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
import { BRAND_GRADIENT } from '@/lib/brand';
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <!-- Header -->
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">
                                Carreras
                            </h1>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-bold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-primary)_60%,var(--sispaa-text))]">
                                {{ carreras.total }}
                            </span>
                        </div>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">
                            Programas académicos de la institución. La malla de asignaturas se gestiona en su propia sección.
                        </p>
                    </div>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 rounded-lg font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] transition-all bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] hover:shadow-lg">
                    <Link :href="route('admin.carreras.create')">
                        <Plus class="h-4 w-4" /> Nueva Carrera
                    </Link>
                </Button>
            </div>

            <!-- Tarjeta única: toolbar + tabla + paginación -->
            <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <!-- Toolbar -->
                <div class="border-b p-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div class="relative max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre o código..."
                            @input="debouncedSearch"
                            class="rounded-lg pl-9 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"
                        />
                    </div>
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
                                    <FlexRender
                                        v-if="!header.isPlaceholder"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)] text-sm">
                            <template v-if="table.getRowModel().rows.length">
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
                                            <GraduationCap class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                                        </div>
                                        <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No hay carreras registradas.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <!-- Paginación -->
                <div class="flex flex-col gap-2 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div class="text-xs opacity-60 text-[var(--sispaa-text)]">
                        Mostrando {{ carreras.from || 0 }} a {{ carreras.to || 0 }} de {{ carreras.total }} resultados
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" class="rounded-lg" :disabled="carreras.current_page === 1" @click="goToPage(carreras.current_page - 1)">
                            Anterior
                        </Button>
                        <span class="text-xs opacity-60 text-[var(--sispaa-text)]">
                            Página {{ carreras.current_page }} de {{ carreras.last_page }}
                        </span>
                        <Button variant="outline" size="sm" class="rounded-lg" :disabled="carreras.current_page === carreras.last_page" @click="goToPage(carreras.current_page + 1)">
                            Siguiente
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
