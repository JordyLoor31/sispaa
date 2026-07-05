import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';

interface Carrera {
    id: number;
    nombre: string;
}

interface Docente {
    id: number;
    name: string;
}

interface Materia {
    id: number;
    nombre: string;
    carrera: Carrera;
}

interface Periodo {
    id: number;
    nombre: string;
}

interface Informe {
    id: number;
    docente: Docente;
    materia: Materia;
    periodo: Periodo;
    estado: 'pendiente' | 'subido' | 'aprobado' | string;
    archivo_url: string | null;
}

const getEstadoStyles = (estado: string) => {
    switch (estado) {
        case 'Cumplido':
            return { backgroundColor: '#d1fae5', color: '#065f46' };
        case 'Pendiente':
            return { backgroundColor: '#fef3c7', color: '#92400e' };
        case 'Incumplido':
            return { backgroundColor: '#fee2e2', color: '#991b1b' };
        default:
            return { backgroundColor: 'var(--sispaa-surface)', color: 'var(--sispaa-text)' };
    }
};

export function makeInformeColumns(mapEstado: (dbEstado: string) => string): ColumnDef<Informe>[] {
    return [
        {
            id: 'carrera',
            header: 'Carrera',
            cell: ({ row }) => {
                const carreraName = row.original.materia?.carrera?.nombre || 'General';
                return h('span', { class: 'inline-block px-3 py-1 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 font-sans' }, carreraName);
            },
        },
        {
            accessorKey: 'estado',
            header: 'Estado',
            cell: ({ row }) => {
                const estado = mapEstado(row.original.estado);
                const style = getEstadoStyles(estado);

                return h('span', {
                    class: 'inline-block px-3 py-1 text-xs font-semibold rounded-lg',
                    style: { backgroundColor: style.backgroundColor, color: style.color },
                }, estado);
            },
        },
        {
            id: 'materia',
            header: 'Asignatura',
            cell: ({ row }) => {
                const informe = row.original;
                return h('div', {}, [
                    h('h3', { class: 'font-bold text-base text-slate-900 dark:text-white' }, informe.materia?.nombre),
                    h('p', { class: 'text-xs text-slate-400 mt-0.5' }, `Periodo: ${informe.periodo?.nombre || '-'}`),
                ]);
            },
        },
        {
            id: 'docente',
            header: 'Docente',
            cell: ({ row }) => h('div', { class: 'text-sm font-medium text-slate-800 dark:text-slate-350' }, row.original.docente?.name || 'Sin asignar'),
        },
        {
            id: 'archivo',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Archivo'),
            cell: ({ row }) => {
                const url = row.original.archivo_url;

                if (url) {
                    return h('div', { class: 'text-right' }, [
                        h('a', {
                            href: url,
                            target: '_blank',
                            class: 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-colors bg-indigo-50 hover:bg-indigo-100 text-indigo-650 dark:bg-indigo-950/40 dark:text-indigo-400 dark:hover:bg-indigo-900/50',
                        }, [
                            h('svg', {
                                xmlns: 'http://www.w3.org/2000/svg',
                                class: 'h-4 w-4',
                                fill: 'none',
                                viewBox: '0 0 24 24',
                                stroke: 'currentColor',
                            }, [
                                h('path', {
                                    'stroke-linecap': 'round',
                                    'stroke-linejoin': 'round',
                                    'stroke-width': '2',
                                    d: 'M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                }),
                            ]),
                            'Ver Informe',
                        ]),
                    ]);
                }

                return h('div', { class: 'text-right' }, [
                    h('span', {
                        class: 'inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium border border-slate-200/80 bg-white text-slate-400 dark:border-slate-800 dark:bg-slate-950',
                    }, 'No adjuntado'),
                ]);
            },
        },
    ];
}

export default makeInformeColumns;