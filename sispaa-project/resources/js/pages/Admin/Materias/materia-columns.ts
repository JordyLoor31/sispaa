import type { ColumnDef } from '@tanstack/vue-table';
import { Edit } from 'lucide-vue-next';
import { h } from 'vue';
import { Switch } from '@/components/ui/switch';

interface CarreraItem {
    id: number;
    nombre: string;
    codigo?: string;
    coordinador_id?: number | null;
    coordinador?: { id: number; name: string } | null;
    activa?: boolean;
}

interface MateriaItem {
    id: number;
    nombre: string;
    codigo: string;
    creditos: number;
    nivel: number;
    carrera_id: number;
    activa?: boolean;
    carrera?: CarreraItem;
}

interface MateriaColumnsOptions {
    onEditMateria: (materia: MateriaItem) => void;
    onToggleStatus: (materia: MateriaItem) => void;
}

export function makeMateriaColumns({ onEditMateria, onToggleStatus }: MateriaColumnsOptions): ColumnDef<MateriaItem>[] {
    return [
        {
            accessorKey: 'codigo',
            header: 'Código',
            cell: ({ row }) => h('span', { class: 'font-bold text-[var(--sispaa-text)]' }, row.original.codigo),
        },
        {
            accessorKey: 'nombre',
            header: 'Asignatura',
            cell: ({ row }) => h('span', { class: 'font-semibold text-[var(--sispaa-text)]' }, row.original.nombre),
        },
        {
            accessorKey: 'nivel',
            header: 'Nivel / Semestre',
            cell: ({ row }) => `${row.original.nivel}º Semestre`,
        },
        {
            accessorKey: 'creditos',
            header: 'Créditos',
            cell: ({ row }) => `${row.original.creditos} crt`,
        },
        {
            id: 'carrera',
            header: 'Carrera',
            cell: ({ row }) => h('span', { class: 'rounded px-2 py-0.5 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[var(--sispaa-text)]' }, row.original.carrera?.codigo || '-'),
        },
        {
            accessorKey: 'activa',
            header: 'Estado',
            cell: ({ row }) => {
                const materia = row.original;
                const isActive = !!materia.activa;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h(Switch, {
                        checked: isActive,
                        modelValue: isActive,
                        'onUpdate:checked': () => onToggleStatus(materia),
                        'onUpdate:modelValue': () => onToggleStatus(materia),
                    }),
                    h('span', {
                        class: `text-xs font-semibold ${isActive ? 'text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]' : 'opacity-50 text-[var(--sispaa-text)]'}`,
                    }, isActive ? 'Activo' : 'Inactivo'),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            cell: ({ row }) => {
                const materia = row.original;

                return h('div', { class: 'text-right space-x-1.5' }, [
                    h('button', {
                        onClick: () => onEditMateria(materia),
                        class: 'p-1.5 rounded-lg border transition-colors opacity-70 text-[var(--sispaa-text)] border-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] hover:opacity-100 hover:text-[var(--sispaa-primary)] hover:bg-[color:color-mix(in_srgb,var(--sispaa-text)_6%,transparent)]',
                    }, [
                        h(Edit, { class: 'h-4 w-4' }),
                    ]),
                ]);
            },
        },
    ];
}

export default makeMateriaColumns;
