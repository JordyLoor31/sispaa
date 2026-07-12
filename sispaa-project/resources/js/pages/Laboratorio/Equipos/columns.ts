import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Microscope } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { EquipoItem } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        operativo: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        mantenimiento: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        'dañado': 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
    };
    return map[estado] ?? map.operativo;
};

interface EquipoColumnsOptions {
    onChangeEstado: (equipo: EquipoItem, estado: string) => void;
}

export function makeEquipoColumns({ onChangeEstado }: EquipoColumnsOptions): ColumnDef<EquipoItem>[] {
    return [
        {
            id: 'nombre',
            meta: { label: 'Equipo' },
            header: 'Equipo',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-slate-900 dark:text-white' }, [
                h(Microscope, { class: 'h-4 w-4 text-indigo-500' }),
                row.original.nombre,
            ]),
        },
        {
            accessorKey: 'codigo',
            meta: { label: 'Código' },
            header: 'Código',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.codigo),
        },
        {
            accessorKey: 'laboratorio',
            meta: { label: 'Laboratorio' },
            header: 'Laboratorio',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.laboratorio),
        },
        {
            accessorKey: 'cantidad',
            meta: { label: 'Cantidad' },
            header: 'Cantidad',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, String(row.original.cantidad)),
        },
        {
            accessorKey: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const e = row.original;
                return h(Select, {
                    modelValue: e.estado,
                    'onUpdate:modelValue': (val: string) => onChangeEstado(e, val),
                }, () => [
                    h(SelectTrigger, { class: `h-7 w-[140px] text-xs border-none ${estadoBadge(e.estado)}` }, () => h(SelectValue)),
                    h(SelectContent, {}, () => [
                        h(SelectItem, { value: 'operativo' }, () => 'Operativo'),
                        h(SelectItem, { value: 'mantenimiento' }, () => 'Mantenimiento'),
                        h(SelectItem, { value: 'dañado' }, () => 'Dañado'),
                    ]),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const e = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'el equipo',
                    showRoute: 'laboratorio.equipos.show',
                    editRoute: 'laboratorio.equipos.edit',
                    deleteRoute: 'laboratorio.equipos.destroy',
                    routeParams: e.id,
                    itemLabel: `"${e.nombre}"`,
                });
            },
        },
    ];
}

export default makeEquipoColumns;
