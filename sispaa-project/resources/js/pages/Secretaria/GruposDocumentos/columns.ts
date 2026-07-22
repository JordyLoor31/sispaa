import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import { FolderOpen, MoreHorizontal, Eye, Power } from 'lucide-vue-next';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import type { GrupoDocumento } from './types';

const toggleGrupo = (grupo: GrupoDocumento) => {
    router.post(
        route('secretaria.grupos-documentos.toggle', grupo.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => toast.success(grupo.activo ? 'Grupo desactivado.' : 'Grupo activado.'),
        },
    );
};

export function makeGrupoDocumentoColumns(): ColumnDef<GrupoDocumento>[] {
    return [
        {
            id: 'nombre',
            header: 'Grupo',
            cell: ({ row }) => {
                const g = row.original;
                return h('div', { class: 'flex items-center gap-2.5' }, [
                    h(
                        'div',
                        { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]' },
                        [h(FolderOpen, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' })],
                    ),
                    h('div', {}, [
                        h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)] leading-tight' }, g.nombre),
                        g.descripcion ? h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)] mt-0.5 max-w-xs truncate' }, g.descripcion) : null,
                    ]),
                ]);
            },
        },
        {
            id: 'requisitos',
            header: 'Requisitos',
            cell: ({ row }) => {
                const reqs = row.original.requisitos;
                if (reqs.length === 0) return h('span', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, 'Sin requisitos aún.');
                return h(
                    'span',
                    { class: 'text-xs opacity-70 text-[var(--sispaa-text)]' },
                    reqs.map((r) => r.nombre).join(', '),
                );
            },
        },
        {
            id: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const g = row.original;
                return h(
                    'span',
                    {
                        class: `inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${
                            g.activo
                                ? 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]'
                                : 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]'
                        }`,
                    },
                    g.activo ? 'Activo' : 'Inactivo',
                );
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right' }, 'Acciones'),
            cell: ({ row }) => {
                const g = row.original;
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
                                    { href: route('secretaria.grupos-documentos.show', g.id), class: 'w-full flex items-center' },
                                    () => [h(Eye, { class: 'mr-2 h-4 w-4' }), 'Ver detalle / requisitos'],
                                ),
                            ),
                            h(
                                DropdownMenuItem,
                                { onClick: () => toggleGrupo(g) },
                                () => [h(Power, { class: 'mr-2 h-4 w-4' }), g.activo ? 'Desactivar' : 'Activar'],
                            ),
                        ]),
                    ]),
                ]);
            },
        },
    ];
}

export default makeGrupoDocumentoColumns;
