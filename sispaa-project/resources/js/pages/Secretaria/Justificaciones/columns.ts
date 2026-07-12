import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Link } from '@inertiajs/vue3';
import { CheckCircle2, XCircle, Clock, FileText, Calendar, BookOpen, Eye } from 'lucide-vue-next';

export interface Estudiante {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    carrera: string | null;
}
export interface FaltaInfo {
    id: number;
    fecha: string;
    materia: string | null;
}
export interface JustificacionRow {
    id: number;
    estado: 'pendiente' | 'aprobada' | 'rechazada';
    motivo_estudiante: string;
    comentario_revisor: string | null;
    archivo_adjunto: string | null;
    created_at: string;
    falta: FaltaInfo;
    estudiante: Estudiante;
}

const estadoClasses: Record<string, string> = {
    pendiente: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
    aprobada: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    rechazada: 'bg-rose-50 text-rose-800 dark:bg-rose-950/30 dark:text-rose-400',
};

export function makeJustificacionColumns(): ColumnDef<JustificacionRow>[] {
    return [
        {
            id: 'estudiante',
            header: 'Estudiante',
            cell: ({ row }) => {
                const e = row.original.estudiante;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-slate-800 dark:text-slate-200' }, e.name),
                    h('p', { class: 'text-xs text-slate-400' }, e.cedula ?? e.email),
                    h('p', { class: 'text-xs text-slate-400' }, e.carrera ?? '—'),
                ]);
            },
        },
        {
            id: 'falta',
            header: 'Falta registrada',
            cell: ({ row }) => {
                const f = row.original.falta;
                return h('div', { class: 'space-y-1' }, [
                    h('div', { class: 'flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300' }, [
                        h(Calendar, { class: 'h-3.5 w-3.5 text-slate-400' }),
                        f.fecha,
                    ]),
                    h('div', { class: 'flex items-center gap-1.5 text-xs text-slate-500' }, [
                        h(BookOpen, { class: 'h-3.5 w-3.5 text-slate-400' }),
                        f.materia ?? 'Sin materia',
                    ]),
                ]);
            },
        },
        {
            id: 'motivo',
            header: 'Motivo',
            cell: ({ row }) => {
                const motivo = row.original.motivo_estudiante;
                return h('p', {
                    class: 'text-xs text-slate-600 dark:text-slate-300 max-w-[200px] truncate',
                    title: motivo,
                }, motivo);
            },
        },
        {
            id: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const estado = row.original.estado;
                const icons: Record<string, any> = {
                    aprobada: CheckCircle2,
                    pendiente: Clock,
                    rechazada: XCircle,
                };
                const Icon = icons[estado] ?? Clock;
                const label = estado.charAt(0).toUpperCase() + estado.slice(1);
                return h('span', {
                    class: `inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${estadoClasses[estado] ?? 'bg-slate-100 text-slate-500'}`,
                }, [
                    h(Icon, { class: estado === 'pendiente' ? 'h-3.5 w-3.5 animate-pulse' : 'h-3.5 w-3.5' }),
                    label,
                ]);
            },
        },
        {
            id: 'adjunto',
            header: 'Adjunto',
            cell: ({ row }) => {
                const url = row.original.archivo_adjunto;
                if (!url) return h('span', { class: 'text-xs text-slate-400' }, '—');
                return h('a', {
                    href: url, target: '_blank',
                    class: 'inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg px-2 py-1',
                }, [h(FileText, { class: 'h-3.5 w-3.5' }), 'Ver adjunto']);
            },
        },
        {
            id: 'solicitud',
            header: 'Solicitud',
            cell: ({ row }) =>
                h('span', { class: 'text-xs text-slate-400' }, row.original.created_at),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right text-xs font-semibold text-slate-500 uppercase' }, 'Acciones'),
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex justify-end' }, [
                    h(Link, {
                        href: route('secretaria.justificaciones.show', item.id),
                        class: 'inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg px-2.5 py-1.5 transition-colors',
                    }, () => [h(Eye, { class: 'h-3.5 w-3.5' }), item.estado === 'pendiente' ? 'Revisar' : 'Ver']),
                ]);
            },
        },
    ];
}

export default makeJustificacionColumns;
