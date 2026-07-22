<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Search, BookOpen } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
import makeSilaboColumns from './columns';
import type { SilaboItem } from './types';

interface Paginated<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] }
interface Stats { pendientes: number; aprobados: number; total: number }

const props = defineProps<{
    silabos: Paginated<SilaboItem>;
    filters: { q?: string; estado?: string; per_page?: string };
    stats: Stats;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterEstado = ref(props.filters.estado || 'all');

const applyFilters = () => {
    router.get(
        route('secretaria.silabos.index'),
        {
            q: search.value || undefined,
            estado: filterEstado.value !== 'all' ? filterEstado.value : undefined,
            per_page: props.silabos.per_page,
        },
        { preserveState: true, replace: true },
    );
};
const debouncedSearch = useDebounceFn(applyFilters, 300);

const columns = makeSilaboColumns();

const table = useVueTable(
    reactive({
        get data() {
            return props.silabos.data;
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
        <Head title="Revisión de Sílabos" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Revisión de Sílabos</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Aprueba o rechaza los sílabos subidos por los docentes.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-[var(--sispaa-text)]">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,#E4BC57_65%,black)]">Pendientes de revisión</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,#E4BC57_65%,black)]">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl p-5 shadow-sm bg-[var(--sispaa-surface)]">
                    <p class="text-xs font-semibold uppercase text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">Aprobados</p>
                    <p class="mt-1 text-3xl font-extrabold text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]">{{ stats.aprobados }}</p>
                </div>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-col gap-3 rounded-xl p-4 bg-[var(--sispaa-surface)] sm:flex-row">
                    <div class="relative min-w-[200px] flex-1">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-40 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar docente..."
                            class="bg-[var(--sispaa-background)] pl-9"
                            @input="debouncedSearch"
                        />
                    </div>
                    <Select v-model="filterEstado" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full bg-[var(--sispaa-background)] sm:w-[160px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="subido">Subidos</SelectItem>
                            <SelectItem value="pendiente">Pendientes</SelectItem>
                            <SelectItem value="aprobado">Aprobados</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="overflow-hidden rounded-lg bg-[var(--sispaa-surface)]">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                    <TableHead v-for="header in hg.headers" :key="header.id" class="h-12 px-4 text-sm font-medium opacity-60 text-[var(--sispaa-text)]">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm text-[var(--sispaa-text)]">
                                <template v-if="table.getRowModel().rows.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_5%,transparent)]">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 opacity-40 text-[var(--sispaa-text)]">
                                            <BookOpen class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay sílabos que coincidan con el filtro</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex flex-col gap-2 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ silabos.data.length }} de {{ silabos.total }} sílabos</span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="link in silabos.links"
                                :key="link.label"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-70 hover:opacity-100 disabled:opacity-40']"
                                @click="navigateToPage(link.url)"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
