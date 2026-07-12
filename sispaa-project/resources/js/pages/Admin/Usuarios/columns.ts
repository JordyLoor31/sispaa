import type { ColumnDef } from '@tanstack/vue-table';
import { Shield } from 'lucide-vue-next';
import { h } from 'vue';
import { Switch } from '@/components/ui/switch';
import ResourceActionsDropdown from '@/components/ResourceActionsDropdown.vue';

export interface Role {
    id: number;
    name: string;
}

export interface Carrera {
    id: number;
    nombre: string;
}

export interface Usuario {
    id: number;
    name: string;
    email: string;
    cedula: string | null;
    telefono: string | null;
    is_active: boolean;
    carrera_id: number | null;
    carrera?: Carrera | null;
    roles: Role[];
    created_at?: string;
}

interface UserColumnsOptions {
    onToggleStatus: (user: Usuario) => void;
}

const getRoleBadgeClass = (rolName: string) => {
    if (rolName === 'SystemAdministrador') return 'bg-purple-50 text-purple-800 dark:bg-purple-950/30 dark:text-purple-400';
    if (rolName === 'secretaria') return 'bg-pink-50 text-pink-800 dark:bg-pink-950/30 dark:text-pink-400';
    if (rolName === 'coordinador') return 'bg-blue-50 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400';
    if (rolName === 'docente') return 'bg-orange-50 text-orange-800 dark:bg-orange-950/30 dark:text-orange-400';

    return 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400';
};

export function makeUserColumns({ onToggleStatus }: UserColumnsOptions): ColumnDef<Usuario>[] {
    return [
        {
            accessorKey: 'name',
            meta: { label: 'Usuario' },
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
            meta: { label: 'Identificación' },
            header: 'Identificación',
            cell: ({ row }) => row.original.cedula ?? '-',
        },
        {
            accessorKey: 'telefono',
            meta: { label: 'Teléfono' },
            header: 'Teléfono',
            cell: ({ row }) => row.original.telefono ?? '-',
        },
        {
            id: 'carrera',
            meta: { label: 'Carrera' },
            header: 'Carrera',
            cell: ({ row }) => row.original.carrera?.nombre ?? 'General / Institucional',
        },
        {
            id: 'rol',
            meta: { label: 'Roles' },
            header: 'Roles',
            cell: ({ row }) => {
                const user = row.original;

                if (user.roles.length === 0) {
                    return h('span', { class: 'text-xs text-slate-400' }, 'Ninguno');
                }

                return h('div', { class: 'flex flex-wrap gap-1' }, user.roles.map((rol) => {
                    const formattedRol = rol.name.charAt(0).toUpperCase() + rol.name.slice(1);
                    return h('span', {
                        key: rol.id,
                        class: `inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold ${getRoleBadgeClass(rol.name)}`,
                    }, [
                        h(Shield, { class: 'h-3 w-3' }),
                        formattedRol,
                    ]);
                }));
            },
        },
        {
            accessorKey: 'is_active',
            meta: { label: 'Estado' },
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
            enableHiding: false,
            cell: ({ row }) => {
                const user = row.original;

                return h(ResourceActionsDropdown, {
                    resourceName: 'el usuario',
                    showRoute: 'admin.usuarios.show',
                    editRoute: 'admin.usuarios.edit',
                    routeParams: user.id,
                    itemLabel: `el usuario "${user.name}"`,
                    canDelete: false, // no se elimina, se desactiva (ver columna Estado)
                });
            },
        },
    ];
}

export default makeUserColumns;
