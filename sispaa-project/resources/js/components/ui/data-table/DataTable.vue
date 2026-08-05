<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef } from '@tanstack/vue-table';
import { FlexRender, getCoreRowModel, useVueTable } from '@tanstack/vue-table';
import { type HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    class?: HTMLAttributes['class'];
}>();

const table = useVueTable({
    get data() { return props.data; },
    get columns() { return props.columns; },
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <div :class="cn('overflow-hidden rounded-2xl border shadow-sm bg-[var(--sispaa-background)] border-[color:color-mix(in_srgb,var(--sispaa-text)_12%,transparent)]', props.class)">
        <div class="overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow v-for="hg in table.getHeaderGroups()" :key="hg.id" class="border-b border-[color:color-mix(in_srgb,var(--sispaa-text)_15%,transparent)]">
                        <TableHead v-for="header in hg.headers" :key="header.id" class="h-9 px-3 text-xs font-semibold uppercase tracking-wider opacity-60 text-[var(--sispaa-text)]">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody class="divide-y divide-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-sm text-[var(--sispaa-text)]">
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow v-for="row in table.getRowModel().rows" :key="row.id" class="transition-colors hover:bg-[color:color-mix(in_srgb,var(--sispaa-primary)_5%,transparent)]">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-3 py-2">
                                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                            </TableCell>
                        </TableRow>
                    </template>
                    <TableRow v-else>
                        <TableCell :colspan="columns.length" class="h-40">
                            <div class="flex flex-col items-center justify-center gap-2 text-center">
                                <slot name="empty">
                                    <p class="text-sm font-medium opacity-70 text-[var(--sispaa-text)]">No hay resultados.</p>
                                </slot>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
