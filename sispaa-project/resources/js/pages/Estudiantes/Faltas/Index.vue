<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-vue-next';
import { useDebounceFn } from '@vueuse/core';
import makeFaltaColumns from './columns';
import type { Catalogo, FaltaRow, Paginated } from './types';

const props = defineProps<{
    faltas: Paginated<FaltaRow>;
    carreras: Catalogo[];
    periodos: Catalogo[];
    filters: { carrera_id?: string; periodo_id?: string; justificada?: string; q?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const search = ref(props.filters.q ?? '');
const carreraId = ref(props.filters.carrera_id ?? 'all');
const periodoId = ref(props.filters.periodo_id ?? 'all');
const justificada = ref(props.filters.justificada ?? 'all');

const aplicar = () => {
    router.get(route('estudiantes.faltas'), {
        q: search.value || undefined,
        carrera_id: carreraId.value !== 'all' ? carreraId.value : undefined,
        periodo_id: periodoId.value !== 'all' ? periodoId.value : undefined,
        justificada: justificada.value !== 'all' ? justificada.value : undefined,
    }, { preserveState: true, replace: true });
};
const debouncedAplicar = useDebounceFn(aplicar, 300);

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};

const columns = makeFaltaColumns();

const table = useVueTable(reactive({
    get data() { return props.faltas.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Faltas" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Faltas</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Reporte institucional de inasistencias de estudiantes.</p>
            </div>

            <div class="flex flex-wrap items-end gap-3 rounded-xl p-4 bg-[var(--sispaa-surface)]">
                <div class="min-w-[220px] flex-1">
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Buscar</label>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-50 text-[var(--sispaa-text)]" />
                        <Input v-model="search" type="text" placeholder="Buscar estudiante, materia o motivo..."
                            class="bg-[var(--sispaa-background)] pl-9" @input="debouncedAplicar" />
                    </div>
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
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Período</label>
                    <Select v-model="periodoId" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[180px] bg-[var(--sispaa-background)]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem v-for="p in periodos" :key="p.id" :value="String(p.id)">{{ p.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Justificación</label>
                    <Select v-model="justificada" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[180px] bg-[var(--sispaa-background)]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todas</SelectItem>
                            <SelectItem value="1">Justificadas</SelectItem>
                            <SelectItem value="0">Sin justificar</SelectItem>
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
                                    No hay faltas registradas para los filtros seleccionados.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex flex-col gap-3 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ faltas.data.length }} de {{ faltas.total }} registros</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button v-for="link in faltas.links" :key="link.label" @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] disabled:opacity-40']" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
