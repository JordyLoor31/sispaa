import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { ChevronDown } from 'lucide-vue-next';

export interface MatriculaRow {
    id: number;
    estado: 'activo' | 'retirado' | 'egresado';
    fecha_matricula: string;
    created_at: string;
    estudiante: { id: number; name: string; email: string; cedula: string | null };
    periodo: { id: number; nombre: string };
    carrera: { id: number; nombre: string; codigo: string };
}

const estadoClasses: Record<string, string> = {
    activo:   'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    retirado: 'bg-rose-50 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400',
    egresado: 'bg-blue-50 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400',
};

interface MatriculaColumnsOptions {
    onChangeEstado: (item: MatriculaRow) => void;
}

export function makeMatriculaColumns({ onChangeEstado }: MatriculaColumnsOptions): ColumnDef<MatriculaRow>[] {
    return [
        {
            id: 'estudiante',
            header: 'Estudiante',
            cell: ({ row }) => {
                const e = row.original.estudiante;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-100' }, e.name),
                    h('p', { class: 'text-xs text-slate-400' }, e.cedula ?? e.email),
                ]);
            },
        },
        {
            id: 'carrera',
            header: 'Carrera',
            cell: ({ row }) => {
                const c = row.original.carrera;
                return h('div', {}, [
                    h('p', { class: 'text-xs font-semibold text-slate-600 dark:text-slate-300' }, c.codigo),
                    h('p', { class: 'text-xs text-slate-400 mt-0.5 max-w-[150px] truncate', title: c.nombre }, c.nombre),
                ]);
            },
        },
        {
            id: 'periodo',
            header: 'Período Académico',
            cell: ({ row }) =>
                h('span', { class: 'text-xs text-slate-600 dark:text-slate-300' }, row.original.periodo.nombre),
        },
        {
            id: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const estado = row.original.estado;
                const label = estado.charAt(0).toUpperCase() + estado.slice(1);
                return h('span', {
                    class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoClasses[estado] ?? 'bg-slate-100 text-slate-500'}`,
                }, label);
            },
        },
        {
            accessorKey: 'fecha_matricula',
            header: 'F. Matrícula',
            cell: ({ row }) =>
                h('span', { class: 'text-xs text-slate-500' }, row.original.fecha_matricula),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right text-xs font-semibold text-slate-500 uppercase' }, 'Acciones'),
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex justify-end' }, [
                    h('button', {
                        onClick: () => onChangeEstado(item),
                        class: 'inline-flex items-center gap-1 text-xs text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200 dark:border-slate-700 hover:border-indigo-300 rounded-lg px-2.5 py-1.5 transition-colors',
                    }, [
                        h(ChevronDown, { class: 'h-3.5 w-3.5' }),
                        'Estado',
                    ]),
                ]);
            },
        },
    ];
}

export default makeMatriculaColumns;
