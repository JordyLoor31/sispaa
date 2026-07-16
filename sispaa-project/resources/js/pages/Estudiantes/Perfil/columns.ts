import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from 'lucide-vue-next';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';

/* ---------------------------------------------------------------
 | Tipos compartidos del módulo de perfil de estudiante (Index/Show del
 | listado administrativo + tabla de familiares del wizard).
 |----------------------------------------------------------------*/

export type Parentesco = 'padre' | 'madre' | 'representante' | 'conyuge' | 'hijo' | 'otro';

export const PARENTESCO_LABELS: Record<Parentesco, string> = {
    padre: 'Padre',
    madre: 'Madre',
    representante: 'Representante',
    conyuge: 'Cónyuge',
    hijo: 'Hijo/a',
    otro: 'Otro',
};

export interface Familiar {
    id: number;
    parentesco: Parentesco;
    nombres: string;
    cedula: string | null;
    telefono: string | null;
    correo: string | null;
    ocupacion: string | null;
    empresa: string | null;
}

export interface PerfilListado {
    id: number;
    user_id: number;
    nivel: string | null;
    sede: string | null;
    graduado_pregrado: boolean;
    user?: { id: number; name: string; email: string; cedula: string | null } | null;
    facultad?: { id: number; nombre: string } | null;
    carrera?: { id: number; nombre: string } | null;
}

/* ---------------------------------------------------------------
 | Columnas: listado administrativo de perfiles (Estudiantes/Perfil/Index.vue,
 | reservado para cuando Secretaría/SystemAdministrador tengan acceso).
 |----------------------------------------------------------------*/

export function makePerfilColumns(): ColumnDef<PerfilListado>[] {
    return [
        {
            id: 'estudiante',
            meta: { label: 'Estudiante' },
            header: 'Estudiante',
            cell: ({ row }) => h('div', {}, [
                h('span', { class: 'block font-semibold text-slate-900 dark:text-white' }, row.original.user?.name ?? '—'),
                h('span', { class: 'block text-xs text-slate-400' }, row.original.user?.email ?? ''),
            ]),
        },
        {
            id: 'carrera',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => row.original.carrera?.nombre ?? 'Sin asignar',
        },
        {
            id: 'facultad',
            meta: { label: 'Facultad' },
            header: 'Facultad',
            cell: ({ row }) => row.original.facultad?.nombre ?? '—',
        },
        {
            accessorKey: 'nivel',
            meta: { label: 'Nivel' },
            header: 'Nivel',
            cell: ({ row }) => row.original.nivel ?? '—',
        },
        {
            accessorKey: 'sede',
            meta: { label: 'Sede' },
            header: 'Sede',
            cell: ({ row }) => row.original.sede ?? '—',
        },
        {
            accessorKey: 'graduado_pregrado',
            meta: { label: 'Graduado' },
            header: 'Graduado',
            cell: ({ row }) => h('span', {
                class: `text-xs font-semibold ${row.original.graduado_pregrado ? 'text-emerald-600' : 'text-slate-400'}`,
            }, row.original.graduado_pregrado ? 'Sí' : 'No'),
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => h(ResourceActionsDropdown, {
                resourceName: 'el perfil',
                showRoute: 'admin.estudiantes.perfiles.show',
                routeParams: row.original.user_id,
                itemLabel: `el perfil de "${row.original.user?.name ?? ''}"`,
                canEdit: false,
                canDelete: false,
            }),
        },
    ];
}

/* ---------------------------------------------------------------
 | Columnas: tabla de familiares dentro del paso 4 del wizard
 | (Estudiantes/Perfil/Edit.vue).
 |----------------------------------------------------------------*/

interface FamiliarColumnsOptions {
    onEdit: (familiar: Familiar) => void;
    onDelete: (familiar: Familiar) => void;
}

export function makeFamiliarColumns({ onEdit, onDelete }: FamiliarColumnsOptions): ColumnDef<Familiar>[] {
    return [
        {
            accessorKey: 'nombres',
            meta: { label: 'Nombres' },
            header: 'Nombres',
            cell: ({ row }) => h('span', { class: 'font-semibold text-slate-800 dark:text-slate-200' }, row.original.nombres),
        },
        {
            accessorKey: 'parentesco',
            meta: { label: 'Parentesco' },
            header: 'Parentesco',
            cell: ({ row }) => PARENTESCO_LABELS[row.original.parentesco],
        },
        {
            accessorKey: 'telefono',
            meta: { label: 'Teléfono' },
            header: 'Teléfono',
            cell: ({ row }) => row.original.telefono ?? '—',
        },
        {
            accessorKey: 'correo',
            meta: { label: 'Correo' },
            header: 'Correo',
            cell: ({ row }) => row.original.correo ?? '—',
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            enableHiding: false,
            cell: ({ row }) => {
                const familiar = row.original;

                return h('div', { class: 'flex justify-end gap-1' }, [
                    h(Button, { type: 'button', variant: 'ghost', size: 'sm', class: 'h-7 w-7 p-0', onClick: () => onEdit(familiar) }, () => h(Pencil, { class: 'h-3.5 w-3.5' })),
                    h(Button, { type: 'button', variant: 'ghost', size: 'sm', class: 'h-7 w-7 p-0 text-rose-500 hover:text-rose-600', onClick: () => onDelete(familiar) }, () => h(Trash2, { class: 'h-3.5 w-3.5' })),
                ]);
            },
        },
    ];
}
