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

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Estudiantes Matriculados</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Lista de estudiantes matriculados en la institución.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 rounded-xl p-4 bg-[var(--sispaa-surface)]">
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Buscar</label>
                    <input v-model="q" @keyup.enter="aplicar" placeholder="Nombre, correo o cédula"
                        class="w-full sm:w-[240px] rounded-lg text-sm bg-[var(--sispaa-background)] text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_20%,transparent)]" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Carrera</label>
                    <Select v-model="carreraId" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[200px] bg-[var(--sispaa-background)]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas las carreras</SelectItem>
                            <SelectItem v-for="c in carreras" :key="c.id" :value="String(c.id)">{{ c.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Estado</label>
                    <Select v-model="estado" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[160px] bg-[var(--sispaa-background)]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="activo">Activo</SelectItem>
                            <SelectItem value="retirado">Retirado</SelectItem>
                            <SelectItem value="egresado">Egresado</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <div class="rounded-lg overflow-hidden bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                <div class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                <TableHead v-for="header in hg.headers" :key="header.id"
                                    class="h-12 px-4 text-sm font-medium opacity-60 text-[var(--sispaa-text)]">
                                    <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                    class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell :colspan="columns.length" class="h-32 text-center text-sm opacity-50 text-[var(--sispaa-text)]">
                                    No hay estudiantes para los filtros seleccionados.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex flex-col gap-3 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ students.data.length }} de {{ students.total }} registros</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button v-for="link in students.links" :key="link.label" @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] disabled:opacity-40']" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
