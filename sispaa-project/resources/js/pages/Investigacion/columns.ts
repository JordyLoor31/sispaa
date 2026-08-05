import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { FlaskConical } from 'lucide-vue-next';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { ProyectoItem } from './types';

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_curso: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]',
        pausada: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_55%,var(--sispaa-text))]',
        finalizada: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_55%,var(--sispaa-text))]',
    };
    return map[estado] ?? map.en_curso;
};

const estadoLabel = (estado: string) => estado.replace('_', ' ');

export function makeProyectoColumns(): ColumnDef<ProyectoItem>[] {
    return [
        {
            accessorKey: 'titulo',
            meta: { label: 'Proyecto' },
            header: 'Proyecto',
            cell: ({ row }) => {
                const p = row.original;
                return h('div', { class: 'flex min-w-0 items-start gap-2.5' }, [
                    h('div', { class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-[var(--sispaa-primary)] bg-[color:color-mix(in_srgb,var(--sispaa-primary)_15%,transparent)]' }, [
                        h(FlaskConical, { class: 'h-4 w-4' }),
                    ]),
                    h('div', { class: 'min-w-0' }, [
                        h('p', { class: 'truncate text-sm font-bold text-[var(--sispaa-text)]' }, p.titulo),
                        p.objetivo
                            ? h('p', { class: 'truncate text-xs opacity-60 text-[var(--sispaa-text)]' }, p.objetivo)
                            : null,
                    ]),
                ]);
            },
        },
        {
            id: 'lider',
            meta: { label: 'Líder' },
            header: 'Líder',
            cell: ({ row }) => h('span', { class: 'text-sm text-[var(--sispaa-text)]' }, row.original.lider.name),
        },
        {
            id: 'colider',
            meta: { label: 'Colíder' },
            header: 'Colíder',
            cell: ({ row }) => h('span', { class: 'text-sm opacity-60 text-[var(--sispaa-text)]' }, row.original.colider?.name ?? '—'),
        },
        {
            id: 'periodo',
            meta: { label: 'Período' },
            header: 'Período',
            cell: ({ row }) => h('span', { class: 'text-sm opacity-60 text-[var(--sispaa-text)]' }, row.original.periodo),
        },
        {
            id: 'hitos',
            meta: { label: 'Hitos' },
            header: 'Hitos',
            cell: ({ row }) => {
                const p = row.original;
                return h('span', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, `${p.hitos_completados}/${p.total_hitos}`);
            },
        },
        {
            id: 'miembros',
            meta: { label: 'Miembros' },
            header: 'Miembros',
            cell: ({ row }) => h('span', { class: 'text-sm opacity-60 text-[var(--sispaa-text)]' }, String(row.original.miembros.length)),
        },
        {
            id: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const p = row.original;
                return h('span', { class: ['inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize', estadoBadge(p.estado)] }, estadoLabel(p.estado));
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const p = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'el proyecto',
                    showRoute: 'investigacion.hitos',
                    editRoute: 'investigacion.edit',
                    deleteRoute: 'investigacion.destroy',
                    routeParams: p.id,
                    itemLabel: `el proyecto "${p.titulo}"`,
                    canView: true,
                    canEdit: p.puede_gestionar,
                    canDelete: p.es_propio,
                });
            },
        },
    ];
}

export default makeProyectoColumns;
