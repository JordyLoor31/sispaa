import type { ColumnDef } from '@tanstack/vue-table';
import { Edit } from 'lucide-vue-next';
import { h } from 'vue';
import { Switch } from '@/components/ui/switch';

interface CarreraItem {
    id: number;
    nombre: string;
    codigo: string;
    coordinador_id: number | null;
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
    activa: boolean;
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
            cell: ({ row }) => h('span', { class: 'font-bold text-slate-800 dark:text-slate-350' }, row.original.codigo),
        },
        {
            accessorKey: 'nombre',
            header: 'Asignatura',
            cell: ({ row }) => h('span', { class: 'font-semibold text-slate-900 dark:text-white' }, row.original.nombre),
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
            cell: ({ row }) => h('span', { class: 'rounded bg-slate-105 dark:bg-slate-900 px-2 py-0.5 text-xs text-slate-700 dark:text-slate-400 font-semibold' }, row.original.carrera?.codigo || '-'),
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
                        class: `text-xs font-semibold ${isActive ? 'text-emerald-650 dark:text-emerald-400' : 'text-slate-400'}`,
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
                        class: 'text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 p-1.5 rounded-lg border border-slate-100 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900 transition-colors',
                    }, [
                        h(Edit, { class: 'h-4 w-4' }),
                    ]),
                ]);
            },
        },
    ];
}

export default makeMateriaColumns;