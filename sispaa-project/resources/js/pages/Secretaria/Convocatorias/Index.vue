<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, reactive } from 'vue';
import { Plus, Megaphone } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import makeConvocatoriaColumns from './columns';
import type { Convocatoria } from './types';

const props = defineProps<{
    convocatorias: Convocatoria[];
    filters: { modulo?: string };
    modulos: string[];
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterModulo = ref(props.filters.modulo || 'all');
const applyFilter = () => {
    router.get(
        route('secretaria.convocatorias.index'),
        { modulo: filterModulo.value !== 'all' ? filterModulo.value : undefined },
        { preserveState: true, replace: true },
    );
};

const columns = makeConvocatoriaColumns();

const table = useVueTable(
    reactive({
        get data() {
            return props.convocatorias;
        },
        columns,
        getCoreRowModel: getCoreRowModel(),
    }),
);
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Convocatorias" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Convocatorias y Fechas Límite</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Define ventanas de tiempo para la entrega de documentos por módulo.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 font-semibold text-white hover:bg-indigo-500">
                    <Link :href="route('secretaria.convocatorias.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Convocatoria
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex gap-3 rounded-xl border border-slate-200/80 bg-white p-4 dark:border-slate-800 dark:bg-slate-950">
                    <Select v-model="filterModulo" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[200px]"><SelectValue placeholder="Módulo" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los módulos</SelectItem>
                            <SelectItem v-for="m in modulos" :key="m" :value="m">{{ m }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-slate-200/80 dark:border-slate-800">
                                    <TableHead v-for="header in hg.headers" :key="header.id" class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 text-sm dark:divide-slate-800">
                                <template v-if="table.getRowModel().rows.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-slate-50/40 dark:hover:bg-slate-900/20">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 text-slate-400">
                                            <Megaphone class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay convocatorias registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
