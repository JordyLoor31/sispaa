import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { User } from 'lucide-vue-next';
import type { StudentRow } from './types';

const estadoBadge = (estado: string | null) => {
    const map: Record<string, string> = {
        activo: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        retirado: 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
        egresado: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400',
    };
    return map[estado ?? ''] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400';
};

export function makeMatriculadosColumns(): ColumnDef<StudentRow>[] {
    return [
        {
            id: 'name',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
                h(User, { class: 'h-4 w-4 text-indigo-500' }),
                h('div', {}, [
                    h('p', { class: 'font-semibold text-slate-900 dark:text-white' }, row.original.name),
                    h('p', { class: 'text-xs text-slate-400' }, row.original.email),
                ]),
            ]),
        },
        {
            accessorKey: 'cedula',
            meta: { label: 'Cédula' },
            header: 'Cédula',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.cedula ?? '—'),
        },
        {
            accessorKey: 'carrera_nombre',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.carrera_nombre ?? '—'),
        },
        {
            accessorKey: 'matricula_estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => h('span', {
                class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoBadge(row.original.matricula_estado)}`,
            }, row.original.matricula_estado ?? 'Sin matrícula'),
        },
        {
            accessorKey: 'faltas_count',
            meta: { label: 'Faltas' },
            header: 'Faltas',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, String(row.original.faltas_count)),
        },
        {
            accessorKey: 'documentos_count',
            meta: { label: 'Documentos' },
            header: 'Documentos',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, String(row.original.documentos_count)),
        },
    ];
}

export default makeMatriculadosColumns;
