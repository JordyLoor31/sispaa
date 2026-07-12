import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, Clock, Eye } from 'lucide-vue-next';

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
    pendiente: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
    aprobado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    rechazado: 'bg-rose-50 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400',
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
                        class: 'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-50 dark:bg-indigo-950/30',
                    }, [h('svg', {
                        xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', fill: 'none',
                        stroke: 'currentColor', 'stroke-width': '2', class: 'h-4 w-4 text-indigo-500',
                    }, [
                        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' }),
                    ])]),
                    h('div', {}, [
                        h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-100 leading-tight' }, d.tipo_documento),
                        h('p', { class: 'text-xs text-slate-400 mt-0.5' }, d.created_at),
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
                    h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-200' }, e.name),
                    h('p', { class: 'text-xs text-slate-400' }, e.cedula ?? e.email),
                ]);
            },
        },
        {
            id: 'carrera',
            header: 'Carrera',
            cell: ({ row }) =>
                h('span', { class: 'text-xs text-slate-500 dark:text-slate-400' },
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
                    class: `inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${estadoClasses[estado] ?? 'bg-slate-100 text-slate-500'}`,
                }, [
                    h(Icon, { class: estado === 'pendiente' ? 'h-3.5 w-3.5 animate-pulse' : 'h-3.5 w-3.5' }),
                    estadoLabel[estado] ?? estado,
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right text-xs font-semibold text-slate-500 uppercase' }, 'Acciones'),
            cell: ({ row }) => {
                const d = row.original;
                return h('div', { class: 'flex justify-end' }, [
                    h(Link, {
                        href: route('secretaria.expediente.show', d.id),
                        class: 'inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg px-2.5 py-1.5 transition-colors',
                    }, () => [h(Eye, { class: 'h-3.5 w-3.5' }), d.estado === 'pendiente' ? 'Revisar' : 'Ver']),
                ]);
            },
        },
    ];
}

export default makeExpedienteColumns;
