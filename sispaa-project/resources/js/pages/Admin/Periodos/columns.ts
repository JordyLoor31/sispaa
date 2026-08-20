import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import { STATUS_COLORS, neutralBadgeStyle, tintedBadgeStyle } from '@/lib/brand';

export type EstadoPeriodo = 'planificado' | 'activo' | 'finalizado';

export interface Periodo {
    id: number;
    nombre: string;
    fecha_inicio: string;
    fecha_fin: string;
    tipo: 'semestral' | 'anual' | string;
    estado: EstadoPeriodo;
    fecha_limite_silabo: string | null;
    fecha_limite_informe: string | null;
    created_at?: string;
}

const ESTADO_BADGE: Record<EstadoPeriodo, string> = {
    planificado: tintedBadgeStyle(STATUS_COLORS.advertencia),
    activo: tintedBadgeStyle(STATUS_COLORS.exito),
    finalizado: neutralBadgeStyle(),
};

const ESTADO_LABEL: Record<EstadoPeriodo, string> = {
    planificado: 'Planificado',
    activo: 'Activo',
    finalizado: 'Finalizado',
};

const formatDateTime = (value: string | null | undefined): string => {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return date.toLocaleString('es-EC', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

export function makePeriodoColumns(): ColumnDef<Periodo>[] {
    return [
        {
            accessorKey: 'nombre',
            meta: { label: 'Periodo' },
            header: 'Periodo',
            cell: ({ row }) => h('span', { class: 'font-bold text-[var(--sispaa-text)]' }, row.original.nombre),
        },
        {
            accessorKey: 'tipo',
            meta: { label: 'Tipo' },
            header: 'Tipo',
            cell: ({ row }) => h('span', { class: 'capitalize opacity-80 text-[var(--sispaa-text)]' }, row.original.tipo),
        },
        {
            id: 'duracion',
            meta: { label: 'Duración' },
            header: 'Duración',
            cell: ({ row }) => h('span', { class: 'text-sm opacity-80 text-[var(--sispaa-text)]' }, `${formatDateTime(row.original.fecha_inicio)} — ${formatDateTime(row.original.fecha_fin)}`),
        },
        {
            accessorKey: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const periodo = row.original;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: 'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold',
                        style: ESTADO_BADGE[periodo.estado],
                    }, ESTADO_LABEL[periodo.estado]),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const periodo = row.original;

                return h(ResourceActionsDropdown, {
                    resourceName: 'el periodo',
                    showRoute: 'admin.periodos.show',
                    editRoute: 'admin.periodos.edit',
                    routeParams: periodo.id,
                    itemLabel: `el periodo "${periodo.nombre}"`,
                    canDelete: false,
                });
            },
        },
    ];
}

export default makePeriodoColumns;
