<script setup lang="ts">
import AppSidebarLayout from '@/layouts/app/AppSidebarLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { usePermissions } from '@/composables/usePermissions';
import makeSolicitudColumns from './columns';
import type { Paginated, SolicitudRow } from './types';

const props = defineProps<{
    solicitudes: Paginated<SolicitudRow>;
    filters: { estado?: string };
    breadcrumbs?: BreadcrumbItemType[];
}>();

const { hasAnyRole } = usePermissions();

const estado = ref(props.filters.estado ?? 'all');

const aplicar = () => {
    router.get(route('estudiantes.justificaciones'), {
        estado: estado.value !== 'all' ? estado.value : undefined,
    }, { preserveState: true, replace: true });
};

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

        <div class="flex h-full flex-1 flex-col gap-6 p-6 bg-slate-50/50 dark:bg-slate-900/50">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">Justificaciones</h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Reporte de solicitudes de justificación de inasistencia.
                    <span v-if="hasAnyRole('secretaria', 'SystemAdministrador')">La aprobación/rechazo se hace desde Secretaría.</span>
                </p>
            </div>

            <div class="flex flex-wrap items-end gap-3 bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/80 dark:border-slate-800">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase mb-1.5">Estado</label>
                    <Select v-model="estado" @update:model-value="aplicar">
                        <SelectTrigger class="w-[180px]"><SelectValue /></SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Todos</SelectItem>
                            <SelectItem value="pendiente">Pendiente</SelectItem>
                            <SelectItem value="aprobada">Aprobada</SelectItem>
                            <SelectItem value="rechazada">Rechazada</SelectItem>
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
                                    No hay solicitudes para el filtro seleccionado.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800 px-6 py-4">
                    <span class="text-xs text-slate-500">Mostrando {{ solicitudes.data.length }} de {{ solicitudes.total }} registros</span>
                    <div class="flex items-center gap-1">
                        <button v-for="link in solicitudes.links" :key="link.label" @click="navigateToPage(link.url)"
                            :disabled="!link.url || link.active" v-html="link.label"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                            :class="[link.active ? 'bg-indigo-600 text-white' : 'border border-slate-200/80 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-300 disabled:opacity-40']" />
                    </div>
                </div>
            </div>
        </div>
    </AppSidebarLayout>
</template>
