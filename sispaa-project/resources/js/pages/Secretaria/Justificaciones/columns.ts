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
    pendiente: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
    aprobada: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
    rechazada: 'bg-rose-50 text-rose-800',
};

export function makeJustificacionColumns(): ColumnDef<JustificacionRow>[] {
    return [
        {
            id: 'estudiante',
            header: 'Estudiante',
            cell: ({ row }) => {
                const e = row.original.estudiante;
                return h('div', {}, [
                    h('p', { class: 'text-sm font-semibold text-[var(--sispaa-text)]' }, e.name),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, e.cedula ?? e.email),
                    h('p', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, e.carrera ?? '—'),
                ]);
            },
        },
        {
            id: 'falta',
            header: 'Falta registrada',
            cell: ({ row }) => {
                const f = row.original.falta;
                return h('div', { class: 'space-y-1' }, [
                    h('div', { class: 'flex items-center gap-1.5 text-xs opacity-70 text-[var(--sispaa-text)]' }, [
                        h(Calendar, { class: 'h-3.5 w-3.5 opacity-50' }),
                        f.fecha,
                    ]),
                    h('div', { class: 'flex items-center gap-1.5 text-xs opacity-60 text-[var(--sispaa-text)]' }, [
                        h(BookOpen, { class: 'h-3.5 w-3.5 opacity-50' }),
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
                    class: 'text-xs opacity-70 text-[var(--sispaa-text)] max-w-[200px] truncate',
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
                    class: `inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold ${estadoClasses[estado] ?? 'bg-[color:color-mix(in_srgb,var(--sispaa-text)_10%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-text)_60%,transparent)]'}`,
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
                if (!url) return h('span', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, '—');
                return h('a', {
                    href: url, target: '_blank',
                    class: 'inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80 border border-[color:color-mix(in_srgb,var(--sispaa-primary)_35%,transparent)] rounded-lg px-2 py-1',
                }, [h(FileText, { class: 'h-3.5 w-3.5' }), 'Ver adjunto']);
            },
        },
        {
            id: 'solicitud',
            header: 'Solicitud',
            cell: ({ row }) =>
                h('span', { class: 'text-xs opacity-50 text-[var(--sispaa-text)]' }, row.original.created_at),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right text-xs font-semibold opacity-60 text-[var(--sispaa-text)] uppercase' }, 'Acciones'),
            cell: ({ row }) => {
                const item = row.original;
                return h('div', { class: 'flex justify-end' }, [
                    h(Link, {
                        href: route('secretaria.justificaciones.show', item.id),
                        class: 'inline-flex items-center gap-1 text-xs font-semibold text-[var(--sispaa-primary)] hover:opacity-80 border border-[color:color-mix(in_srgb,var(--sispaa-primary)_35%,transparent)] rounded-lg px-2.5 py-1.5 transition-colors',
                    }, () => [h(Eye, { class: 'h-3.5 w-3.5' }), item.estado === 'pendiente' ? 'Revisar' : 'Ver']),
                ]);
            },
        },
    ];
}

export default makeJustificacionColumns;
