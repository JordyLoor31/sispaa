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
import { usePermissions } from '@/composables/usePermissions';
import makeSolicitudColumns from './columns';
import type { Paginated, SolicitudRow } from './types';

const props = defineProps<{
    solicitudes: Paginated<SolicitudRow>;
    filters: { estado?: string; q?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const { hasAnyRole } = usePermissions();

const search = ref(props.filters.q ?? '');
const estado = ref(props.filters.estado ?? 'all');

const aplicar = () => {
    router.get(route('estudiantes.justificaciones'), {
        q: search.value || undefined,
        estado: estado.value !== 'all' ? estado.value : undefined,
    }, { preserveState: true, replace: true });
};
const debouncedAplicar = useDebounceFn(aplicar, 300);

const navigateToPage = (url: string | null) => {
    if (url) router.get(url, {}, { preserveState: true });
};

const columns = makeSolicitudColumns({ canReviewInSecretaria: hasAnyRole('secretaria', 'SystemAdministrador') });

const table = useVueTable(reactive({
    get data() { return props.solicitudes.data; },
    columns,
    getCoreRowModel: getCoreRowModel(),
}));
</script>

<template>
    <AppSidebarLayout :breadcrumbs="breadcrumbs">
        <Head title="Justificaciones" />

        <div class="flex h-full flex-1 flex-col gap-4 p-4 sm:gap-6 sm:p-6 bg-[var(--sispaa-background)]">
            <div>
                <h1 class="text-xl font-bold tracking-tight text-[var(--sispaa-text)] sm:text-2xl">Justificaciones</h1>
                <p class="mt-1 text-sm opacity-60 text-[var(--sispaa-text)]">
                    Reporte de solicitudes de justificación de inasistencia.
                    <span v-if="hasAnyRole('secretaria', 'SystemAdministrador')">La aprobación/rechazo se hace desde Secretaría.</span>
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-3">
                <div class="w-full max-w-sm">
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Buscar</label>
                    <div class="relative">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-50 text-[var(--sispaa-text)]" />
                        <Input v-model="search" type="text" placeholder="Buscar estudiante, materia o motivo..."
                            class="pl-9" @input="debouncedAplicar" />
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-semibold uppercase opacity-60 text-[var(--sispaa-text)]">Estado</label>
                    <Select v-model="estado" @update:model-value="aplicar">
                        <SelectTrigger class="w-full sm:w-[180px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="pendiente">Pendiente</SelectItem>
                            <SelectItem value="aprobada">Aprobada</SelectItem>
                            <SelectItem value="rechazada">Rechazada</SelectItem>
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
                                    No hay solicitudes para el filtro seleccionado.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex flex-col gap-3 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6 border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)]">
                    <span class="text-xs opacity-60 text-[var(--sispaa-text)]">Mostrando {{ solicitudes.data.length }} de {{ solicitudes.total }} registros</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <button v-for="link in solicitudes.links" :key="link.label" @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active" v-html="link.label"
                            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition-all"
                            :class="[link.active ? 'bg-[var(--sispaa-primary)] text-white' : 'text-[var(--sispaa-text)] bg-[var(--sispaa-background)] border border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_10%,transparent)] disabled:opacity-40']" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
