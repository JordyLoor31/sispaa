import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { FaltaSemanalItem } from './types';

const estadoPeriodoBadge = (estado: string) => {
    const map: Record<string, string> = {
        activo: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]',
        planificado: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]',
        finalizado: 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]',
    };
    return map[estado] ?? map.planificado;
};

export function makeFaltaSemanalColumns(): ColumnDef<FaltaSemanalItem>[] {
    return [
        {
            id: 'carrera',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, row.original.carrera.nombre),
        },
        {
            id: 'periodo',
            meta: { label: 'Período' },
            header: 'Período',
            cell: ({ row }) => {
                const p = row.original.periodo;
                return h('span', { class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoPeriodoBadge(p.estado)}` }, p.nombre);
            },
        },
        {
            accessorKey: 'semana',
            meta: { label: 'Semana' },
            header: 'Semana',
            cell: ({ row }) => h('span', { class: 'text-sm text-[var(--sispaa-text)]' }, `Semana ${row.original.semana}`),
        },
        {
            accessorKey: 'cantidad_faltas',
            meta: { label: 'Cantidad de Faltas' },
            header: 'Cantidad de Faltas',
            cell: ({ row }) => h('span', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, String(row.original.cantidad_faltas)),
        },
        {
            accessorKey: 'observaciones',
            meta: { label: 'Observaciones' },
            header: 'Observaciones',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.observaciones ?? '—'),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const f = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'el registro',
                    editRoute: 'secretaria.faltas-semanales.edit',
                    deleteRoute: 'secretaria.faltas-semanales.destroy',
                    routeParams: f.id,
                    itemLabel: `las faltas de "${f.carrera.nombre}" (semana ${f.semana})`,
                });
            },
        },
    ];
}

export default makeFaltaSemanalColumns;
