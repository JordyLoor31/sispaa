import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { CalendarX } from 'lucide-vue-next';
import type { FaltaRow } from './types';

const solicitudBadge = (estado: string | null) => {
    if (!estado) return null;
    const map: Record<string, string> = {
        pendiente: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        aprobada: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        rechazada: 'bg-rose-50 text-rose-600',
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
                h('span', { class: 'font-semibold text-[var(--sispaa-text)]' }, row.original.fecha ?? '—'),
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
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.carrera ?? '—'),
        },
        {
            accessorKey: 'materia',
            meta: { label: 'Materia' },
            header: 'Materia',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.materia ?? '—'),
        },
        {
            accessorKey: 'periodo',
            meta: { label: 'Período' },
            header: 'Período',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.periodo ?? '—'),
        },
        {
            id: 'justificada',
            meta: { label: 'Justificada' },
            header: 'Justificada',
            cell: ({ row }) => {
                const badge = solicitudBadge(row.original.solicitud_estado);
                if (!row.original.justificada) {
                    return h('span', { class: 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]' }, 'Sin justificar');
                }
                if (badge) {
                    return h('span', { class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${badge.class}` }, badge.label);
                }
                return h('span', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, '—');
            },
        },
    ];
}

export default makeFaltaColumns;
