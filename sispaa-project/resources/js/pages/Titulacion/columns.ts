import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { GraduationCap } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';

export interface Persona {
    id: number;
    name: string;
}

export interface Titulacion {
    id: number;
    tema: string;
    estado: 'en_proceso' | 'defendido' | 'graduado';
    fecha_inicio: string | null;
    fecha_graduacion: string | null;
    estudiante: Persona;
    tutor: Persona;
    creator?: Persona | null;
    updater?: Persona | null;
    created_at?: string;
}

const estadoBadge = (estado: string) => {
    const map: Record<string, string> = {
        en_proceso: 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400',
        defendido: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400',
        graduado: 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400',
    };
    return map[estado] ?? map.en_proceso;
};

interface TitulacionColumnsOptions {
    onChangeEstado: (titulacion: Titulacion, estado: string) => void;
}

export function makeTitulacionColumns({ onChangeEstado }: TitulacionColumnsOptions): ColumnDef<Titulacion>[] {
    return [
        {
            id: 'estudiante',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-slate-900 dark:text-white' }, [
                h(GraduationCap, { class: 'h-4 w-4 text-indigo-500' }),
                row.original.estudiante.name,
            ]),
        },
        {
            accessorKey: 'tema',
            meta: { label: 'Tema' },
            header: 'Tema',
            cell: ({ row }) => h('span', { class: 'text-slate-600 dark:text-slate-300 max-w-xs truncate block' }, row.original.tema),
        },
        {
            id: 'tutor',
            meta: { label: 'Tutor' },
            header: 'Tutor',
            cell: ({ row }) => row.original.tutor.name,
        },
        {
            accessorKey: 'estado',
            meta: { label: 'Estado' },
            header: 'Estado',
            cell: ({ row }) => {
                const t = row.original;
                return h(Select, {
                    modelValue: t.estado,
                    'onUpdate:modelValue': (val: string) => onChangeEstado(t, val),
                }, () => [
                    h(SelectTrigger, { class: `h-7 w-[130px] text-xs border-none ${estadoBadge(t.estado)}` }, () => h(SelectValue)),
                    h(SelectContent, {}, () => [
                        h(SelectItem, { value: 'en_proceso' }, () => 'En proceso'),
                        h(SelectItem, { value: 'defendido' }, () => 'Defendido'),
                        h(SelectItem, { value: 'graduado' }, () => 'Graduado'),
                    ]),
                ]);
            },
        },
        {
            accessorKey: 'fecha_inicio',
            meta: { label: 'Inicio' },
            header: 'Inicio',
            cell: ({ row }) => h('span', { class: 'text-xs text-slate-400' }, row.original.fecha_inicio ?? '—'),
        },
        {
            accessorKey: 'fecha_graduacion',
            meta: { label: 'Graduación' },
            header: 'Graduación',
            cell: ({ row }) => h('span', { class: 'text-xs text-slate-400' }, row.original.fecha_graduacion ?? '—'),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const t = row.original;
                return h(ResourceActionsDropdown, {
                    resourceName: 'el proceso de titulación',
                    showRoute: 'titulacion.show',
                    editRoute: 'titulacion.edit',
                    deleteRoute: 'titulacion.destroy',
                    routeParams: t.id,
                    itemLabel: `el proceso de "${t.estudiante.name}"`,
                });
            },
        },
    ];
}

export default makeTitulacionColumns;
