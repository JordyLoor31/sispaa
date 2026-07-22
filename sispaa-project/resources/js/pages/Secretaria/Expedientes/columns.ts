import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, Clock, Eye, MoreHorizontal, ArrowUpRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';

export interface ArchivoMeta { name: string; size: string | null }
export interface Estudiante {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    carrera: string | null;
}
export interface DocumentoRow {
    id: number;
    tipo_documento: string;
    estado: 'pendiente' | 'aprobado' | 'rechazado';
    observacion: string | null;
    reviewed_at: string | null;
    reviewed_at_raw: string | null;
    created_at: string;
    archivo_url: string | null;
    archivo_meta: ArchivoMeta | null;
    estudiante: Estudiante;
    revisado_por: string | null;
}

const estadoClasses: Record<string, string> = {
    pendiente: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
    aprobado: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
    rechazado: 'bg-rose-50 text-rose-800',
};
const estadoLabel: Record<string, string> = {
    pendiente: 'Pendiente',
    aprobado: 'Aprobado',
    rechazado: 'Rechazado',
};

export function makeExpedienteColumns(): ColumnDef<DocumentoRow>[] {
    return [
        {
            id: 'documento',
            header: 'Documento',
            cell: ({ row }) => {
                const d = row.original;
                return h('div', { class: 'flex items-center gap-2.5' }, [
                    h('div', {
                        class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]',
                    }, [h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', fill: 'none',
                        stroke: 'currentColor', 'stroke-width': '2', class: 'h-4 w-4 text-[var(--sispaa-primary)]',
                    }, [
                        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }),
                    ])]),
                    h('div', {}, [
                        h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)] leading-tight' }, d.tipo_documento),
                        h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5' }, d.created_at),
                    ]),
                ]);
            },
        },
        {
            id: 'estudiante',
            header: 'Estudiante',
            cell: ({ row }) => {
                const e = row.original.estudiante;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, e.name),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, e.cedula ?? e.email),
                ]);
            },
        },
        {
            id: 'carrera',
            header: 'Carrera',
            cell: ({ row }) =>
                h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' },
                    row.original.estudiante.carrera ?? '—'),
        },
        {
            id: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const estado = row.original.estado;
                const icons: Record<string, any> = {
                    aprobado: CheckCircle2,
                    pendiente: Clock,
                    rechazado: XCircle,
                };
                const Icon = icons[estado] ?? Clock;
                return h('span', {
                    class: `inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${estadoClasses[estado] ?? 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]'}`,
                }, [
                    h(Icon, { class: estado === 'pendiente' ? 'h-3.5 w-3.5 animate-pulse' : 'h-3.5 w-3.5' }),
                    estadoLabel[estado] ?? estado,
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right text-xs font-semibold opacity-60 text-[var(--sispaa-text)] uppercase' }, 'Acciones'),
            cell: ({ row }) => {
                const d = row.original;
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
                                    { href: route('secretaria.expediente.show', d.id), class: 'w-full flex items-center' },
                                    () => [h(Eye, { class: 'mr-2 h-4 w-4' }), d.estado === 'pendiente' ? 'Revisar' : 'Ver'],
                                ),
                            ),
                            d.archivo_url
                                ? h(DropdownMenuItem, { asChild: true }, () =>
                                      h(
                                          'a',
                                          { href: d.archivo_url as string, target: '_blank', rel: 'noopener', class: 'w-full flex items-center' },
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

export default makeExpedienteColumns;
