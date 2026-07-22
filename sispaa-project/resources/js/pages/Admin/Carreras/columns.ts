import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Switch } from '@/components/ui/switch';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';

export interface Coordinador {
    id: number;
    name: string;
}

export interface Carrera {
    id: number;
    nombre: string;
    codigo: string;
    color?: string | null;
    activa: boolean;
    coordinador_id: number | null;
    coordinador?: Coordinador | null;
    materias_count?: number;
    usuarios_count?: number;
    created_at?: string;
}

interface CarreraColumnsOptions {
    onToggleStatus: (carrera: Carrera) => void;
}

export function makeCarreraColumns({ onToggleStatus }: CarreraColumnsOptions): ColumnDef<Carrera>[] {
    return [
        {
            accessorKey: 'codigo',
            meta: { label: 'Código' },
            header: 'Código',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
                h('span', {
                    class: 'inline-block h-3 w-3 shrink-0 rounded-full border border-black/10',
                    style: { backgroundColor: row.original.color ?? '#94a3b8' },
                    title: row.original.color ?? 'Sin color asignado',
                }),
                h('span', { class: 'font-bold text-[var(--sispaa-text)]' }, row.original.codigo),
            ]),
        },
        {
            accessorKey: 'nombre',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => h('span', { class: 'font-semibold text-[var(--sispaa-text)]' }, row.original.nombre),
        },
        {
            id: 'coordinador',
            meta: { label: 'Coordinador' },
            header: 'Coordinador',
            cell: ({ row }) => row.original.coordinador?.name ?? 'No asignado',
        },
        {
            id: 'materias_count',
            meta: { label: 'Asignaturas' },
            header: () => h('div', { class: 'text-center' }, 'Asignaturas'),
            cell: ({ row }) => h('div', { class: 'text-center' }, row.original.materias_count ?? 0),
        },
        {
            id: 'usuarios_count',
            meta: { label: 'Usuarios' },
            header: () => h('div', { class: 'text-center' }, 'Usuarios'),
            cell: ({ row }) => h('div', { class: 'text-center' }, row.original.usuarios_count ?? 0),
        },
        {
            accessorKey: 'activa',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const carrera = row.original;
                const isActive = !!carrera.activa;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h(Switch, {
                        checked: isActive,
                        modelValue: isActive,
                        'onUpdate:checked': () => onToggleStatus(carrera),
                        'onUpdate:modelValue': () => onToggleStatus(carrera),
                    }),
                    h('span', {
                        class: `text-xs font-semibold ${isActive ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]' : 'opacity-50 text-[var(--sispaa-text)]'}`,
                    }, isActive ? 'Activa' : 'Inactiva'),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const carrera = row.original;

                return h(ResourceActionsDropdown, {
                    resourceName: 'la carrera',
                    showRoute: 'admin.carreras.show',
                    editRoute: 'admin.carreras.edit',
                    routeParams: carrera.id,
                    itemLabel: `la carrera "${carrera.nombre}"`,
                    canDelete: false, // no se elimina, se desactiva (ver columna Estado)
                });
            },
        },
    ];
}

export default makeCarreraColumns;
