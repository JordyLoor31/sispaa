import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { CalendarX } from 'lucide-vue-next';
import type { FaltaRow } from './types';

const solicitudBadge = (estado: string | null) => {
    if (!estado) return null;
    const map: Record<string, string> = {
        pendiente: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        aprobada: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        rechazada: 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
    };
    const label: Record<string, string> = { pendiente: 'Pendiente', aprobada: 'Aprobada', rechazada: 'Rechazada' };
    return { class: map[estado] ?? '', label: label[estado] ?? estado };
};

export function makeFaltaColumns(): ColumnDef<FaltaRow>[] {
    return [
        {
            id: 'fecha',
            meta: { label: 'Fecha' },
            header: 'Fecha',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
                h(CalendarX, { class: 'h-4 w-4 text-rose-400' }),
                h('span', { class: 'font-semibold text-slate-900 dark:text-white' }, row.original.fecha ?? '—'),
            ]),
        },
        {
            id: 'estudiante',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => row.original.estudiante?.name ?? '—',
        },
        {
            accessorKey: 'carrera',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.carrera ?? '—'),
        },
        {
            accessorKey: 'materia',
            meta: { label: 'Materia' },
            header: 'Materia',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.materia ?? '—'),
        },
        {
            accessorKey: 'periodo',
            meta: { label: 'Período' },
            header: 'Período',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.periodo ?? '—'),
        },
        {
            id: 'justificada',
            meta: { label: 'Justificada' },
            header: 'Justificada',
            cell: ({ row }) => {
                const badge = solicitudBadge(row.original.solicitud_estado);
                if (!row.original.justificada) {
                    return h('span', { class: 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400' }, 'Sin justificar');
                }
                if (badge) {
                    return h('span', { class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${badge.class}` }, badge.label);
                }
                return h('span', { class: 'text-slate-400 text-xs' }, '—');
            },
        },
    ];
}

export default makeFaltaColumns;
