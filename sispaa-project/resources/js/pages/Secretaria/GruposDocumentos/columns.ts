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
                        { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/30' },
                        [h(FolderOpen, { class: 'h-4 w-4 text-indigo-500' })],
                    ),
                    h('div', {}, [
                        h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight' }, g.nombre),
                        g.descripcion ? h('p', { class: 'text-xs text-slate-400 mt-0.5 max-w-xs truncate' }, g.descripcion) : null,
                    ]),
                ]);
            },
        },
        {
            id: 'requisitos',
            header: 'Requisitos',
            cell: ({ row }) => {
                const reqs = row.original.requisitos;
                if (reqs.length === 0) return h('span', { class: 'text-xs text-slate-400' }, 'Sin requisitos aún.');
                return h(
                    'span',
                    { class: 'text-xs text-slate-600 dark:text-slate-300' },
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
                                ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400'
                                : 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500'
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
