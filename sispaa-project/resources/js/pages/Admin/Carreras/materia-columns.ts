import type { ColumnDef } from '@tanstack/vue-table';
import { Edit, Trash2 } from 'lucide-vue-next';
import { h } from 'vue';

interface CarreraItem {
    id: number;
    nombre: string;
    codigo: string;
    coordinador_id: number | null;
    coordinador?: { id: number; name: string } | null;
}

interface MateriaItem {
    id: number;
    nombre: string;
    codigo: string;
    creditos: number;
    nivel: number;
    carrera_id: number;
    carrera?: CarreraItem;
}

interface MateriaColumnsOptions {
    onEditMateria: (materia: MateriaItem) => void;
    onDeleteMateria: (materia: MateriaItem) => void;
}

export function makeMateriaColumns({ onEditMateria, onDeleteMateria }: MateriaColumnsOptions): ColumnDef<MateriaItem>[] {
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
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            cell: ({ row }) => {
                const materia = row.original;

                return h('div', { class: 'text-right space-x-1.5' }, [
                    h('button', {
                        onClick: () => onEditMateria(materia),
                        class: 'text-slate-500 hover:text-indigo-600 p-1',
                    }, [
                        h(Edit, { class: 'h-4 w-4' }),
                    ]),
                    h('button', {
                        onClick: () => onDeleteMateria(materia),
                        class: 'text-slate-400 hover:text-rose-500 p-1',
                    }, [
                        h(Trash2, { class: 'h-4 w-4' }),
                    ]),
                ]);
            },
        },
    ];
}

export default makeMateriaColumns;