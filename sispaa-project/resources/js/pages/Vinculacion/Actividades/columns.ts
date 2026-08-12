import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Handshake } from 'lucide-vue-next';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import { type Actividad, type EstadoActividad, ESTADO_LABELS } from './types';

const estadoBadge = (estado: EstadoActividad) => {
    if (estado === 'ejecutado') return 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]';
    if (estado === 'cancelado') return 'bg-rose-500/15 text-rose-600';
    return 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]';
};

const beneficiarioNombre = (a: Actividad) => (typeof a.beneficiario === 'string' ? a.beneficiario : a.beneficiario?.nombre ?? null);

export function makeActividadColumns(): ColumnDef<Actividad>[] {
    return [
        {
            accessorKey: 'nombre',
            meta: { label: 'Actividad' },
            header: 'Actividad',
            cell: ({ row }) => {
                const a = row.original;
                return h('div', { class: 'flex min-w-0 items-center gap-2.5' }, [
                    h('div', { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]' }, [
                        h(Handshake, { class: 'h-4 w-4' }),
                    ]),
                    h('div', { class: 'min-w-0' }, [
                        h('p', { class: 'truncate text-sm font-bold text-[var(--sispaa-text)]' }, a.nombre),
                        h('p', { class: 'truncate text-xs opacity-60 text-[var(--sispaa-text)]' }, `${a.carrera ?? '—'} · ${a.periodo ?? '—'}`),
                    ]),
                ]);
            },
        },
        {
            id: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const a = row.original;
                return h('span', { class: ['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', estadoBadge(a.estado)] }, ESTADO_LABELS[a.estado]);
            },
        },
        {
            id: 'docente_lider',
            meta: { label: 'Líder' },
            header: 'Líder',
            cell: ({ row }) => h('span', { class: 'text-sm text-[var(--sispaa-text)]' }, row.original.docente_lider?.name ?? '—'),
        },
        {
            id: 'beneficiario',
            meta: { label: 'Beneficiario' },
            header: 'Beneficiario',
            cell: ({ row }) => {
                const nombre = beneficiarioNombre(row.original);
                return h('span', { class: 'text-sm opacity-70 text-[var(--sispaa-text)]' }, nombre ?? '—');
            },
        },
        {
            id: 'total_beneficiarios',
            meta: { label: 'Beneficiarios' },
            header: 'Beneficiarios',
            cell: ({ row }) => h('span', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, String(row.original.total_beneficiarios ?? 0)),
        },
        {
            id: 'fecha_inicio',
            meta: { label: 'Inicio' },
            header: 'Inicio',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.fecha_inicio ?? '—'),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const a = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'la actividad',
                    showRoute: 'vinculacion.actividades.show',
                    editRoute: 'vinculacion.actividades.edit',
                    deleteRoute: 'vinculacion.actividades.destroy',
                    routeParams: a.id,
                    itemLabel: `la actividad "${a.nombre}"`,
                });
            },
        },
    ];
}

export default makeActividadColumns;
