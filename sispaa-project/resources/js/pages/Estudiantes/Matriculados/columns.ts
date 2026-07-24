import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { User } from 'lucide-vue-next';
import type { StudentRow } from './types';

const estadoBadge = (estado: string | null) => {
    const map: Record<string, string> = {
        activo: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_25%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        retirado: 'bg-rose-50 text-rose-600',
        egresado: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-accent)_75%,black)]',
    };
    return map[estado ?? ''] ?? 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]';
};

export function makeMatriculadosColumns(): ColumnDef<StudentRow>[] {
    return [
        {
            id: 'name',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2' }, [
                h(User, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' }),
                h('div', {}, [
                    h('p', { class: 'font-semibold text-[var(--sispaa-text)]' }, row.original.name),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, row.original.email),
                ]),
            ]),
        },
        {
            accessorKey: 'cedula',
            meta: { label: 'Cédula' },
            header: 'Cédula',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.cedula ?? '—'),
        },
        {
            accessorKey: 'carrera_nombre',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, row.original.carrera_nombre ?? '—'),
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
            accessorKey: 'documentos_count',
            meta: { label: 'Documentos' },
            header: 'Documentos',
            cell: ({ row }) => h('span', { class: 'opacity-70 text-[var(--sispaa-text)]' }, String(row.original.documentos_count)),
        },
    ];
}

export default makeMatriculadosColumns;
