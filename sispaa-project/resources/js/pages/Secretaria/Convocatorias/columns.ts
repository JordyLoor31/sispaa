import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { Convocatoria } from './types';

const estadoBadge = (activa: boolean) =>
    activa
        ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]'
        : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]';

export function makeConvocatoriaColumns(): ColumnDef<Convocatoria>[] {
    return [
        {
            id: 'titulo',
            header: 'Título',
            cell: ({ row }) => {
                const c = row.original;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, c.titulo),
                    c.descripcion ? h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5 max-w-xs truncate' }, c.descripcion) : null,
                ]);
            },
        },
        {
            id: 'modulo',
            header: 'Módulo',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-70 text-[var(--sispaa-text)]' }, row.original.modulo),
        },
        {
            id: 'vigencia',
            header: 'Vigencia',
            cell: ({ row }) =>
                h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, `${row.original.fecha_inicio} — ${row.original.fecha_fin}`),
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
