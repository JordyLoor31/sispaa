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
import makeEquipoColumns from './columns';
import type { Catalogo, EquipoItem } from './types';

const props = defineProps<{
    equipos: EquipoItem[];
    laboratorios: Catalogo[];
    filters: { laboratorio_id?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const filterLab = ref(props.filters.laboratorio_id || 'all');
const applyFilter = () => {
    router.get(route('laboratorio.equipos'), { laboratorio_id: filterLab.value !== 'all' ? filterLab.value : undefined }, { preserveState: true, replace: true });
};

const changeEstado = (e: EquipoItem, estado: string) => {
    router.put(route('laboratorio.equipos.update', e.id), { estado }, {
        preserveScroll: true,
        onSuccess: () => toast.success('Estado actualizado.'),
    });
};

const columns = makeEquipoColumns({ onChangeEstado: changeEstado });

const table = useVueTable(reactive({
    get data() { return props.equipos; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Equipos de Laboratorio" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Equipos</h1>
                    <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">Inventario de equipos de laboratorio.</p>
                </div>
                <Button as-child class="inline-flex items-center gap-1.5 font-semibold text-white bg-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]">
                    <Link :href="route('laboratorio.equipos.create')">
                        <Plus class="h-4 w-4" /> Nuevo Equipo
                    </Link>
                </Button>
            </div>

            <div class="w-full space-y-4">
                <div class="flex flex-wrap gap-3">
                    <Select v-model="filterLab" @update:model-value="applyFilter">
                        <SelectTrigger class="w-full sm:w-[220px]"><SelectValue placeholder="Laboratorio" /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos los laboratorios</SelectItem>
                            <SelectItem v-for="l in laboratorios" :key="l.id" :value="String(l.id)">{{ l.nombre }}</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="rounded-lg overflow-hidden bg-[var(--sispaa-surface)]">
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id"
                                    class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                                    <TableHead v-for="header in hg.headers" :key="header.id"
                                        class="h-12 px-4 text-sm font-medium opacity-70 text-[var(--sispaa-text)]">
                                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm">
                                <template v-if="table.getRowModel().rows?.length">
                                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                        class="hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_4%,transparent)] transition-colors">
                                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-4">
                                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow v-else>
                                    <TableCell :colspan="columns.length" class="h-32 text-center text-sm opacity-50 text-[var(--sispaa-text)]">
                                        No hay equipos registrados.
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
