import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';
import type { AsignacionDocenteItem } from './types';

const estadoPeriodoBadge = (estado: string) => {
    const map: Record<string, string> = {
        activo: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
        planificado: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        finalizado: 'bg-slate-100 text-slate-500 dark:bg-slate-900 dark:text-slate-500',
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
                    h('p', { class: 'text-sm font-semibold text-slate-900 dark:text-white' }, d.name),
                    h('p', { class: 'text-xs text-slate-400' }, d.email),
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
                    h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-200' }, `${m.codigo} — ${m.nombre}`),
                    m.carrera ? h('p', { class: 'text-xs text-slate-400' }, m.carrera.nombre) : null,
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
            cell: ({ row }) => h('span', { class: 'text-xs text-slate-600 dark:text-slate-300' }, row.original.tipo_rol),
        },
        {
            accessorKey: 'grupo',
            meta: { label: 'Grupo' },
            header: 'Grupo',
            cell: ({ row }) => h('span', { class: 'text-xs text-slate-500' }, row.original.grupo ?? '—'),
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
