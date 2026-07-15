import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';

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
    planificado: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
    activo: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
    finalizado: 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
};

const ESTADO_LABEL: Record<EstadoPeriodo, string> = {
    planificado: 'Planificado',
    activo: 'Activo',
    finalizado: 'Finalizado',
};

interface PeriodoColumnsOptions {
    onActivar: (periodo: Periodo) => void;
    onFinalizar: (periodo: Periodo) => void;
}

export function makePeriodoColumns({ onActivar, onFinalizar }: PeriodoColumnsOptions): ColumnDef<Periodo>[] {
    return [
        {
            accessorKey: 'nombre',
            meta: { label: 'Periodo' },
            header: 'Periodo',
            cell: ({ row }) => h('span', { class: 'font-bold text-slate-900 dark:text-white' }, row.original.nombre),
        },
        {
            accessorKey: 'tipo',
            meta: { label: 'Tipo' },
            header: 'Tipo',
            cell: ({ row }) => h('span', { class: 'capitalize text-slate-600 dark:text-slate-300' }, row.original.tipo),
        },
        {
            id: 'duracion',
            meta: { label: 'Duración' },
            header: 'Duración',
            cell: ({ row }) => h('span', { class: 'text-sm text-slate-600 dark:text-slate-300' }, `${row.original.fecha_inicio} — ${row.original.fecha_fin}`),
        },
        {
            accessorKey: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const periodo = row.original;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h('span', {
                        class: `inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ${ESTADO_BADGE[periodo.estado]}`,
                    }, ESTADO_LABEL[periodo.estado]),
                    periodo.estado === 'planificado'
                        ? h(Button, { size: 'sm', variant: 'outline', class: 'h-7 px-2 text-xs', onClick: () => onActivar(periodo) }, () => 'Activar')
                        : null,
                    periodo.estado === 'activo'
                        ? h(Button, { size: 'sm', variant: 'outline', class: 'h-7 px-2 text-xs text-rose-500 hover:text-rose-600', onClick: () => onFinalizar(periodo) }, () => 'Finalizar')
                        : null,
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
