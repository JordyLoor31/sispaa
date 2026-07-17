<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Plus, UserCog } from 'lucide-vue-next';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import makeAsignacionDocenteColumns from './columns';
import type { AsignacionDocenteItem, Docente, MateriaOption, Paginated, PeriodoOption } from './types';

const props = defineProps<{
    asignaciones: Paginated<AsignacionDocenteItem>;
    docentes: Docente[];
    materias: MateriaOption[];
    periodos: PeriodoOption[];
    filters: { docente_id?: string; materia_id?: string; periodo_id?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterDocente = ref(props.filters.docente_id || 'all');
const filterMateria = ref(props.filters.materia_id || 'all');
const filterPeriodo = ref(props.filters.periodo_id || 'all');

const applyFilters = () => {
    router.get(
        route('secretaria.asignaciones-docente.index'),
        {
            docente_id: filterDocente.value !== 'all' ? filterDocente.value : undefined,
            materia_id: filterMateria.value !== 'all' ? filterMateria.value : undefined,
            periodo_id: filterPeriodo.value !== 'all' ? filterPeriodo.value : undefined,
        },
        { preserveState: true, replace: true },
    );
};

const columns = makeAsignacionDocenteColumns();

const table = useVueTable(
    reactive({
        get data() {
            return props.asignaciones.data;
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
        <Head title="Asignación de Docentes" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Asignación de Docentes</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Vincula docentes a materias/período/grupo. Sin una asignación aquí, el docente no ve materias en Mis Sílabos, Mis Informes ni Mis Estudiantes.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold">
                    <Link :href="route('secretaria.asignaciones-docente.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Asignación
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row flex-wrap bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                    <Select v-model="filterDocente" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[220px]"><SelectValue placeholder="Docente" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los docentes</SelectItem>
                            <SelectItem v-for="d in docentes" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterMateria" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[220px]"><SelectValue placeholder="Materia" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las materias</SelectItem>
                            <SelectItem v-for="m in materias" :key="m.id" :value="String(m.id)">{{ m.codigo }} — {{ m.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterPeriodo" @update:model-value="applyFilters">
                        <SelectTrigger class="w-[180px]"><SelectValue placeholder="Período" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los períodos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
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
                                            <UserCog class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay asignaciones registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                        <span class="text-xs text-slate-500">Mostrando {{ asignaciones.data.length }} de {{ asignaciones.total }} asignaciones</span>
                        <div class="flex items-center gap-1">
                            <button
                                v-for="link in asignaciones.links"
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
