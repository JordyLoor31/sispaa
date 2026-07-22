import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { AsignacionDocenteItem } from './types';

const estadoPeriodoBadge = (estado: string) => {
    const map: Record<string, string> = {
        activo: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
        planificado: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        finalizado: 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]',
    };
    return map[estado] ?? map.planificado;
};

export function makeAsignacionDocenteColumns(): ColumnDef<AsignacionDocenteItem>[] {
    return [
        {
            id: 'docente',
            meta: { label: 'Docente' },
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
            id: 'materia',
            meta: { label: 'Materia' },
            header: 'Materia',
            cell: ({ row }) => {
                const m = row.original.materia;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, `${m.codigo} — ${m.nombre}`),
                    m.carrera ? h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, m.carrera.nombre) : null,
                ]);
            },
        },
        {
            id: 'periodo',
            meta: { label: 'Período' },
            header: 'Período',
            cell: ({ row }) => {
                const p = row.original.periodo;
                return h('span', { class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoPeriodoBadge(p.estado)}` }, p.nombre);
            },
        },
        {
            accessorKey: 'tipo_rol',
            meta: { label: 'Tipo' },
            header: 'Tipo',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-70 text-[var(--sispaa-text)]' }, row.original.tipo_rol),
        },
        {
            accessorKey: 'grupo',
            meta: { label: 'Grupo' },
            header: 'Grupo',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.grupo ?? '—'),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const a = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'la asignación',
                    editRoute: 'secretaria.asignaciones-docente.edit',
                    deleteRoute: 'secretaria.asignaciones-docente.destroy',
                    routeParams: a.id,
                    itemLabel: `la asignación de "${a.docente.name}" a "${a.materia.nombre}"`,
                });
            },
        },
    ];
}

export default makeAsignacionDocenteColumns;
