<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Search, BookOpen, CheckCircle2, Clock3, Files } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
import { BRAND_GRADIENT } from '@/lib/brand';
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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <!-- Header -->
            <div class="flex items-center gap-3.5">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                    <BookOpen class="h-5 w-5" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Revisión de Sílabos</h1>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-primary)_60%,var(--sispaa-text))]">
                            {{ stats.total }}
                        </span>
                    </div>
                    <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Aprueba o rechaza los sílabos subidos por los docentes.</p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="flex items-center gap-3.5 rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)]">
                        <Files class="h-5 w-5 text-[color:color-mix(in_srgb,var(--sispaa-primary)_70%,var(--sispaa-text))]" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider opacity-50 text-[var(--sispaa-text)]">Total</p>
                        <p class="text-2xl font-extrabold leading-tight text-[var(--sispaa-text)]">{{ stats.total }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3.5 rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,#E4BC57_18%,transparent)]">
                        <Clock3 class="h-5 w-5 text-[color:color-mix(in_srgb,#E4BC57_60%,var(--sispaa-text))]" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]">Pendientes</p>
                        <p class="text-2xl font-extrabold leading-tight text-[var(--sispaa-text)]">{{ stats.pendientes }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3.5 rounded-2xl border p-5 shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_15%,transparent)]">
                        <CheckCircle2 class="h-5 w-5 text-[color:color-mix(in_srgb,var(--sispaa-secondary)_60%,var(--sispaa-text))]" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]">Aprobados</p>
                        <p class="text-2xl font-extrabold leading-tight text-[var(--sispaa-text)]">{{ stats.aprobados }}</p>
                    </div>
                </div>
            </div>

            <!-- Tarjeta única: toolbar + tabla + paginación -->
            <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                <div class="flex flex-col gap-3 border-b p-4 sm:flex-row sm:items-center border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-50 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar docente..."
                            class="rounded-lg pl-9 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"
                            @input="debouncedSearch"
                        />
                    </div>
                    <Select v-model="filterEstado" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full rounded-lg sm:w-[160px] bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                            <SelectValue placeholder="Estado" />
                        </SelectTrigger>
                        <SelectContent class="rounded-lg">
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="subido">Subidos</SelectItem>
                            <SelectItem value="pendiente">Pendientes</SelectItem>
                            <SelectItem value="aprobado">Aprobados</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow
                                v-for="hg in table.getHeaderGroups()"
                                :key="hg.id"
                                class="border-b bg-[color:color-mix(in_srgb,var(--sispaa-text)_3%,transparent)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]"
                            >
                                <TableHead
                                    v-for="header in hg.headers"
                                    :key="header.id"
                                    class="h-9 px-3 text-xs font-semibold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]"
                                >
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_8%,transparent)] text-sm text-[var(--sispaa-text)]">
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
                                            <BookOpen class="h-5 w-5 opacity-40 text-[var(--sispaa-text)]" />
                                        </div>
                                        <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No hay sílabos que coincidan con el filtro.</p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex flex-col gap-2 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ silabos.data.length }} de {{ silabos.total }} sílabos</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button
                            v-for="link in silabos.links"
                            :key="link.label"
                            :disabled="!link.url || link.active"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[
                                link.active
                                    ? 'text-white shadow-sm bg-[var(--sispaa-primary)]'
                                    : 'border text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:border-[var(--sispaa-primary)] hover:text-[var(--sispaa-primary)] disabled:opacity-50'
                            ]"
                            @click="navigateToPage(link.url)"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
