import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { FlaskConical } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { ReactivoItem } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        disponible: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        agotado: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        vencido: 'bg-rose-50 text-rose-600',
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
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-[var(--sispaa-text)]' }, [
                h(FlaskConical, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' }),
                row.original.nombre,
            ]),
        },
        {
            accessorKey: 'formula',
            meta: { label: 'Fórmula' },
            header: 'Fórmula',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.formula ?? '—'),
        },
        {
            accessorKey: 'laboratorio',
            meta: { label: 'Laboratorio' },
            header: 'Laboratorio',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.laboratorio),
        },
        {
            id: 'cantidad',
            meta: { label: 'Cantidad' },
            header: 'Cantidad',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, `${row.original.cantidad}${row.original.unidad ? ' ' + row.original.unidad : ''}`),
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
