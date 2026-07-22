<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Plus, FileText, Search, Files } from 'lucide-vue-next';
import { BRAND_GRADIENT } from '@/lib/brand';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useDebounceFn } from '@vueuse/core';
import makePlantillaColumns from './columns';
import type { PlantillaItem } from './types';

interface Paginated<T> { data: T[]; current_page: number; last_page: number; per_page: number; total: number; links: any[] }

const props = defineProps<{
    plantillas: Paginated<PlantillaItem>;
    filters: { q?: string; per_page?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');

const applyFilters = () => {
    router.get(
        route('secretaria.plantillas.index'),
        { q: search.value || undefined, per_page: props.plantillas.per_page },
        { preserveState: true, replace: true },
    );
};
const debouncedSearch = useDebounceFn(applyFilters, 300);

const columns = makePlantillaColumns();

const table = useVueTable(
    reactive({
        get data() {
            return props.plantillas.data;
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
        <Head title="Plantillas de Documentos" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_30%,var(--sispaa-background))]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-white shadow-sm" :style="BRAND_GRADIENT">
                        <Files class="h-5 w-5" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Plantillas de Documentos</h1>
                        <p class="mt-0.5 text-sm opacity-60 text-[var(--sispaa-text)]">Formatos institucionales disponibles para que cualquier estudiante los descargue desde su portal.</p>
                    </div>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 rounded-lg font-semibold text-white shadow-md shadow-[color:color-mix(in_srgb,var(--sispaa-primary)_30%,transparent)] transition-all bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)] hover:shadow-lg">
                    <Link :href="route('secretaria.plantillas.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Plantilla
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <div class="relative w-full max-w-sm">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 opacity-40 text-[var(--sispaa-text)]" />
                        <Input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre..."
                            class="rounded-lg pl-9 bg-[color:color-mix(in_srgb,var(--sispaa-surface)_35%,var(--sispaa-background))]"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>

                <div class="overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                    <TableHead v-for="header in hg.headers" :key="header.id" class="h-9 px-3 text-xs font-semibold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm text-[var(--sispaa-text)]">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-3 py-2">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 opacity-40 text-[var(--sispaa-text)]">
                                            <FileText class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay plantillas registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex flex-col gap-2 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ plantillas.data.length }} de {{ plantillas.total }} plantillas</span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="link in plantillas.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                :class="[link.active ? 'text-white shadow-sm bg-[var(--sispaa-primary)]' : 'border text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:border-[var(--sispaa-primary)] hover:text-[var(--sispaa-primary)] disabled:opacity-50']"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
