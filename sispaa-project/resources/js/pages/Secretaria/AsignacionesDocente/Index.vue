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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Asignación de Docentes</h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                        Vincula docentes a materias/período/grupo. Sin una asignación aquí, el docente no ve materias en Mis Sílabos, Mis Informes ni Mis Estudiantes.
                    </p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('secretaria.asignaciones-docente.create')">
                        <Plus class="h-4 w-4" />
                        Nueva Asignación
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-col flex-wrap gap-3 rounded-xl p-4 bg-[var(--sispaa-surface)] sm:flex-row">
                    <Select v-model="filterDocente" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full bg-[var(--sispaa-background)] sm:w-[220px]"><SelectValue placeholder="Docente" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los docentes</SelectItem>
                            <SelectItem v-for="d in docentes" :key="d.id" :value="String(d.id)">{{ d.name }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterMateria" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full bg-[var(--sispaa-background)] sm:w-[220px]"><SelectValue placeholder="Materia" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las materias</SelectItem>
                            <SelectItem v-for="m in materias" :key="m.id" :value="String(m.id)">{{ m.codigo }} — {{ m.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select v-model="filterPeriodo" @update:model-value="applyFilters">
                        <SelectTrigger class="w-full bg-[var(--sispaa-background)] sm:w-[180px]"><SelectValue placeholder="Período" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los períodos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
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
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_5%,transparent)]">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center">
                                        <div class="flex flex-col items-center gap-2 opacity-40 text-[var(--sispaa-text)]">
                                            <UserCog class="h-8 w-8" />
                                            <span class="text-sm font-medium">No hay asignaciones registradas</span>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                    <div class="flex flex-col gap-2 border-t px-4 py-4 border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ asignaciones.data.length }} de {{ asignaciones.total }} asignaciones</span>
                        <div class="flex flex-wrap items-center gap-1">
                            <button
                                v-for="link in asignaciones.links"
                                :key="link.label"
                                @click="navigateToPage(link.url)"
                                :disabled="!link.url || link.active"
                                v-html="link.label"
                                class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                                :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'bg-[var(--sispaa-background)] text-[var(--sispaa-text)] opacity-70 hover:opacity-100 disabled:opacity-40']"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
