<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Plus, FileText, Search } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Plantillas de Documentos</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Formatos institucionales disponibles para que cualquier estudiante los descargue desde su portal.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('secretaria.plantillas.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Plantilla
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <div class="relative min-w-[240px] flex-1 sm:flex-none sm:w-[280px]">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nombre..."
                            class="w-full rounded-lg border-slate-200 bg-slate-50/30 pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                            @input="debouncedSearch"
                        />
                    </div>
                </div>

                <div class="rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-slate-200/80 dark:border-slate-800">
                                    <TableHead v-for="header in hg.headers" :key="header.id" class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10 transition-colors">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 text-slate-400">
                                            <FileText class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay plantillas registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                        <span class="text-xs text-slate-500">Mostrando {{ plantillas.data.length }} de {{ plantillas.total }} plantillas</span>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in plantillas.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                :class="[link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40']"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
