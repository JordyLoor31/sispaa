import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, FileText } from 'lucide-vue-next';
import type { SolicitudRow } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        pendiente: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        aprobada: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        rechazada: 'bg-rose-50 text-rose-600 dark:bg-rose-950/20 dark:text-rose-400',
    };
    return map[estado] ?? 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-400';
};

interface SolicitudColumnsOptions {
    canReviewInSecretaria: boolean;
}

export function makeSolicitudColumns({ canReviewInSecretaria }: SolicitudColumnsOptions): ColumnDef<SolicitudRow>[] {
    const columns: ColumnDef<SolicitudRow>[] = [
        {
            id: 'estudiante',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-slate-900 dark:text-white' }, [
                h(FileText, { class: 'h-4 w-4 text-indigo-500' }),
                row.original.estudiante ?? '—',
            ]),
        },
        {
            accessorKey: 'materia',
            meta: { label: 'Materia' },
            header: 'Materia',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.materia ?? '—'),
        },
        {
            accessorKey: 'fecha_falta',
            meta: { label: 'Falta del' },
            header: 'Falta del',
            cell: ({ row }) => h('span', { class: 'text-slate-500' }, row.original.fecha_falta ?? '—'),
        },
        {
            accessorKey: 'motivo_estudiante',
            meta: { label: 'Motivo' },
            header: 'Motivo',
            cell: ({ row }) => h('span', { class: 'text-slate-600 dark:text-slate-300 max-w-xs truncate block' }, row.original.motivo_estudiante),
        },
        {
            accessorKey: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => h('span', {
                class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoBadge(row.original.estado)}`,
            }, row.original.estado.charAt(0).toUpperCase() + row.original.estado.slice(1)),
        },
    ];

    if (canReviewInSecretaria) {
        columns.push({
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => h('div', { class: 'text-right' }, [
                h(Link, {
                    href: route('secretaria.justificaciones.show', row.original.id),
                    class: 'inline-flex items-center gap-1 text-xs text-indigo-600 hover:text-indigo-500 font-semibold',
                }, () => [h(ArrowUpRight, { class: 'h-3.5 w-3.5' }), 'Ver en Secretaría']),
            ]),
        });
    }

    return columns;
}

export default makeSolicitudColumns;
