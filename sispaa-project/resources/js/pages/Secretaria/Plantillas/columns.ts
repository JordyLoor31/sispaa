import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { FileText, ArrowUpRight } from 'lucide-vue-next';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { PlantillaItem } from './types';

export function makePlantillaColumns(): ColumnDef<PlantillaItem>[] {
    return [
        {
            id: 'nombre_doc',
            header: 'Documento',
            cell: ({ row }) => {
                const p = row.original;
                return h('div', { class: 'flex items-center gap-2.5' }, [
                    h(
                        'div',
                        { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]' },
                        [h(FileText, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' })],
                    ),
                    h(
                        'a',
                        {
                            href: p.ver_url,
                            target: '_blank',
                            rel: 'noopener',
                            class: 'inline-flex items-center gap-1 text-sm font-semibold text-[var(--sispaa-text)] hover:text-[var(--sispaa-primary)]',
                        },
                        [p.nombre_doc, h(ArrowUpRight, { class: 'h-3.5 w-3.5' })],
                    ),
                ]);
            },
        },
        {
            id: 'creado',
            header: 'Creado por',
            cell: ({ row }) => {
                const p = row.original;
                return h('div', {}, [
                    h('p', { class: 'text-sm opacity-80 text-[var(--sispaa-text)]' }, p.creado_por ?? '—'),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, p.created_at ?? '—'),
                ]);
            },
        },
        {
            id: 'actualizado',
            header: 'Última actualización',
            cell: ({ row }) => {
                const p = row.original;
                return h('div', {}, [
                    h('p', { class: 'text-sm opacity-80 text-[var(--sispaa-text)]' }, p.actualizado_por ?? '—'),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, p.updated_at ?? '—'),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right' }, 'Acciones'),
            cell: ({ row }) =>
                h('div', { class: 'flex justify-end' }, [
                    h(ResourceActionsDropdown, {
                        resourceName: 'la plantilla',
                        editRoute: 'secretaria.plantillas.edit',
                        deleteRoute: 'secretaria.plantillas.destroy',
                        routeParams: row.original.id,
                        itemLabel: `la plantilla "${row.original.nombre_doc}"`,
                    }),
                ]),
        },
    ];
}

export default makePlantillaColumns;
