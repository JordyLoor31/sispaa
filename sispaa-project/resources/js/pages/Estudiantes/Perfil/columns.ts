import type { ColumnDef } from '@tanstack/vue-table';
import { h } from 'vue';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from 'lucide-vue-next';

/* ---------------------------------------------------------------
 | Tipos compartidos del módulo de perfil de estudiante (Show
 | administrativo/Mis Datos + tabla de familiares del wizard).
 |----------------------------------------------------------------*/

export type Parentesco = 'padre' | 'madre' | 'conyuge' | 'hijo' | 'otro';

export const PARENTESCO_LABELS: Record<Parentesco, string> = {
    padre: 'Padre',
    madre: 'Madre',
    conyuge: 'Cónyuge',
    hijo: 'Hijo/a',
    otro: 'Otro',
};

export interface Familiar {
    id: number;
    parentesco: Parentesco;
    nombres: string;
    telefono: string | null;
    correo: string | null;
    ocupacion: string | null;
    empresa: string | null;
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
            cell: ({ row }) => h('span', { class: 'font-semibold', style: { color: 'var(--sispaa-text)' } }, row.original.nombres),
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
