import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { Convocatoria } from './types';

const estadoBadge = (activa: boolean) =>
    activa
        ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400'
        : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500';

export function makeConvocatoriaColumns(): ColumnDef<Convocatoria>[] {
    return [
        {
            id: 'titulo',
            header: 'Título',
            cell: ({ row }) => {
                const c = row.original;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-100' }, c.titulo),
                    c.descripcion ? h('p', { class: 'text-xs text-slate-400 mt-0.5 max-w-xs truncate' }, c.descripcion) : null,
                ]);
            },
        },
        {
            id: 'modulo',
            header: 'Módulo',
            cell: ({ row }) => h('span', { class: 'text-xs text-slate-600 dark:text-slate-300' }, row.original.modulo),
        },
        {
            id: 'vigencia',
            header: 'Vigencia',
            cell: ({ row }) =>
                h('span', { class: 'text-xs text-slate-500 dark:text-slate-400' }, `${row.original.fecha_inicio} — ${row.original.fecha_fin}`),
        },
        {
            id: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const activa = row.original.activa;
                return h(
                    'span',
                    { class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoBadge(activa)}` },
                    activa ? 'Activa' : 'Inactiva',
                );
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right' }, 'Acciones'),
            cell: ({ row }) => {
                const c = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'la convocatoria',
                    showRoute: 'secretaria.convocatorias.show',
                    editRoute: 'secretaria.convocatorias.edit',
                    deleteRoute: 'secretaria.convocatorias.destroy',
                    routeParams: c.id,
                    itemLabel: `la convocatoria "${c.titulo}"`,
                });
            },
        },
    ];
}

export default makeConvocatoriaColumns;
