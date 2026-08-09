import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

export interface EstudianteRow {
    id: number;
    name: string;
    email: string;
    cedula: string;
    telefono: string | null;
    carrera_id: number | null;
    carrera_nombre: string | null;
    documentos_count: number;
}

export function makeEstudianteColumns(): ColumnDef<EstudianteRow>[] {
    return [
        {
            id: 'name',
            header: 'Estudiante',
            cell: ({ row }) => {
                const s = row.original;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, s.name),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5' }, s.email),
                ]);
            },
        },
        {
            id: 'cedula',
            header: 'Cédula',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-70 text-[var(--sispaa-text)]' }, row.original.cedula),
        },
        {
            id: 'telefono',
            header: 'Teléfono',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.telefono ?? '—'),
        },
        {
            id: 'carrera_nombre',
            header: 'Carrera',
            cell: ({ row }) =>
                h(
                    'span',
                    { class: 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-[color:color-mix(in_srgb,var(--sispaa-primary)_12%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-primary)_65%,var(--sispaa-text))]' },
                    row.original.carrera_nombre ?? '—',
                ),
        },
        {
            id: 'documentos_count',
            header: 'Documentos',
            cell: ({ row }) => h('span', { class: 'text-xs font-semibold text-[var(--sispaa-text)]' }, String(row.original.documentos_count)),
        },
    ];
}

export default makeEstudianteColumns;
