import type { ColumnDef } from '@tanstack/vue-table';
import { Edit, Shield } from 'lucide-vue-next';
import { h } from 'vue';
import { Switch } from '@/components/ui/switch';

interface Role {
    id: number;
    name: string;
}

interface Carrera {
    id: number;
    nombre: string;
}

interface UserItem {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    telefono: string | null;
    is_active: boolean;
    carrera_id: number | null;
    carrera?: Carrera | null;
    roles: Role[];
}

interface UserColumnsOptions {
    onEdit: (user: UserItem) => void;
    onToggleStatus: (user: UserItem) => void;
}

const getRoleBadgeClass = (rolName: string) => {
    if (rolName === 'SystemAdministrador') return 'bg-purple-50 text-purple-800 dark:bg-purple-950/30 dark:text-purple-400';
    if (rolName === 'secretaria') return 'bg-pink-50 text-pink-800 dark:bg-pink-950/30 dark:text-pink-400';
    if (rolName === 'coordinador') return 'bg-blue-50 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400';
    if (rolName === 'docente') return 'bg-orange-50 text-docente dark:bg-orange-950/30 dark:text-orange-400';

    return 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400';
};

export function makeUserColumns({ onEdit, onToggleStatus }: UserColumnsOptions): ColumnDef<UserItem>[] {
    return [
        {
            accessorKey: 'name',
            header: 'Usuario',
            cell: ({ row }) => {
                const user = row.original;
                return h('div', {}, [
                    h('div', { class: 'font-semibold text-slate-900 dark:text-white' }, user.name),
                    h('div', { class: 'text-xs text-slate-400' }, user.email),
                ]);
            },
        },
        {
            accessorKey: 'cedula',
            header: 'Identificación',
            cell: ({ row }) => row.original.cedula ?? '-',
        },
        {
            accessorKey: 'telefono',
            header: 'Teléfono',
            cell: ({ row }) => row.original.telefono ?? '-',
        },
        {
            id: 'carrera',
            header: 'Carrera',
            cell: ({ row }) => row.original.carrera?.nombre ?? 'General / Institucional',
        },
        {
            id: 'rol',
            header: 'Rol',
            cell: ({ row }) => {
                const user = row.original;
                const rolName = user.roles[0]?.name || 'Ninguno';
                const formattedRol = rolName.charAt(0).toUpperCase() + rolName.slice(1);

                return h('span', {
                    class: `inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ${getRoleBadgeClass(rolName)}`,
                }, [
                    h(Shield, { class: 'h-3 w-3' }),
                    formattedRol,
                ]);
            },
        },
        {
            accessorKey: 'is_active',
            header: 'Estado',
            cell: ({ row }) => {
                const user = row.original;
                const isActive = !!user.is_active;

                return h('div', { class: 'flex items-center gap-2' }, [
                    h(Switch, {
                        checked: isActive,
                        modelValue: isActive,
                        'onUpdate:checked': () => onToggleStatus(user),
                        'onUpdate:modelValue': () => onToggleStatus(user),
                    }),
                    h('span', {
                        class: `text-xs font-semibold ${isActive ? 'text-emerald-650 dark:text-emerald-400' : 'text-slate-400'}`,
                    }, isActive ? 'Activo' : 'Inactivo'),
                ]);
            },
        },
        {
            id: 'actions',
            header: () => h('div', { class: 'text-right font-semibold' }, 'Acciones'),
            cell: ({ row }) => {
                const user = row.original;

                return h('div', { class: 'text-right' }, [
                    h('button', {
                        onClick: () => onEdit(user),
                        class: 'text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 p-1.5 rounded-lg border border-slate-100 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-900 transition-colors',
                    }, [
                        h(Edit, { class: 'h-4 w-4' }),
                    ]),
                ]);
            },
        },
    ];
}

export default makeUserColumns;