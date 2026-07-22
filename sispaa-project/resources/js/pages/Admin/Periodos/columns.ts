import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
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

// Estilos inline theme-aware (ver @/lib/brand): antes mezclaban con negro
// fijo y en tema oscuro quedaban ilegibles.
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

interface PeriodoColumnsOptions {
    onActivar: (periodo: Periodo) => void;
}

export function makePeriodoColumns({ onActivar }: PeriodoColumnsOptions): ColumnDef<Periodo>[] {
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
            cell: ({ row }) => h('span', { class: 'text-sm opacity-80 text-[var(--sispaa-text)]' }, `${row.original.fecha_inicio} — ${row.original.fecha_fin}`),
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
                    periodo.estado === 'planificado'
                        ? h(Button, { size: 'sm', variant: 'outline', class: 'h-7 px-2 text-xs', onClick: () => onActivar(periodo) }, () => 'Activar')
                        : null,
                    // 'Finalizar' se movió a la vista de Edición del periodo
                    // (no vive más en la tabla, a pedido).
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
                    canDelete: false, // no se elimina, se finaliza (ver columna Estado)
                });
            },
        },
    ];
}

export default makePeriodoColumns;
