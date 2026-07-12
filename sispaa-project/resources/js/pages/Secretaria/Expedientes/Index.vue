<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Search, FileText } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useDebounceFn } from '@vueuse/core';
import makeExpedienteColumns, { type DocumentoRow } from './columns';

interface PaginatedDocumentos {
    data: DocumentoRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    documentos: PaginatedDocumentos;
    tiposDocumento: string[];
    filters: { q?: string; estado?: string; tipo?: string; per_page?: string };
    stats: { pendientes: number; aprobados: number; rechazados: number; total: number };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q || '');
const filterEstado = ref(props.filters.estado || 'all');
const filterTipo = ref(props.filters.tipo || 'all');

const applyFilters = () => router.get(
    route('secretaria.expediente.index'),
    {
        q: search.value || undefined,
        estado: filterEstado.value !== 'all' ? filterEstado.value : undefined,
        tipo: filterTipo.value !== 'all' ? filterTipo.value : undefined,
        per_page: props.documentos.per_page,
    },
    { preserveState: true, replace: true }
);
const debouncedSearch = useDebounceFn(applyFilters, 300);

const columns = makeExpedienteColumns();

const table = useVueTable(reactive({
    get data() { return props.documentos.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Expediente SGA — Secretaría" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    Expediente SGA
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Valida, aprueba o rechaza los documentos habilitantes subidos por los estudiantes.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-400 uppercase tracking-wide">Pendientes</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.pendientes }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 uppercase tracking-wide">Aprobados</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.aprobados }}</p>
                </div>
                <div class="rounded-2xl border border-rose-200/60 bg-rose-50/40 p-5 shadow-sm dark:border-rose-900/30 dark:bg-rose-950/10">
                    <p class="text-xs font-semibold text-rose-700 dark:text-rose-400 uppercase tracking-wide">Rechazados</p>
                    <p class="mt-1 text-3xl font-extrabold text-rose-700 dark:text-rose-300">{{ stats.rechazados }}</p>
                </div>
            </div>

            <div class="max-w-5xl w-full space-y-4">
                <div class="flex flex-col sm:flex-row gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <div class="flex-1 relative">
                        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
                        <input
                            v-model="search"
                            @input="debouncedSearch"
                            type="text"
                            placeholder="Buscar estudiante, cédula o correo..."
                            class="pl-9 w-full rounded-lg border-slate-200 bg-slate-50/30 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-200"
                        />
                    </div>
                    <Select v-model="filterEstado" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[160px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los estados</SelectItem>
                            <SelectItem value="pendiente">Pendiente</SelectItem>
                            <SelectItem value="aprobado">Aprobado</SelectItem>
                            <SelectItem value="rechazado">Rechazado</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterTipo" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[220px]"><SelectValue placeholder="Tipo de documento" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los tipos</SelectItem>
                            <SelectItem v-for="t in tiposDocumento" :key="t" :value="t">{{ t }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow
                                    v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                    class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30"
                                >
                                    <TableHead
                                        v-for="header in hg.headers" :key="header.id"
                                        class="py-3.5 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide"
                                    >
                                        <FlexRender
                                            v-if="!header.isPlaceholder"
                                            :render="header.column.columnDef.header"
                                            :props="header.getContext()"
                                        />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow
                                        v-for="row in table.getRowModel().rows" :key="row.id"
                                        class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10 transition-colors"
                                    >
                                        <TableCell
                                            v-for="cell in row.getVisibleCells()" :key="cell.id"
                                            class="py-3.5 px-4"
                                        >
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 text-slate-400">
                                            <FileText class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay documentos para mostrar</span>
                                            <span class="text-xs">Intenta cambiar los filtros de búsqueda</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                        <span class="text-xs text-slate-500">
                            Mostrando {{ documentos.data.length }} de {{ documentos.total }} documentos
                        </span>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in documentos.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                                :class="[
                                    link.active
                                        ? 'bg-indigo-600 text-white'
                                        : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40'
                                ]"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
