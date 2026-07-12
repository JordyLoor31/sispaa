<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Plus } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { toast } from 'vue-sonner';
import makeReactivoColumns from './columns';
import type { Catalogo, ReactivoItem } from './types';

const props = defineProps<{
    reactivos: ReactivoItem[];
    laboratorios: Catalogo[];
    filters: { laboratorio_id?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterLab = ref(props.filters.laboratorio_id || 'all');
const applyFilter = () => {
    router.get(route('laboratorio.reactivos'), { laboratorio_id: filterLab.value !== 'all' ? filterLab.value : undefined }, { preserveState: true, replace: true });
};

const changeEstado = (r: ReactivoItem, estado: string) => {
    router.put(route('laboratorio.reactivos.update', r.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const columns = makeReactivoColumns({ onChangeEstado: changeEstado });

const table = useVueTable(reactive({
    get data() { return props.reactivos; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Reactivos de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Reactivos</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Inventario de reactivos de laboratorio.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('laboratorio.reactivos.create')">
                        <Plus class="h-4 w-4" /> Nuevo Reactivo
                    </Link>
                </Button>
            </div>

            <div class="max-w-5xl w-full space-y-4">
                <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <Select v-model="filterLab" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[220px]"><SelectValue placeholder="Laboratorio" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los laboratorios</SelectItem>
                            <SelectItem v-for="l in laboratorios" :key="l.id" :value="String(l.id)">{{ l.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                    class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/30">
                                    <TableHead v-for="header in hg.headers" :key="header.id"
                                        class="py-3.5 px-4 text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                        class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10 transition-colors">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="py-3.5 px-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center text-sm text-slate-400">
                                        No hay reactivos registrados.
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
