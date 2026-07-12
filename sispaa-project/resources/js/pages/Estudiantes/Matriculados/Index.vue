<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import makeMatriculadosColumns from './columns';
import type { Catalogo, Paginated, StudentRow } from './types';

const props = defineProps<{
    students: Paginated<StudentRow>;
    carreras: Catalogo[];
    filters: { carrera_id?: string; estado?: string; q?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const q = ref(props.filters.q ?? '');
const carreraId = ref(props.filters.carrera_id ?? 'all');
const estado = ref(props.filters.estado ?? 'all');

const aplicar = () => {
    router.get(route('estudiantes.matriculados'), {
        q: q.value || undefined,
        carrera_id: carreraId.value !== 'all' ? carreraId.value : undefined,
        estado: estado.value !== 'all' ? estado.value : undefined,
    }, { preserveState: true, replace: true });
};

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};

const columns = makeMatriculadosColumns();

const table = useVueTable(reactive({
    get data() { return props.students.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Estudiantes Matriculados" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Estudiantes Matriculados</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Lista de estudiantes matriculados en la institución.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Buscar</label>
                    <input v-model="q" @keyup.enter="aplicar" placeholder="Nombre, correo o cédula"
                        class="w-[240px] rounded-lg border-slate-300 bg-white text-sm dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Carrera</label>
                    <Select v-model="carreraId" @update:model-value="aplicar">
                        <SelectTrigger class="w-[200px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Estado</label>
                    <Select v-model="estado" @update:model-value="aplicar">
                        <SelectTrigger class="w-[160px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="activo">Activo</SelectItem>
                            <SelectItem value="retirado">Retirado</SelectItem>
                            <SelectItem value="egresado">Egresado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
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
                                    No hay estudiantes para los filtros seleccionados.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                    <span class="text-xs text-slate-500">Mostrando {{ students.data.length }} de {{ students.total }} registros</span>
                    <div class="flex items-center gap-1">
                        <button v-for="link in students.links" :key="link.label" @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active" v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="[link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40']" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
