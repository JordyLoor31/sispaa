import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { FileText, MoreHorizontal, Eye, ArrowUpRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { STATUS_COLORS, neutralBadgeStyle, tintedBadgeStyle } from '@/lib/brand';
import type { InformeItem } from './types';

// Estilos inline theme-aware (ver @/lib/brand): antes mezclaban con negro
// fijo y en tema oscuro quedaban ilegibles.
const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        aprobado: tintedBadgeStyle(STATUS_COLORS.exito),
        subido: tintedBadgeStyle(STATUS_COLORS.advertencia),
        pendiente: neutralBadgeStyle(),
    };
    return map[estado] ?? map.pendiente;
};

export function makeInformeColumns(): ColumnDef<InformeItem>[] {
    return [
        {
            id: 'materia',
            header: 'Materia',
            cell: ({ row }) => {
                const i = row.original;
                return h('div', { class: 'flex items-center gap-2.5' }, [
                    h(
                        'div',
                        { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]' },
                        [h(FileText, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' })],
                    ),
                    h('div', {}, [
                        h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)] leading-tight' }, i.materia),
                        h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5' }, `${i.carrera ?? '—'} · ${i.periodo}`),
                    ]),
                ]);
            },
        },
        {
            id: 'docente',
            header: 'Docente',
            cell: ({ row }) => {
                const d = row.original.docente;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, d.name),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, d.email),
                ]);
            },
        },
        {
            id: 'fecha_subida',
            header: 'Subido',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.fecha_subida ?? '—'),
        },
        {
            id: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const estado = row.original.estado;
                return h(
                    'span',
                    { class: 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold', style: estadoBadge(estado) },
                    estado.charAt(0).toUpperCase() + estado.slice(1),
                );
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right' }, 'Acciones'),
            cell: ({ row }) => {
                const i = row.original;
                return h('div', { class: 'flex justify-end' }, [
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
                                    { href: route('secretaria.informes.show', i.id), class: 'w-full flex items-center' },
                                    () => [h(Eye, { class: 'mr-2 h-4 w-4' }), i.estado === 'aprobado' ? 'Ver' : 'Revisar'],
                                ),
                            ),
                            i.ver_url
                                ? h(DropdownMenuItem, { asChild: true }, () =>
                                      h(
                                          'a',
                                          { href: i.ver_url as string, target: '_blank', rel: 'noopener', class: 'w-full flex items-center' },
                                          [h(ArrowUpRight, { class: 'mr-2 h-4 w-4' }), 'Ver archivo'],
                                      ),
                                  )
                                : null,
                        ]),
                    ]),
                ]);
            },
        },
    ];
}

export default makeInformeColumns;
