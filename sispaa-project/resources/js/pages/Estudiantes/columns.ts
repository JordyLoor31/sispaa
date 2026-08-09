import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FileText, MoreHorizontal, User } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

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
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right text-xs font-semibold opacity-60 text-[var(--sispaa-text)] uppercase' }, 'Acciones'),
            cell: ({ row }) => {
                const s = row.original;
                return h('div', { class: 'flex justify-end items-center gap-1' }, [
                    h(DropdownMenu, {}, () => [
                        h(DropdownMenuTrigger, { asChild: true }, () =>
                            h(Button, { variant: 'ghost', size: 'sm', class: 'h-8 w-8 p-0' }, () => [
                                h('span', { class: 'sr-only' }, 'Abrir menú'),
                                h(MoreHorizontal, { class: 'h-4 w-4' }),
                            ]),
                        ),
                        h(DropdownMenuContent, { align: 'end' }, () => [
                            h(DropdownMenuItem, { asChild: true }, () =>
                                h(
                                    Link,
                                    { href: route('estudiantes.perfil', s.id), class: 'w-full flex items-center' },
                                    () => [h(User, { class: 'mr-2 h-4 w-4' }), 'Ver datos personales'],
                                ),
                            ),
                            h(DropdownMenuItem, { asChild: true }, () =>
                                h(
                                    Link,
                                    { href: route('estudiantes.show', s.id), class: 'w-full flex items-center' },
                                    () => [h(FileText, { class: 'mr-2 h-4 w-4' }), 'Ver documentos'],
                                ),
                            ),
                        ]),
                    ]),
                ]);
            },
        },
    ];
}

export default makeEstudianteColumns;
