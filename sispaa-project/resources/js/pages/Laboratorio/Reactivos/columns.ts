import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { FlaskConical } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { ReactivoItem } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        disponible: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        agotado: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        vencido: 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
    };
    return map[estado] ?? map.disponible;
};

interface ReactivoColumnsOptions {
    onChangeEstado: (reactivo: ReactivoItem, estado: string) => void;
}

export function makeReactivoColumns({ onChangeEstado }: ReactivoColumnsOptions): ColumnDef<ReactivoItem>[] {
    return [
        {
            id: 'nombre',
            meta: { label: 'Reactivo' },
            header: 'Reactivo',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-slate-900 dark:text-white' }, [
                h(FlaskConical, { class: 'h-4 w-4 text-indigo-500' }),
                row.original.nombre,
            ]),
        },
        {
            accessorKey: 'formula',
            meta: { label: 'Fórmula' },
            header: 'Fórmula',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.formula ?? '—'),
        },
        {
            accessorKey: 'laboratorio',
            meta: { label: 'Laboratorio' },
            header: 'Laboratorio',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.laboratorio),
        },
        {
            id: 'cantidad',
            meta: { label: 'Cantidad' },
            header: 'Cantidad',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, `${row.original.cantidad}${row.original.unidad ? ' ' + row.original.unidad : ''}`),
        },
        {
            accessorKey: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const r = row.original;
                return h(Select, {
                    modelValue: r.estado,
                    'onUpdate:modelValue': (val: string) => onChangeEstado(r, val),
                }, () => [
                    h(SelectTrigger, { class: `h-7 w-[130px] text-xs border-none ${estadoBadge(r.estado)}` }, () => h(SelectValue)),
                    h(SelectContent, {}, () => [
                        h(SelectItem, { value: 'disponible' }, () => 'Disponible'),
                        h(SelectItem, { value: 'agotado' }, () => 'Agotado'),
                        h(SelectItem, { value: 'vencido' }, () => 'Vencido'),
                    ]),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const r = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'el reactivo',
                    showRoute: 'laboratorio.reactivos.show',
                    editRoute: 'laboratorio.reactivos.edit',
                    deleteRoute: 'laboratorio.reactivos.destroy',
                    routeParams: r.id,
                    itemLabel: `"${r.nombre}"`,
                });
            },
        },
    ];
}

export default makeReactivoColumns;
