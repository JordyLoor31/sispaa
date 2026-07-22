import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { GraduationCap } from 'lucide-vue-next';
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
        en_proceso: 'bg-[color:color-mix(in_srgb,#E4BC57_45%,transparent)] text-[color:color-mix(in_srgb,#E4BC57_60%,black)]',
        defendido: 'bg-[color:color-mix(in_srgb,var(--sispaa-accent)_20%,transparent)] text-[var(--sispaa-accent)]',
        graduado: 'bg-[color:color-mix(in_srgb,var(--sispaa-secondary)_30%,transparent)] text-[color:color-mix(in_srgb,var(--sispaa-secondary)_70%,black)]',
    };
    return map[estado] ?? map.en_proceso;
};

interface TitulacionColumnsOptions {
    /** false para docente/secretaría (solo lectura): oculta las acciones de editar/eliminar. */
    puedeGestionar: boolean;
}

export function makeTitulacionColumns({ puedeGestionar }: TitulacionColumnsOptions): ColumnDef<Titulacion>[] {
    return [
        {
            id: 'estudiante',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => h('div', { class: 'flex items-center gap-2 font-semibold text-[var(--sispaa-text)]' }, [
                h(GraduationCap, { class: 'h-4 w-4 text-[var(--sispaa-primary)]' }),
                row.original.estudiante.name,
            ]),
        },
        {
            accessorKey: 'tema',
            meta: { label: 'Tema' },
            header: 'Tema',
            cell: ({ row }) => h('span', { class: 'block max-w-xs truncate text-[var(--sispaa-text)] opacity-80' }, row.original.tema),
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
                const label = t.estado === 'en_proceso' ? 'En proceso' : t.estado === 'defendido' ? 'Defendido' : 'Graduado';

                return h('span', { class: `inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${estadoBadge(t.estado)}` }, label);
            },
        },
        {
            accessorKey: 'fecha_inicio',
            meta: { label: 'Inicio' },
            header: 'Inicio',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.fecha_inicio ?? '—'),
        },
        {
            accessorKey: 'fecha_graduacion',
            meta: { label: 'Graduación' },
            header: 'Graduación',
            cell: ({ row }) => h('span', { class: 'text-xs opacity-60 text-[var(--sispaa-text)]' }, row.original.fecha_graduacion ?? '—'),
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
                    editRoute: puedeGestionar ? 'titulacion.edit' : undefined,
                    deleteRoute: puedeGestionar ? 'titulacion.destroy' : undefined,
                    routeParams: t.id,
                    itemLabel: `el proceso de "${t.estudiante.name}"`,
                });
            },
        },
    ];
}

export default makeTitulacionColumns;
