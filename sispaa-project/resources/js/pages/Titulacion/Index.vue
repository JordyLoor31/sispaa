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
import makeTitulacionColumns, { type Titulacion } from './columns';

const props = defineProps<{
    titulaciones: Titulacion[];
    filters: { estado?: string };
    stats: { en_proceso: number; defendido: number; graduado: number; total: number };
    puedeGestionar: boolean;
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterEstado = ref(props.filters.estado || 'all');
const applyFilter = () => {
    router.get(route('titulacion.index'), { estado: filterEstado.value !== 'all' ? filterEstado.value : undefined }, { preserveState: true, replace: true });
};

const changeEstado = (t: Titulacion, estado: string) => {
    router.put(route('titulacion.update', t.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const columns = makeTitulacionColumns({ onChangeEstado: changeEstado, puedeGestionar: props.puedeGestionar });

const table = useVueTable(reactive({
    get data() { return props.titulaciones; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Titulación" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Titulación</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Temas, procesos en curso y estudiantes titulados.</p>
                </div>
                <Button v-if="puedeGestionar" as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('titulacion.create')">
                        <Plus class="h-4 w-4" /> Registrar Tema
                    </Link>
                </Button>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-950">
                    <p class="text-xs font-semibold text-slate-500 uppercase">Total</p>
                    <p class="mt-1 text-3xl font-extrabold text-slate-900 dark:text-white">{{ stats.total }}</p>
                </div>
                <div class="rounded-2xl border border-amber-200/60 bg-amber-50/40 p-5 shadow-sm dark:border-amber-900/30 dark:bg-amber-950/10">
                    <p class="text-xs font-semibold text-amber-700 uppercase">En proceso</p>
                    <p class="mt-1 text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ stats.en_proceso }}</p>
                </div>
                <div class="rounded-2xl border border-indigo-200/60 bg-indigo-50/40 p-5 shadow-sm dark:border-indigo-900/30 dark:bg-indigo-950/10">
                    <p class="text-xs font-semibold text-indigo-700 uppercase">Defendidos</p>
                    <p class="mt-1 text-3xl font-extrabold text-indigo-700 dark:text-indigo-300">{{ stats.defendido }}</p>
                </div>
                <div class="rounded-2xl border border-emerald-200/60 bg-emerald-50/40 p-5 shadow-sm dark:border-emerald-900/30 dark:bg-emerald-950/10">
                    <p class="text-xs font-semibold text-emerald-700 uppercase">Titulados</p>
                    <p class="mt-1 text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">{{ stats.graduado }}</p>
                </div>
            </div>

            <div class="w-full space-y-4">
                <div class="flex gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <Select v-model="filterEstado" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[200px]"><SelectValue placeholder="Estado" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los estados</SelectItem>
                            <SelectItem value="en_proceso">En proceso</SelectItem>
                            <SelectItem value="defendido">Defendido</SelectItem>
                            <SelectItem value="graduado">Graduado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="rounded-lg border border-slate-200/80 bg-white dark:border-slate-800 dark:bg-slate-950 overflow-hidden">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                    class="border-b border-slate-200/80 dark:border-slate-800">
                                    <TableHead v-for="header in hg.headers" :key="header.id"
                                        class="h-12 px-4 text-sm font-medium text-slate-500 dark:text-slate-400">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-slate-100 dark:divide-slate-800 text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                        class="hover:bg-slate-50/30 dark:hover:bg-slate-900/10 transition-colors">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center text-sm text-slate-400">
                                        No hay procesos de titulación registrados.
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
