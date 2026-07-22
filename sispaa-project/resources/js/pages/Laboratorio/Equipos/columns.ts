import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Microscope } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { EquipoItem } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        operativo: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        mantenimiento: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        'dañado': 'bg-rose-50 text-rose-600',
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
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-[var(--sispaa-text)]' }, [
                h(Microscope, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' }),
                row.original.nombre,
            ]),
        },
        {
            accessorKey: 'codigo',
            meta: { label: 'Código' },
            header: 'Código',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.codigo),
        },
        {
            accessorKey: 'laboratorio',
            meta: { label: 'Laboratorio' },
            header: 'Laboratorio',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.laboratorio),
        },
        {
            accessorKey: 'cantidad',
            meta: { label: 'Cantidad' },
            header: 'Cantidad',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, String(row.original.cantidad)),
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
