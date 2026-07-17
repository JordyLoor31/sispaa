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
                        { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/30' },
                        [h(FileText, { class: 'h-4 w-4 text-indigo-500' })],
                    ),
                    h(
                        'a',
                        {
                            href: p.ver_url,
                            target: '_blank',
                            rel: 'noopener',
                            class: 'inline-flex items-center gap-1 text-sm font-semibold text-slate-800 dark:text-slate-100 hover:text-indigo-600 dark:hover:text-indigo-400',
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
                    h('p', { class: 'text-sm text-slate-700 dark:text-slate-300' }, p.creado_por ?? '—'),
                    h('p', { class: 'text-xs text-slate-400' }, p.created_at ?? '—'),
                ]);
            },
        },
        {
            id: 'actualizado',
            header: 'Última actualización',
            cell: ({ row }) => {
                const p = row.original;
                return h('div', {}, [
                    h('p', { class: 'text-sm text-slate-700 dark:text-slate-300' }, p.actualizado_por ?? '—'),
                    h('p', { class: 'text-xs text-slate-400' }, p.updated_at ?? '—'),
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
