import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { ArrowUpRight, FileText } from 'lucide-vue-next';
import type { SolicitudRow } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        pendiente: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        aprobada: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        rechazada: 'bg-rose-50 text-rose-600',
    };
    return map[estado] ?? 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]';
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
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-[var(--sispaa-text)]' }, [
                h(FileText, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' }),
                row.original.estudiante ?? '—',
            ]),
        },
        {
            accessorKey: 'materia',
            meta: { label: 'Materia' },
            header: 'Materia',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.materia ?? '—'),
        },
        {
            accessorKey: 'fecha_falta',
            meta: { label: 'Falta del' },
            header: 'Falta del',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.fecha_falta ?? '—'),
        },
        {
            accessorKey: 'motivo_estudiante',
            meta: { label: 'Motivo' },
            header: 'Motivo',
            cell: ({ row }) => h('span', { class: 'block max-w-xs truncate opacity-80 text-[var(--sispaa-text)]' }, row.original.motivo_estudiante),
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
                    class: 'inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:text-[color:color-mix(in_srgb,var(--sispaa-primary)_85%,black)]',
                }, () => [h(ArrowUpRight, { class: 'h-3.5 w-3.5' }), 'Ver en Secretaría']),
            ]),
        });
    }

    return columns;
}

export default makeSolicitudColumns;
